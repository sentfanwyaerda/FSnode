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
*    https://github.com/sentfanwyaerda/FSnode/blob/master/manual/FSnode_ftp.md      *
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
require_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'FSnode.php');

define('FSnode_FTP_URI_PREFIX', 'ftp:// ftps:// sftp://');
define('FSnode_FTP_SCHEME', 'ftp');

class FSnode_ftp extends FSnode {
	public function Version($a=FALSE){ return (/*!(parent::version(TRUE) == self::version(FALSE)) && */ !($a==FALSE) ? parent::version(TRUE).'-' : NULL).'experimental'; }
	public function Product($full=FALSE){ return "FSnode:ftp".(!($full===FALSE) ? " ".self::version(TRUE).(class_exists('Xnode') && method_exists('Xnode', 'Product') ? '/'.Xnode::Product(TRUE) : NULL) : NULL); }
	
	private /*resource*/ $ftp_stream;
	private /*bool*/ $authenticated = FALSE;
	#private $host, $user, $port;
	
	private function _initialize($a=NULL, $b=NULL, $c=NULL, $d=NULL){}
	public /*string*/ function __toString(){ return (string) NULL; }

	/* ignores the following FTP Functions ( http://php.net/manual/en/ref.ftp.php ) functions: ftp_alloc*, ftp_cdup, ftp_chdir, ftp_exec, ftp_fget*, ftp_fput*, ftp_get_option, ftp_get*, ftp_login*, ftp_nb_continue, ftp_nb_fget, ftp_nb_fput, ftp_nb_get, ftp_nb_put, ftp_nlist, ftp_pasv, ftp_put*, ftp_pwd, ftp_quit*, ftp_raw, ftp_rawlist, ftp_set_option, ftp_site, ftp_ssl_connect*, ftp_systype */
	
	
	private function _allowed_methods(){
		#return array('chmod','chgrp','chown','copy','delete','disk_free_space','disk_total_space','file_exists','file_get_contents','file_put_contents','file','fileatime','filectime','filegroup','fileinode','filemtime','fileowner','fileperms','filesize','filetype','is_dir','is_executable','is_file','is_readable','is_writable','is_writeable','mkdir','rename','rndir','stat','touch','unlink');
		return array('chmod','copy','file_get_contents','file_put_contents','file','filemtime','filesize','is_dir','is_file','mkdir','rename','rmdir','unlink','scandir','close','connect','read','write','delete');
	}
	
	/* the following FTP functions are renamed: ftp_chmod > chmod, ftp_close > close, ftp_connect > connect, ftp_delete > delete, ftp_mdtm > filemtime, ftp_mkdir > mkdir, ftp_rename > rename, ftp_rmdir > rmdir, ftp_size > filesize, ftp_nlist > scandir */
	
	#Filesystem Handlers
	public /*int*/ function chmod($filename, $mode){
		if(!self::is_connected()){ return /*error: not connected*/ FALSE; } else { return ftp_chmod($this->ftp_stream, (int) $mode, (string) self::realpath($filename)); }
	}
	public /*dummy*/ function chgrp(){ }
	public /*dummy*/ function chown(){ }
	
	public /*bool*/ function copy($source, $dest){ return self::write( (string) self::realpath($dest), self::read( (string) self::realpath($source) ) ); }
	
	public /*dummy*/ function disk_free_space(){ }
	public /*dummy*/ function disk_total_space(){ }
	
	public /*dummy*/ function file_exists(){ }
	public /*string*/ function file_get_contents($filename){ return self::read( (string) self::realpath($filename) ); } #alias
	public /**/ function file_put_contents($filename, $data){ return self::write( (string) self::realpath($filename), $data); } #alias
	public /*array*/ function file($filename){ return explode("\n", self::file_get_contents( (string) $filename )); }
		
	public /*dummy*/ function fileatime(){ }
	public /*dummy*/ function filectime(){ }
	public /*dummy*/ function filegroup(){ }
	public /*dummy*/ function fileinode(){ }
	public /*int*/ function filemtime($filename){ if(!self::is_connected()){ return /*error: not connected*/ FALSE; } else { return ftp_mdtm($this->ftp_stream, (string) self::realpath($filename) ); } }
	public /*dummy*/ function fileowner(){ }
	public /*dummy*/ function fileperms(){ }
	public /*int*/ function filesize($filename){ if(!self::is_connected()){ return /*error: not connected*/ FALSE; } else { return ftp_size($this->ftp_stream, (string) self::realpath($filename) ); } }
	public /*dummy*/ function filetype(){ }
	
	public /*bool*/ function is_dir($filename){
		return /*dummy*/ (in_array($filename, array('', '/')) ? TRUE : ( (string) self::filemtime($filename) == '' || (string) self::filesize($filename) == '-1' ? TRUE : FALSE));
		#return is_dir(self::realpath_URI($filename));
	}
	public /*dummy*/ function is_executable($filename){ }
	public /*dummy*/ function is_file($filename){ return is_file(self::realpath_URI($filename)); }
	#public /*dummy*/ function is_link($filename){ }
	public /*dummy*/ function is_readable($filename){ }
	#public /*dummy*/ function is_uploaded_file($filename){ }
	public /*dummy*/ function is_writable($filename){ }
	public /*dummy*/ function is_writeable($filename){ return self::is_writable( (string) self::realpath($filename) ); } #alias
	
