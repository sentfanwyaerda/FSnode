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
*    Sent f&acirc;n Wy&aelig;rda (sent@wyaerda.org) [creator, main]                 *
*                                                                                   *
* License: cc-by-nd                                                                 *
*    Creative Commons, Attribution-No Derivative Works 3.0 Unported                 *
*    http://creativecommons.org/licenses/by-nd/3.0/                                 *
*    http://creativecommons.org/licenses/by-nd/3.0/legalcode                        *
*                                                                                   *
****************** CHANGES IN THE CODE ARE AT OWN RISK *****************************/
require_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'FSnode.php');

define('FSnode_EXTENSION_FTP', 'ftp:// ftps:// sftp://');

class FSnode_ftp extends FSnode {
	private /*resource*/ $ftp_stream;
	#private $host, $user, $port;
	
	private function _initialize($a=NULL, $b=NULL, $c=NULL, $d=NULL){}
	public /*string*/ function __toString(){ return (string) NULL; }

	/* ignores the following FTP Functions ( http://php.net/manual/en/ref.ftp.php ) functions: ftp_alloc*, ftp_cdup, ftp_chdir, ftp_exec, ftp_fget*, ftp_fput*, ftp_get_option, ftp_get*, ftp_login*, ftp_nb_continue, ftp_nb_fget, ftp_nb_fput, ftp_nb_get, ftp_nb_put, ftp_nlist, ftp_pasv, ftp_put*, ftp_pwd, ftp_quit*, ftp_raw, ftp_rawlist, ftp_set_option, ftp_site, ftp_ssl_connect*, ftp_systype */
	
	
	private function _allowed_methods(){
		#return array('chmod','chgrp','chown','copy','delete','disk_free_space','disk_total_space','file_exists','file_get_contents','file_put_contents','file','fileatime','filectime','filegroup','fileinode','filemtime','fileowner','fileperms','filesize','filetype','is_dir','is_executable','is_file','is_readable','is_writable','is_writeable','mkdir','rename','rndir','stat','touch','unlink');
		return array('chmod','copy','file_get_contents','file_put_contents','file','filemtime','filesize','mkdir','rename','rmdir','unlink','scandir','close','connect','read','write','delete');
	}
	
	/* the following FTP functions are renamed: ftp_chmod > chmod, ftp_close > close, ftp_connect > connect, ftp_delete > delete, ftp_mdtm > filemtime, ftp_mkdir > mkdir, ftp_rename > rename, ftp_rmdir > rmdir, ftp_size > filesize, ftp_nlist > scandir */
	
	#Filesystem Handlers
	public /*int*/ function chmod($filename, $mode){
		return ftp_chmod($this->ftp_stream, (int) $mode, (string) $filename);
	}
	public /*dummy*/ function chgrp(){ }
	public /*dummy*/ function chown(){ }
	
	public /*bool*/ function copy($source, $dest){ return self::write( (string) $dest, self::read( (string) $source ) ); }
	
	public /*dummy*/ function disk_free_space(){ }
	public /*dummy*/ function disk_total_space(){ }
	
	public /*dummy*/ function file_exists(){ }
	public /*string*/ function file_get_contents($filename){ return self::read( (string) $filename ); } #alias
	public /**/ function file_put_contents($filename, $data){ return self::write( (string) $filename, $data); } #alias
	public /*array*/ function file($filename){ return explode("\n", self::file_get_contents( (string) $filename )); }
		
	public /*dummy*/ function fileatime(){ }
	public /*dummy*/ function filectime(){ }
	public /*dummy*/ function filegroup(){ }
	public /*dummy*/ function fileinode(){ }
	public /*int*/ function filemtime($filename){ return ftp_mdtm($this->ftp_stream, (string) $filename ); }
	public /*dummy*/ function fileowner(){ }
	public /*dummy*/ function fileperms(){ }
	public /*int*/ function filesize($filename){ return ftp_size($this->ftp_stream, (string) $filename ); }
	public /*dummy*/ function filetype(){ }
	
