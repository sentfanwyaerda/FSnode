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
*    https://github.com/sentfanwyaerda/FSnode/blob/master/manual/FSnode_zip.md      *
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

define('FSnode_ZIP_URI_PREFIX', 'zip:/');
define('FSnode_ZIP_SCHEME', 'zip');

class FSnode_zip extends FSnode {
	public function Version($a=FALSE){ return (/*!(parent::version(TRUE) == self::version(FALSE)) && */ !($a==FALSE) ? parent::version(TRUE).'-' : NULL).'native'; }
	public function Product($full=FALSE){ return "FSnode:zip".(!($full===FALSE) ? " ".self::version(TRUE).(class_exists('Xnode') && method_exists('Xnode', 'Product') ? '/'.Xnode::Product(TRUE) : NULL) : NULL); }
	private function _allowed_methods(){
		//return array('chmod','chgrp','chown','copy','delete','disk_free_space','disk_total_space','file_exists','file_get_contents','file_put_contents','file','fileatime','filectime','filegroup','fileinode','filemtime','fileowner','fileperms','filesize','filetype','is_dir','is_executable','is_file','is_readable','is_writable','is_writeable','mkdir','rename','rndir','stat','touch','unlink','read','write');
		return array('connect','is_connected','close','file_get_contents','file_put_contents');
	}
	public /*string*/ function __toString(){ return (string) $this->URI; }
	
	var $zip;
	private function _is_ZipArchive($za=NULL){
		if(!($za === NULL) && isset($this) && isset($this->zip)){ $za = $this->zip; }
		return (is_object($za) && get_class($za) == 'ZipArchive');
	}
	
	public /*bool*/ function is_connected(){
		return (self::_is_ZipArchive($this->zip) ? TRUE : FALSE);
	}
	public function connect(){
		$zipfile = $this->URI;
		$flags = ZipArchive::CREATE; //:OVERWRITE :EXCL :CHECKONS
		$this->zip = new ZipArchive();
		$this->zip->open($zipfile, $flags);
	}
	public function close(){
		if(self::_is_ZipArchive($this->zip)){
			$this->zip->close();
		}
	}
	public function file_get_contents($file){
		if(self::_is_ZipArchive($this->zip)){
			return $this->zip->getFromName($file);
		}
	}
	public function file_put_contents($file, $contents){
		if(self::_is_ZipArchive($this->zip)){
			return $this->zip->addFromString($file, $contents);
		}
	}
	public function filemtime($file){
		if(self::_is_ZipArchive($this->zip)){
			$s = $this->zip->statname($file);
			return $s['mtime'];
		}
	}
	public function filesize($file, $compressed=FALSE){
		if(self::_is_ZipArchive($this->zip)){
			$s = $this->zip->statname($file);
			return ($compressed === FALSE ? $s['size'] : $s['comp_size'] );
		}
	}
}
?>