	public /*bool*/ function is_connected($and_authenticated=TRUE){ return ($and_authenticated===TRUE ? (is_resource($this->ftp_stream) && $this->authenticated === TRUE ? TRUE : FALSE) : (is_resource($this->ftp_stream) ? TRUE : FALSE)); }
	public /*string*/ function realpath($filename=NULL){ return preg_replace('#[/]+#', '/', $this->parse_url($this->URI, 'path').($filename===NULL ? '/' : (substr($filename, 0, 1) == '/' ? $filename : '/'.$filename))); }
	
	public /*string*/ function mkdir($directory){ if(!self::is_connected()){ return /*error: not connected*/ FALSE; } else { return ftp_mkdir($this->ftp_stream, (string) self::realpath($directory) ); } }
	public /*bool*/ function rename($oldname, $newname){ if(!self::is_connected()){ return /*error: not connected*/ FALSE; } else { return ftp_rename($this->ftp_stream, (string) self::realpath($oldname), (string) self::realpath($newname) ); } }
	public /*bool*/ function rmdir($directory){ if(!self::is_connected()){ return /*error: not connected*/ FALSE; } else { return ftp_rmdir($this->ftp_stream, (string) $directory ); } }
	public /*dummy*/ function stat(){ }
	public /*dummy*/ function touch(){ }
	public /**/ function unlink($path){ return self::delete($path); } #dummy
	
	#Directory Handlers
	public /*array*/ function scandir($directory=NULL, $sorting_order=SCANDIR_SORT_ASCENDING){
		/*debug*/ print '<!-- FSnode_ftp::scandir( '.$directory.' ) -->'."\n";
		if(!self::is_connected()){ return /*error: not connected*/ array(); }
		else { 
			if($directory === NULL){ $directory = '.'; }
			/*realpath fix*/ $directory = self::realpath($directory);
			$list = ftp_nlist($this->ftp_stream, (string) $directory);
			#/*debug*/ print '<!-- directory: '.$directory.' = '; var_dump($list); print ' x '; var_dump(ftp_rawlist($this->ftp_stream, (string) $directory)); print ' -->'."\n";
			/*fix*/ if(strlen($directory) > 2){ $list = array_merge(array('..'), $list);}
			if(!( $sorting_order === SCANDIR_SORT_ASCENDING )){ $list = array_reverse($list); }
			return $list;
		}
	}
	public /*array*/ function scan($directory=NULL, $sorting_order=SCANDIR_SORT_ASCENDING){
		return $this->scandir($directory, $sorting_order);
	}
	
	#Server Handlers
	public /*bool*/ function close(){ if(!self::is_connected()){ return /*error: not connected*/ FALSE; } else { return ftp_close($this->ftp_stream); } }
	public /*resource*/ function connect($host=TRUE, $user=NULL, $pass=NULL, $port=21, $timeout=90, $secure=NULL){
		if($host === TRUE){ $host = $this->URI; }
		$set = self::parse_url($host); foreach($set as $k=>$v){ if(in_array($k, array('host','user','pass','port'))){ $$k = $v; } }
		if($secure === NULL && isset($set['scheme'])){ $secure = (!($set['scheme'] == 'ftp') ? TRUE : FALSE); }
		
		#if($user === NULL || !is_int($user)){ $port = $user; unset($user); if(!is_int($pass)){ $timeout = $pass; unset($pass); } elseif($pass === NULL){ $timeout = 90;} }
		/*set parameters*/ $this->host = $host; $this->user = $user; $this->port = $port;
		if($secure === TRUE && function_exists('ftp_ssl_connect') ){ $this->ftp_stream = ftp_ssl_connect( (string) $host, (int) $port /*, (int) $timeout*/ ); }
		else{ $this->ftp_stream = ftp_connect( (string) $host, (int) $port /*, (int) $timeout*/ ); }
		if($this->ftp_stream === FALSE){ return NULL; } /*else: login >> */
		if(isset($user) && isset($pass)){
			$this->authenticated = @ftp_login($this->ftp_stream, $user, $pass);
		}
		else{
			$this->authenticated = @ftp_login($this->ftp_stream, 'anonymous', NULL);
		}
		return $this->authenticated;
	}
	
	#Basic
	public /*string*/ function read($filename){
		/*realpath fix*/ $filename = self::realpath($filename);
		if(!self::is_connected()){ return /*error: not connected*/ FALSE; }
		else { 
			$temp_file = tempnam(FSnode_TEMP_DIRECTORY, 'fsnode_');
			$handle = fopen($temp_file, 'x+');
			if(ftp_get($this->ftp_stream, $handle, (string) $filename )){ $contents = fread($handle, filesize($temp_file)); }
			else{ $contents = FALSE; }
			fclose($handle);
			unlink($temp_file);
			return $contents;
		}
	}
	public /*bool*/ function write($filename, $data){
		/*realpath fix*/ $filename = self::realpath($filename);
		if(!self::is_connected()){ return /*error: not connected*/ FALSE; }
		else { 
			$bool = TRUE;
			$temp_file = tempnam(FSnode_TEMP_DIRECTORY, 'fsnode_');
			file_put_contents($temp_file, $data);
			if(ftp_alloc($this->ftp_stream, filesize($temp_file), $result)){
				if(!ftp_put($this->ftp_stream, (string) $filename, $temp_file )){ $bool = FALSE; }
			} else { $bool = FALSE; /*$bool = $result;*/ }
			unlink($temp_file);
			return $bool;
		}
	}
	public /*bool*/ function delete($path){
		if(!self::is_connected()){
			return /*error: not connected*/ FALSE;
		} else {
			/*realpath fix*/ $path = self::realpath($path);
			return ftp_delete($this->ftp_stream, (string) $path);
		}
	}
}
?>