<?php
/****************** DO NOT REMOVE OR ALTER THIS HEADER ******************************
*                                                                                   *
* Product: FSnode                                                                   *
*    FSnode is a node-based Uniform FileSystem handler, written in PHP. It allows   *
*    you to access all kind of file systems in the exact same manner, with the same *
*    simple commands. You can write your web application once and let users switch  *
*    file system/platform; mount through URI.                                       *
*                                                                                   *
* Latest version to download:                                                       *
*    https://github.com/sentfanwyaerda/FSnode                                       *
*                                                                                   *
* Documentation:                                                                    *
*    http://sent.wyaerda.org/FSnode/                                                *
*    https://github.com/sentfanwyaerda/FSnode/blob/master/manual/Introduction.md    *
*                                                                                   *
* Authors:                                                                          *
*    Sent fan Wy&aelig;rda (fsnode@sent.wyaerda.org) [creator, main]                *
*                                                                                   *
* License: cc-by-nd                                                                 *
*    Creative Commons, Attribution-No Derivative Works 3.0 Unported                 *
*    http://creativecommons.org/licenses/by-nd/3.0/                                 *
*    http://creativecommons.org/licenses/by-nd/3.0/legalcode                        *
*                                                                                   *
****************** CHANGES IN THE CODE ARE AT OWN RISK *****************************/

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'settings.php');

if(!class_exists('Xnode')){
	class Xnode { }
}

#for use of hooks:
define('PREFIX', 'prefix');
define('POSTFIX', 'postfix');
define('ITTERATION', 'itteration');
define('FAIL', 'fail');

class FSnode extends Xnode {
	public function Version($f=FALSE){ return '0.2.4'; }
	public function Product_url($u=FALSE){ return ($u === TRUE ? "https://github.com/sentfanwyaerda/FSnode" : "http://sent.wyaerda.org/FSnode/?version=".self::Version(TRUE).'&license='.str_replace(' ', '+', self::License()) );}
	public function Product($full=FALSE){ return "FSnode".(!($full===FALSE) ? " ".self::version(TRUE).(class_exists('Xnode') && method_exists('Xnode', 'Product') ? '/'.Xnode::Product(TRUE) : NULL) : NULL); }
	public function License($with_link=FALSE){ return ($with_link ? '<a href="'.self::License_url().'">' : NULL).'cc-by-nd 3.0'.($with_link ? '</a>' : NULL); }
	public function License_url(){ return 'http://creativecommons.org/licenses/by-nd/3.0/'; }
	public function Product_base(){ return dirname(__FILE__).DIRECTORY_SEPARATOR; }
	public function Product_file($full=FALSE){ return ($full ? self::Product_base() : NULL).basename(__FILE__); }
		
	function FSnode($a=NULL, $b=NULL, $c=NULL, $d=NULL){
		self::_initialize($a, $b, $c, $d);
	}
	private function _initialize($a=NULL, $b=NULL, $c=NULL, $d=NULL){
		#This will do some magic, later on
	}

	private /*array*/ function _allowed_methods(){
		return array('chmod','chgrp','chown','copy','delete','disk_free_space','disk_total_space','file_exists','file_get_contents','file_put_contents','file','fileatime','filectime','filegroup','fileinode','filemtime','fileowner','fileperms','filesize','filetype','is_dir','is_executable','is_file','is_readable','is_writable','is_writeable','mkdir','rename','rndir','stat','touch','unlink','read','write');
	}
	public /*bool*/ function method_exists($str=NULL){ return (in_array(strtolower($str), self::_allowed_methods() )); }
	
	public /*string*/ function __toString(){ return (string) NULL; }
	
