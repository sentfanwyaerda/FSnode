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

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'FSnode.php');

class FSfile extends Xnode {
	public function Version($f=FALSE){ return FSnode::Version($f); }
	public function Product_url($u=FALSE){ return FSnode::Product_url($u); }
	public function Product($full=FALSE){ return FSnode::Product($full); }
	public function License($with_link=FALSE){ return FSnode::License($with_link); }
	public function License_url(){ return FSnode::License_url(); }
	public function Product_base(){ return dirname(__FILE__).DIRECTORY_SEPARATOR; }
	public function Product_file($full=FALSE){ return ($full ? self::Product_base() : NULL).basename(__FILE__); }
	
	var /*mounted FSnode|FSarchive*/ $fsnode;
	var $URI;
	private $_reference;
	
	function FSfile($URI, &$fsnode){
		$this->URI = $URI;
		if(!isset($fsnode) || !is_object($fsnode)){ $fsnode = FSnode($URI); }
		$this->fsnode &= $fsnode;
		$this->open();
	}
	public /*string*/ function __toString(){ return (string) $this->read(); }

	public /*string*/ function read($ignore=TRUE){
		#if(){ $this->open(); }
		$data = file_get_contents($this->_reference);
		return $data;
	}
	public function write($ignore=TRUE, $data=NULL, $auto_save=FALSE){
		$status = file_put_contents($this->_reference, $data);
		if(!($auto_save===FALSE)){ $this->save(); }
		return $status;
	}
	public /*dummy*/ function connect($URI=NULL, $refresh=FALSE){ return $this->open($URI, $refresh); }
	public function open($URI=NULL, $refresh=FALSE){
		#if($refresh === TRUE && !file_exists($this->_reference))
		$this->_reference = tempname();
		$data = $this->fsnode->read($this->URI);
		if(!($data === FALSE) && strlen($data) > 0){ $this->write($data); return TRUE; }
		else{ return FALSE; }
	}
	public function save($as=FALSE){
		if($as===FALSE){ $as = $this->URI; }
		return $this->fsnode->write($as, $this->read());
	}
	public function close(){
		#clean-up: remove file $this->_reference;
		unlink($this->_reference);
		$this->_reference = NULL;
		#destroy $this
	}
}
?>