		public /*dummy*/ function is_dir($filename){ }
	public /*dummy*/ function is_executable($filename){ }
	public /*dummy*/ function is_file($filename){ }
	#public /*dummy*/ function is_link($filename){ }
	public /*dummy*/ function is_readable($filename){ }
	#public /*dummy*/ function is_uploaded_file($filename){ }
	public /*dummy*/ function is_writable($filename){ }
	public /*dummy*/ function is_writeable($filename){ return self::is_writable( (string) $filename ); } #alias
	
	public /*string*/ function mkdir($directory){ return ftp_mkdir($this->ftp_stream, (string) $directory ); }
	public /*bool*/ function rename($oldname, $newname){ return ftp_rename($this->ftp_stream, (string) $oldname, (string) $newname ); }
	public /*bool*/ function rmdir($directory){ return ftp_rmdir($this->ftp_stream, (string) $directory ); }
	public /*dummy*/ function stat(){ }
	public /*dummy*/ function touch(){ }
	public /**/ function unlink($path){ return self::delete($path); } #dummy
	
	#Directory Handlers
	public /*array*/ function scandir($directory=NULL, $sorting_order=SCANDIR_SORT_ASCENDING){
		if($directory === NULL){ $directory = '.'; }
		$list = ftp_nlist($this->ftp_stream, (string) $directory);
		if(!( $sorting_order === SCANDIR_SORT_ASCENDING )){ $list = array_reverse($list); }
		return $list;
	}
	
	#Server Handlers
	public /*bool*/ function close(){ return ftp_close($this->ftp_stream); }
	public /*resource*/ function connect($host, $user=NULL, $pass=NULL, $port=21, $timeout=90, $secure=FALSE){
		if(preg_match('#^([sx]?ftp[s]?://)(([^:@]+)([:]([^@]+))?[@])?([^:/]+)([:]([0-9]+))?(.*)$#i', $host, $dummy)){
			list($trash, $protocol, $trash, $user, $trash, $pass, $host, $trash, $port, $filename) = $dummy;
			$secure = ($protocol == 'ftp://' ? FALSE : TRUE);
		}
		if($user === NULL || !is_int($user)){ $port = $user; unset($user); if(!is_int($pass)){ $timeout = $pass; unset($pass); } elseif($pass === NULL){ $timeout = 90;} }
		#/*set parameters*/ $this->host = $host; $this->user = $user; $this->port = $port;
		if(!($secure === FALSE) && function_exists('ftp_ssl_connect') ){ $this->ftp_stream = ftp_ssl_connect( (string) $host, (int) $port, (int) $timeout ); }
		else{ $this->ftp_stream = ftp_connect( (string) $host, (int) $port, (int) $timeout ); }
		if(isset($user) && isset($pass)){
			return @ftp_login($this->ftp_stream, $user, $pass);
		}
		return $this->ftp_stream;
	}
	
	#Basic
	public /*string*/ function read($filename){
		$temp_file = tempnam(FSnode_TEMP_DIRECTORY, 'fsnode_');
		$handle = fopen($temp_file, 'x+');
		if(ftp_get($this->ftp_stream, $handle, (string) $filename )){ $contents = fread($handle, filesize($temp_file)); }
		else{ $contents = FALSE; }
		fclose($handle);
		unlink($temp_file);
		return $contents;
	}
	public /*bool*/ function write($filename, $data){
		$bool = TRUE;
		$temp_file = tempnam(FSnode_TEMP_DIRECTORY, 'fsnode_');
		file_put_contents($temp_file, $data);
		if(ftp_alloc($this->ftp_stream, filesize($temp_file), $result)){
			if(!ftp_put($this->ftp_stream, (string) $filename, $temp_file )){ $bool = FALSE; }
		} else { $bool = FALSE; /*$bool = $result;*/ }
		unlink($temp_file);
		return $bool;	
	}
	public /*bool*/ function delete($path){ return ftp_delete($this->ftp_stream, (string) $path); }
	
}
?>