	private $hooks = array();
	private function _hook($_m_, $vars=array(), $placeholder='default', $add_default=FALSE){
		$bool = TRUE;
		#use like: $this->_hook(__METHOD__, $args, PREFIX);
		$placeholder = strtolower($placeholder); if(!in_array($placeholder, array('default','prefix','postfix','itteration','fail') )){ $placeholder = 'default'; }
		$method = $placeholder.'_hook_'.strtolower($_m_);
		$def_method = 'default_hook_'.strtolower($_m_);
		foreach($this->hooks as $h){
			$hook = 'FSnode_'.$h;
			if(class_exists($hook) && method_exists($hook, $method)){ $bool = ( $bool && $hook::$method($vars) ); }
			if(!($add_default===FALSE) && $placeholder != 'default' && class_exists($hook) && method_exists($hook, $def_method )){ $bool = ( $bool && $hook::$def_method($vars) ); }
		}
		return $bool;
	}
	public function add_hook($hook){
		if(class_exists( (string) $hook) && !in_array($hook, $this->hooks)){ $this->hooks[] = (string) $hook; return TRUE; }
		else{ return FALSE; }
	}
	public function load_extension($ext=FALSE){
		switch($ext){
			case TRUE:
				foreach(scandir('./extension/') as $f){
					if(!in_array($f, array('.', '..', 'all.php')) && preg_match("#(.*).php$#i", $f, $buffer)){
						FSnode::load_extension($buffer[1]);
					}
				}
				break;
			case FALSE: break;
			default:
				if(is_array($ext)){
					$bool = TRUE;
					foreach($ext as $buffer){
						$bool = ($bool && FSnode::load_extension($buffer) );
					}
					return $bool;
				}
				else{
					$p = dirname(__FILE__).DIRECTORY_SEPARATOR.'extension'.DIRECTORY_SEPARATOR.preg_replace("#[^a-z0-9_]#i", "", $ext).'.php';
					if(file_exists($p)){
						require_once($p);
					} else{ return FALSE; }
				}
		}
		return TRUE;
	}
	
	private $URI = NULL;
	public /*FSnode*/ function URI_load($URI){
		#if(isset($this)){ $o =& $this; } else {
			$o = new FSnode();
		#}
		$o->URI = $URI;
		
		#detect FSnode_?? by namespace
		$ns = strtolower(preg_replace("#^([a-z]+)[:](.*)$#i", "\\1", $URI));
		switch($ns){
			case 'ftp':
				$o->_rewrite_class = 'FSnode_ftp';
				break;
			default:
				#if(isset($[$ns])){} #loads manual affix of custom FSnode_??
				#do nothing: keep FSnode on LOCAL
				#$o->_rewrite_class = 'FSnode_local';
		}
		
		#FSnode REWRITE		
		if(method_exists('Xnode', 'XnodeClassRewrite')){ Xnode::XnodeClassRewrite($o); }
		
		if($o->_validate_URI($o->URI)){
			$o->connect();
			return $o;
		}
		else{
			return FALSE;
		}
	}
	private /*bool*/ function _validate_URI($URI){
		if(preg_match("#^(file:)(.*)([/]?)$#i", $URI, $buffer)){
			#if(isset($buffer[3]) && $buffer[3]==='/')
			return (file_exists($buffer[2]) && is_dir($buffer[2]));
		} else { return FALSE; }
	}
	private /*string*/ function _filename_attach_prefix($filename){
		if(isset($this->URI) && $this->_validate_URI($this->URI)){
			preg_match("#^file:(.*)([/]?)$#i", $this->URI, $buffer); list($trash, $chroot, $trash) = $buffer;
			#if(!preg_match("#^(".$chroot.")#i", $filename)){ #check for prefix: do not double prefix
				$filename = $chroot.(!preg_match("#^[/\\/]#i", $filename) ? DIRECTORY_SEPARATOR : NULL).$filename;
			#}
			#/*debug*/ print "<!-- \n\t".$filename."\n=\t".realpath($chroot)."\n=\t".realpath($filename)."\n -->\n";
			if(!(substr(realpath($filename), 0, strlen(realpath($chroot))) == realpath($chroot))){ return FALSE; /*out of chroot*/ }
		}	
		return (string) $filename;
	}
	
	/* ignores the following Filesystem&Directory Functions ( http://php.net/manual/en/ref.filesystem.php & http://php.net/manual/en/ref.dir.php ) functions: basename, clearstatcace, dirname, diskfreespace*, fclose, feof, fflush, fgetc, fgetcsv, fgets, fgetss, flock, fnmatch, fopen, fpassthru, fputcsv, fputs, fread, fscanf, fseek, fstat, ftell, ftruncate, fwrite, glob, is_link, is_uploaded_file, lchgrp,, lchown, link, linkinfo, lstat, move_uploaded_file, parse_ini_file, parse_ini_string, pathinfo, pclose, popen, readfile, readlink, realpath_cache_get, realpath_cache_size, realpath, rewind, set_file_buffer, symlink, tempnam, umask & chdir, chroot, closedir, dir, getcwd, opendir, readdir, rewinddir */
	
