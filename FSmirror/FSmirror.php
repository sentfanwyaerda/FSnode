<?php 
require_once(dirname(dirname(__FILE__)).'/FSnode.php');

define('OPERATOR_OR','OR');
define('OPERATOR_AND','AND');

class FSmirror{
	var $local; // "local", location A
	var $remote; // "remote", location B
	
	var $ignore = array();
	var $recursive = FALSE;
	
	var $log = array();
	
	public function ignore($set=array()){ return array_merge(array('^[.]{1,2}$'), $set); }
	
	function FSmirror($local, $remote, $ignore=array(), $recursive=FALSE){
		/*force FSnode*/ $this->local = $local;
		/*force FSnode*/ $this->remote = $remote;
		if(!is_array($ignore) || $ignore === array() ){
			$this->ignore = FSmirror::ignore();
		} else { $this->ignore = $ignore; }
		$this->recursive = $recursive;
	}
	
	function get_local(){ return $this->local; }
	function get_remote(){ return $this->remote; }
	
	private function _push_or_pull($filename=NULL, $force=FALSE, $a='local'){
		$b = (!in_array(strtolower($a), array('push','remote')) ? 'local' : 'remote');
		$a = ($b != 'local' ? 'local' : 'remote'); //fix: in case $a != (local|remote)
		$res = TRUE;
		if(is_array($filename)){ $list = $filename; }
		else{ $list = self::scandir($filename); }
		
		foreach($list as $file){
			$action = array();
			/*debug*/ print ($a == 'local' ? 'PUSH' : 'PULL').' '.$file."\n";
			if($this->$a->is_file($file)){
				$this->$b->write($file, $this->$a->read($file)); $action[] = 'replace';
			}
			elseif($this->$a->is_dir($file)){
				if(!$this->$b->file_exists($file)){
					if($this->$b->method_exists('mkdir')){ $this->$b->mkdir($file); $action[] = 'mkdir'; }
				}
			}
			if($this->$b->method_exists('touch')){ $this->$b->touch($file, $this->$a->filemtime($file)); $action[] = 'touch'; }
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
		
		foreach($list as $file){
			$compare = self::compare($file, $force);
			if(in_array(strtolower($compare[$file]['action']), array('push','pull') )){ self::_push_or_pull($file, $force, $compare[$file]['action']); }
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
			$local_list = $this->local->scandir($filename);
			$remote_list = $this->remote->scandir($filename);
			$lr = array_unique(array_merge($lr, $local_list, $remote_list));
		}
		/*fix*/ ksort($lr);
		foreach($lr as $file){
			//if(!preg_match('#^[\.]{1,2}$#i', $file)){
			//if(!in_array($file, $this->ignore) ){
			if(!self::_preg_array_match($this->ignore, $file, 'OR')){
				if(($this->local->is_dir($file) || $this->remote->is_dir($file) ) ){
					$list[] = $this->_generate_filename($filename, $file.DIRECTORY_SEPARATOR);
					if($recursive !== FALSE && !in_array($this->_generate_filename($filename, $file.DIRECTORY_SEPARATOR), $list) && !in_array($file, array('/','')) && !in_array($filename, array('/','')) ){
						$list = array_merge($list, self::scandir($this->_generate_filename($filename, $file.DIRECTORY_SEPARATOR), $recursive));
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
			return $directory.(strlen($directory) == 0 || preg_match('#'.DIRECTORY_SEPARATOR.'$#', $directory) || in_array($file, array('/','')) ? NULL : DIRECTORY_SEPARATOR).$file;
		}
	}
	private function _preg_array_match($patterns=array(), $needle=NULL, $operator=OPERATOR_AND){
		$bool = ($operator == OPERATOR_OR ? FALSE : TRUE);
		foreach($patterns as $pattern){
			if(preg_match('#'.$pattern.'#i', $needle)){
				$bool = ($operator == OPERATOR_OR ? TRUE : ($bool && TRUE));
			} else {
				$bool = ($operator == OPERATOR_OR ? $bool : ($bool && FALSE));
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