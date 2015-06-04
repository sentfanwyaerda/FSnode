<?php 
require_once(dirname(dirname(__FILE__)).'/FSnode.php');

define('OPERATOR_OR','OR');
define('OPERATOR_AND','AND');

class FSmirror{
	var $local; // "local", location A
	var $remote; // "remote", location B
	
	var $ignore_switch = FALSE;
	var $preg_list = array();
	var $recursive = FALSE;
	
	var $log = array();
	
	public function ignore($set=array()){ return array_unique(array_merge((isset($this) && $this->ignore_switch == FALSE ? array('^[.]{1,2}$') : array()), $set)); }
	
	function FSmirror($local, $remote, $preg_list=array(), $recursive=FALSE, $ignore_switch=FALSE){
		/*force FSnode*/ $this->local = (is_string($local) ? FSnode::URI_load($local) : $local);
		/*force FSnode*/ $this->remote = (is_string($remote) ? FSnode::URI_load($remote) : $remote);
		$this->ignore_switch = $ignore_switch;
		$this->preg_list = FSmirror::ignore( (!is_array($preg_list) ? array() : $preg_list) );
		$this->recursive = $recursive;
		
		//$this->connect();
	}
	function connect(){
		$this->local->connect();
		$this->remote->connect();		
	}
	function close(){
		$this->local->close();
		$this->remote->close();
	}
	
	function get_local(){ return $this->local; }
	function get_remote(){ return $this->remote; }
	
	private function _push_or_pull($filename=NULL, $force=FALSE, $a='local'){
		$b = (!in_array(strtolower($a), array('push','remote')) ? 'local' : 'remote');
		$a = ($b != 'local' ? 'local' : 'remote'); //fix: in case $a != (local|remote)
		$ignore_directory = (!$force);
		$res = TRUE;
		if(is_array($filename)){ $list = $filename; }
		else{ $list = self::scandir($filename); }
				
		$compare = self::compare($list, $force);
		
		foreach($list as $file){
			$action = array();
			//*debug*/ print '<!-- '.$file.' [a='.$a.'] ('.print_r($compare[$file]['action'], TRUE).') do '.($a == 'local' ? 'PUSH' : 'PULL').' -->';
			if( isset($compare[$file]) && ($a == 'local' ? 'push' : 'pull') == $compare[$file]['action'] ){
				/*debug*/ print ($a == 'local' ? 'PUSH' : 'PULL').' '.$file."\n";
				if($this->$a->file_exists($file) && $this->$a->is_file($file)){
					$action[] = ($this->$a->file_exists($file) ? 'replace file' : 'add file');
					$this->$b->write($file, $this->$a->read($file));
				}
				elseif($this->$a->file_exists($file) && $this->$a->is_dir($file)){
					if(!$this->$b->file_exists($file)){
						if($this->$b->method_exists('mkdir')){ $this->$b->mkdir($file); $ignore_directory = FALSE; $action[] = 'mkdir'; }
					}
				}
				elseif(!$this->$a->file_exists($file) && $force != FALSE){
					if($this->$b->file_exists($file) && $this->$b->is_file($file)){ $this->$b->unlink($file); $action[] = 'delete file'; }
					elseif($this->$b->file_exists($file) && $this->$b->is_dir($file)){ $this->$b->rmdir($file); $action[] = 'delete directory'; }
				}
				
				if(($ignore_directory != TRUE && $this->$b->is_dir($file)) || $this->$b->is_file($file) ){
					if($this->$b->method_exists('chmod') && $this->$a->method_exists('fileperms')){ $this->$b->chmod($file, octdec($this->$a->fileperms($file, TRUE))); $action[] = 'chmod ('.$this->$a->fileperms($file, TRUE).')'; }
				
					if($this->$b->method_exists('touch')){ $this->$b->touch($file, $this->$a->filemtime($file)); $action[] = 'touch'; }
				}
			}
			else {
				$action[] = 'ignore '.($b == 'local' ? 'push' : 'pull');
			}
			$this->log[] = array((!$this->$a->is_dir($file) ? 'file' : 'directory')=>$file,'action'=>array_merge(array(($a == 'local' ? 'push' : 'pull')), $action));
		}
		return $res;
	}
	function push($filename=NULL, $force=FALSE){
		return self::_push_or_pull($filename, $force, 'PUSH');
	}
	function pull($filename=NULL, $force=FALSE){
		return self::_push_or_pull($filename, $force, 'PULL');
	}
	function sync($filename=NULL, $force=FALSE){
		$res = TRUE;
		if(is_array($filename)){ $list = $filename; }
		else{ $list = self::scandir($filename); }
		
		$compare = self::compare($list, $force);
		
		foreach($list as $file){
			if(in_array(strtolower($compare[$file]['action']), array('push','pull') )){
				self::_push_or_pull(array($file), $force, $compare[$file]['action']);
			}
			else {
				$this->log[] = array(($this->local->is_dir($file) || $this->remote->is_dir($file) ? 'directory' : 'file')=>$file,'action'=>array('synchronized'));
			}
		}
		return $res;
	}
	function compare($filename=NULL, $force=FALSE){
		$res = array();
		if(is_array($filename)){ $list = $filename; }
		else{ $list = self::scandir($filename); }
		
		//*debug*/ $list = array_merge(array('/'), $list);
		foreach($list as $file){
			$res[$file]['action'] = NULL;
			foreach(array('local','remote') as $rep){
				if($this->$rep->method_exists('file_exists') && $this->$rep->file_exists($file)){
						$res[$file][$rep]['file_exists'] = TRUE;
						//,'fileatime','filectime','fileinode','filetype','stat'
						foreach(array('is_dir','is_file','filesize','filemtime','filegroup','fileowner','fileperms','is_readable','is_writable','is_executable') as $method){
							if($this->$rep->method_exists($method)) $res[$file][$rep][$method] = $this->$rep->$method($file);
						}
						/*debug*/ if(isset($res[$file][$rep]['filemtime'])){ $res[$file][$rep]['lastmod'] = date("c", $res[$file][$rep]['filemtime']); }
					}
				}
			if(!isset($res[$file]['local']) || (isset($res[$file]['remote']) && $res[$file]['local']['filemtime'] < $res[$file]['remote']['filemtime'] )){ $res[$file]['action'] = 'pull'; }
			if(!isset($res[$file]['remote']) || (isset($res[$file]['local']) && $res[$file]['local']['filemtime'] > $res[$file]['remote']['filemtime'] )){ $res[$file]['action'] = 'push'; }
		}
		return $res;
	}
	