	#Filesystem Handlers
	public /*bool*/ function chmod($filename, $mode){ return chmod( $this->_filename_attach_prefix( (string) $filename ), (int) $mode ); }
	public /*bool*/ function chgrp($filename, $group){ return chgrp( $this->_filename_attach_prefix( (string) $filename ), $group ); }
	public /*bool*/ function chown($filename, $user){ return chown( $this->_filename_attach_prefix( (string) $filename ), $user ); }
	
	public /*bool*/ function copy($source, $dest /*, (resource) $context */ ){ return copy( $this->_filename_attach_prefix( (string) $source ), $this->_filename_attach_prefix( (string) $dest ) /*, (resource) $context */ ); }
	
	public /*float*/ function disk_free_space($directory=NULL){ if($directory === NULL){ $directory = './'; } return disk_free_space( $this->_filename_attach_prefix( (string) $directory ) ); }
	public /*float*/ function disk_total_space($directory=NULL){ if($directory === NULL){ $directory = './'; } return disk_total_space( $this->_filename_attach_prefix( (string) $directory ) ); }
	
	public /*bool*/ function file_exists($filename){ return file_exists( $this->_filename_attach_prefix( (string) $filename ) ); }
	public /*string*/ function file_get_contents($filename, $use_include_path=FALSE /*, (resource) $context, $offset=-1, $maxlen=FALSE */){ return file_get_contents( $this->_filename_attach_prefix( (string) $filename ), (bool) $use_include_path /*, (resource) $context, (int) $offset, (int) $maxlen */ ); }
	public /*int*/ function file_put_contents($filename, $data, $flags=0 /*, (resource) $context */ ){ return file_put_contents( $this->_filename_attach_prefix( (string) $filename ), $data, (int) $flags /*, (resource) $context */ ); }
	public /*array*/ function file($filename, $flags=0 /*, (resource) $context */ ){ return file( $this->_filename_attach_prefix( (string) $filename ), (int) $flags /*, (resource) $context */ ); }
	
	public /*int*/ function fileatime($filename){ return fileatime( $this->_filename_attach_prefix( (string) $filename ) ); }
	public /*int*/ function filectime($filename){ return filectime( $this->_filename_attach_prefix( (string) $filename ) ); }
	public /*int*/ function filegroup($filename){ return filegroup( $this->_filename_attach_prefix( (string) $filename ) ); }
	public /*int*/ function fileinode($filename){ return fileinode( $this->_filename_attach_prefix( (string) $filename ) ); }
	public /*int*/ function filemtime($filename){ return filemtime( $this->_filename_attach_prefix( (string) $filename ) ); }
	public /*int*/ function fileowner($filename){ return fileowner( $this->_filename_attach_prefix( (string) $filename ) ); }
	public /*int*/ function fileperms($filename){ return fileperms( $this->_filename_attach_prefix( (string) $filename ) ); }
	public /*int*/ function filesize($filename){ return filesize( $this->_filename_attach_prefix( (string) $filename ) ); }
	public /*int*/ function filetype($filename){ return filetype( $this->_filename_attach_prefix( (string) $filename ) ); }
	
	public /*bool*/ function is_dir($filename){ return is_dir( $this->_filename_attach_prefix( (string) $filename ) ); }
	public /*bool*/ function is_executable($filename){ return is_executable( $this->_filename_attach_prefix( (string) $filename ) ); }
	public /*bool*/ function is_file($filename){ return is_file( $this->_filename_attach_prefix( (string) $filename ) ); }
	#public /*bool*/ function is_link($filename){ return is_link( $this->_filename_attach_prefix( (string) $filename ) ); }
	public /*bool*/ function is_readable($filename){ return is_readable( $this->_filename_attach_prefix( (string) $filename ) ); }
	#public /*bool*/ function is_uploaded_file($filename){ return is_uploaded_file( $this->_filename_attach_prefix( (string) $filename ) ); }
	public /*bool*/ function is_writable($filename){ return is_writable( $this->_filename_attach_prefix( (string) $filename ) ); }
	public /*bool*/ function is_writeable($filename){ return self::is_writable( $this->_filename_attach_prefix( (string) $filename ) ); } #alias
	