	function scandir($filename=NULL, $recursive=NULL){
		if($recursive === NULL){ $recursive = $this->recursive; }
		$list = $lr = array();
				
		if($this->local->is_file($filename) || $this->remote->is_file($filename)){
			$lr[] = $filename; $filename = NULL;
		}
		else{
			if($this->local->is_dir($filename) || $this->remote->is_dir($filename)){
				$lr[] = NULL;
			}
			$local_list = ($this->local->file_exists($filename) ? $this->local->scandir($filename) : array());
			$remote_list = ($this->local->file_exists($filename) ? $this->remote->scandir($filename) : array());
			$lr = array_unique(array_merge($lr, $local_list, $remote_list));
		}
		/*fix*/ ksort($lr);
		foreach($lr as $file){
			//if(!preg_match('#^[\.]{1,2}$#i', $file)){
			//if(!in_array($file, $this->preg_list) ){
			//if(($this->ignore_switch == FALSE ? !self::_preg_array_match($this->preg_list, $file, 'OR') : self::_preg_array_match($this->preg_list, $file, 'OR'))){
			if(!self::_preg_array_match($this->preg_list, $file, 'OR')){
				if(($this->local->is_dir($file) || $this->remote->is_dir($file) ) ){
					/*debug*/ print '<!-- advise recursive scandir for: '.$this->_generate_filename($filename, $file.DIRECTORY_SEPARATOR).' -->';
					if($recursive !== FALSE && !in_array($this->_generate_filename($filename, $file.DIRECTORY_SEPARATOR), $list) && !in_array($file, array('/','')) ){
						// && !in_array($filename, array('/',''))
						$list = array_merge($list, self::scandir($this->_generate_filename($filename, $file.DIRECTORY_SEPARATOR), $recursive));
					}
					else{
						$list[] = $this->_generate_filename($filename, $file.DIRECTORY_SEPARATOR);
					}
				}
				else {
					$list[] = $this->_generate_filename($filename, $file);
				}
			}
		}
		return $list;
	}
	private function _generate_filename($directory, $file=NULL){
		if($file == NULL){
			return $directory;
		}
		else{
			//if(!($this->local->is_dir($directory) || $this->remote->is_dir($directory)) ){
			//	return $this->_generate_filename(dirname($directory).DIRECTORY_SEPARATOR, $file);
			//}
			$res = $directory.(strlen($directory) == 0 || preg_match('#'.DIRECTORY_SEPARATOR.'$#', $directory) || in_array($file, array('/','')) ? NULL : DIRECTORY_SEPARATOR).$file;
			if(preg_match("#^[/]+$#", $res)){ return '/';}
			return preg_replace("#^/(.*)$#", "\\1", preg_replace("#//#", "/", $res));
		}
	}
	private function _preg_array_match($patterns=array(), $needle=NULL, $operator=OPERATOR_AND){
		$bool = ($operator == OPERATOR_OR ? FALSE : TRUE);
		if(is_array($needle)){
			foreach($needle as $n){
				$bool = ($operator == OPERATOR_OR ? ($bool || self::_preg_array_match($patterns, $n, $operator)) : ($bool && self::_preg_array_match($patterns, $n, $operator)) ); 
			}
		}
		else{
			foreach($patterns as $pattern){
				if(preg_match('#'.$pattern.'#i', $needle)){
					$bool = ($operator == OPERATOR_OR ? TRUE : ($bool && TRUE));
				} else {
					$bool = ($operator == OPERATOR_OR ? $bool : ($bool && FALSE));
				}
			}
		}
		return $bool;
	}
	
	private /*array*/ function _allowed_methods(){
		return array('push','pull','sync','compare','scandir');
	}
	public /*bool*/ function method_exists($str=NULL){ return (in_array(strtolower($str), self::_allowed_methods() )); }
}
?>