	public /*bool*/ function mkdir($pathname, $mode=0777, $recursive=FALSE /*, (resource) $context */ ){ return mkdir( $this->_filename_attach_prefix( (string) $pathname ), (int) $mode, (bool) $recursive /*, (resource) $context */ ); }
	public /*bool*/ function rename($oldname, $newname /*, (resource) $context */ ){ return rename( $this->_filename_attach_prefix( (string) $oldname ), $this->_filename_attach_prefix( (string) $newname ) /*, (resource) $context */  ); }
	public /*bool*/ function rmdir($dirname /*, (resource) $context */ ){ return rmdir( $this->_filename_attach_prefix( (string) $dirname ) /*, (resource) $context */  ); }
	public /*array*/ function stat($filename){ return stat( $this->_filename_attach_prefix( (string) $filename ) ); }
	public /*bool*/ function touch($filename, $time=NULL, $atime=0){
		if($time === NULL){ $time = time(); }
		if($atime === 0){ $atime = time(); }
		return touch( $this->_filename_attach_prefix( (string) $filename ), (int) $time, (int) $atime );
	}
	public /*bool*/ function unlink($filename /*, (resource) $context */ ){ return unlink( $this->_filename_attach_prefix( (string) $filename ) /*, (resource) $context */  ); }
	
	#Directory Handlers
	public /*directory*/ function scandir($directory=NULL, $sorting_order=SCANDIR_SORT_ASCENDING /*, (resource) $context */ ){
		if($directory === NULL){ $directory = './'; } 
		$d = $this->_filename_attach_prefix( (string) $directory );
		if($d && is_dir($d)){
			return scandir( $d, (int) $sorting_order /*, (resource) $context */ );
		} else { return array(); }
	}
	public /*string*/ function realpath($filename=NULL){
		$f = $this->_filename_attach_prefix( (string) $filename );
		if( !($filename===NULL) && $f ){
			return realpath( $f );
		} else{ #returns an empty $filename to be an error
			return FALSE;
		}
	}

	#Server Handlers
	public /*bool*/ function close(){
		$this->_hook(__METHOD__, array(), PREFIX, TRUE);
		$this->_hook(__METHOD__, array(), POSTFIX);
		return TRUE;	
	}
	public /*bool*/ function connect($a=NULL, $b=NULL, $c=NULL, $d=NULL, $timeout=90, $secure=FALSE){
		$this->_hook(__METHOD__, array(), PREFIX, TRUE);
		$this->_hook(__METHOD__, array(), POSTFIX);
		return TRUE;
	}
	
	#Basic
	public /*string*/ function read($filename){
		$this->_hook(__METHOD__, array('filename'=>$filename), PREFIX, TRUE);
		$result = self::file_get_contents( $this->_filename_attach_prefix( (string) $filename ) );
		$this->_hook(__METHOD__, array('filename'=>$filename, 'result'=>$result), POSTFIX);
		return $result;
	}
	public /*int*/ function write($filename, $data){
		$this->_hook(__METHOD__, array('filename'=>$filename, 'data'=>$data), PREFIX, TRUE);
		$result = self::file_put_contents( $this->_filename_attach_prefix( (string) $filename ), $data);
		$this->_hook(__METHOD__, array('filename'=>$filename, 'data'=>$data, 'result'=>$result), POSTFIX);
		return $result;
	}
	public /*bool*/ function delete($filename /*, (resource) $context */ ){
		$this->_hook(__METHOD__, array('filename'=>$filename), PREFIX, TRUE);
		if(!$this->file_exists($filename)){ $result = FALSE; }
		if($this->is_dir($filename)){
			$result = self::rmdir( (string) $filename );
		}
		else{
			$result = self::unlink( (string) $filename /*, (resource) $context */  );
		}
		$this->_hook(__METHOD__, array('filename'=>$filename, 'result'=>$result), POSTFIX);
		return $result;
	}
	public /*array*/ function scan($directory=NULL, $sorting_order=SCANDIR_SORT_ASCENDING){
		$this->_hook(__METHOD__, array('directory'=>$directory, 'sorting_order'=>$sorting_order), PREFIX, TRUE);
		$result = self::scandir($directory, $sorting_order);
		$this->_hook(__METHOD__, array('directory'=>$directory, 'sorting_order'=>$sorting_order, 'result'=>$result), POSTFIX);
		return $reslut;
	}
	
}
?>