<?php
/****************** DO NOT REMOVE OR ALTER THIS HEADER ******************************
*                                                                                   *
* Product: FSbrowser                                                                *
*    FSbrowser is a test application written for FSnode. It let you browse the file *
*    system like Apache indexes directories.                                        *
*                                                                                   *
* Latest version to download:                                                       *
*    https://github.com/sentfanwyaerda/FSnode                                       *
*                                                                                   *
* Documentation:                                                                    *
*    http://sent.wyaerda.org/FSbrowser/                                             *
*    https://github.com/sentfanwyaerda/FSnode/blob/master/manual/FSbrowser.md       *
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
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'extra-library.php');
session_start();

class FSbrowser{
	public function Version($f=FALSE){ return '0.2.1'; }
	public function Product_url($u=FALSE){ return ($u === TRUE ? "https://github.com/sentfanwyaerda/FSnode" : "http://sent.wyaerda.org/FSbrowser/".'?version='.self::Version(TRUE).'&license='.str_replace(' ', '+', self::License()) );}
	public function Product($full=FALSE){ return "FSnode Browser".($full ? " ".self::version(TRUE).(class_exists('FSnode') && method_exists('FSnode', 'Product') ? '/'.FSnode::Product(TRUE) : NULL).(class_exists('Xnode') && method_exists('Xnode', 'Product') ? '/'.Xnode::Product(TRUE) : NULL) : NULL); }
	public function License($with_link=FALSE){ return ($with_link ? '<a href="'.self::License_url().'">' : NULL).'cc-by-nd 3.0'.($with_link ? '</a>' : NULL); }
	public function License_url(){ return 'http://creativecommons.org/licenses/by-nd/3.0/'; }
	public function Product_base(){ return dirname(__FILE__).DIRECTORY_SEPARATOR; }
	public function Product_file($full=FALSE){ return ($full ? self::Product_base() : NULL).basename(__FILE__); }

	var $label;
	var $URI = NULL;
	var $handler;
	
	function FSbrowser($URI, $label=NULL){
		$this->label = (string) $label;
		$this->URI = (string) $URI;
		$this->handler = FSnode::URI_load((string) $URI);
	}
	
	function connect($URI, $label=NULL, $redirect=FALSE){
		if($label===NULL){ $label = substr(preg_replace("#^([a-z]+[:][/]{0,2})?([^@]+[@])?([^/]+)(.*)#i", "\\3", $URI), 0, 8); }
		$_SESSION['URI_db'][strtolower($label)] = $URI;
		if(!($redirect===FALSE)){
			print parse_template('templates/redirect.html', array('URI'=>'/'.$label.'/'));
		}
	}
	
	function getURI_by_SESSION_and_PREFIX(&$URI){
		global $_SESSION;
		if(preg_match("#^[/]?([^/]+)(.*)$#i", $URI, $buffer)){
			if(
				isset($_SESSION['URI_db'])
				&& is_array($_SESSION['URI_db'])
			#	&& in_array(strtolower($buffer[1]), $_SESSION['URI_db'])
				&& array_key_exists(strtolower($buffer[1]), $_SESSION['URI_db'])
				){
				$baseURI = $_SESSION['URI_db'][strtolower($buffer[1])];
				$URI = $buffer[2];
				return $baseURI;
			}
			else{ return 'NULL'; }
		}
		else{
			return FALSE;
		}
	}
	function build($subURI){
		#return '[ '.$subURI.' ]';
		
		if(is_object($this->handler)){
			$lines = array();
			$subURI = str_replace(DIRECTORY_SEPARATOR, '/',  str_replace($this->handler->realpath('/'), '', $this->handler->realpath($subURI)) );
			foreach($this->handler->scandir($subURI) as $f){
				if($this->handler->realpath($subURI. DIRECTORY_SEPARATOR .$f) && !($f == '.')){
				$lines[$f] = parse_template('templates/line.html', array(
					'file:name.full' => ($f == '..' ? 'Parent Directory' : $f.($this->handler->is_dir($subURI. DIRECTORY_SEPARATOR .$f) ? '/' : NULL)),
					'file:url' => str_replace(DIRECTORY_SEPARATOR, '/',  './FSbrowser.php?URI='.($f == '..' ? DIRECTORY_SEPARATOR .$this->label.dirname($subURI): DIRECTORY_SEPARATOR .$this->label.$subURI. DIRECTORY_SEPARATOR .$f) ),
					'file:type.class' => ($f == '..' ? 'parent-directory' : ($this->handler->is_dir($subURI. DIRECTORY_SEPARATOR .$f) ? 'directory' : strtolower(preg_replace("#^(.*)[.]([a-z0-9]+)$#i", "\\2", $f)) )),
					'file:modified.last' => date('d-M-Y H:i', $this->handler->filemtime($subURI. DIRECTORY_SEPARATOR .$f) ),
					'file:size' => ($this->handler->file_exists($subURI. DIRECTORY_SEPARATOR .$f) && !$this->handler->is_dir($subURI. DIRECTORY_SEPARATOR .$f) ? $this->handler->filesize($subURI. DIRECTORY_SEPARATOR .$f) : NULL),
					'file:description' => NULL,
					'actions(file)' => NULL,
					));
				}
			}
			ksort($lines);
			
			$set = array('title'=>'Index of '.$subURI.'/','include:lines()'=>implode($lines),'footer'=>NULL,'URI'=>$this->URI,'label'=>$this->label);
			return parse_template('templates/overview.html', $set);
		}
		else{
			return $this->build_connect($subURI);
		}
	}
	function build_connect($URI=NULL){
		$set = array('URI'=>'file:'.dirname(__FILE__).DIRECTORY_SEPARATOR, 'label'=>'local');
		return parse_template('templates/connect.html', $set);
	}
}

if(TRUE){
	if(function_exists('XLtrace_about_class')){ print '<pre>'; print XLtrace_about_class('FSbrowser'); print '</pre><hr/>'; }
	#/*debug*/ if(isset($_GET)){ print '<pre>$_GET = '; print_r($_GET); print '</pre>'; }
	#/*debug*/ if(isset($_POST)){ print '<pre>$_POST = '; print_r($_POST); print '</pre>'; }
	#/*debug*/ if(isset($_SESSION)){ print '<pre>$_SESSION ='; print_r($_SESSION); print '</pre>'; }
	$subURI = (isset($_GET['URI']) ? $_GET['URI'] : NULL);
	$label = preg_replace("#^[/]?([^/]+)[/](.*)$#", "\\1", $subURI);
	$baseURI = FSbrowser::getURI_by_SESSION_and_PREFIX($subURI);
	#/*debug*/ print '<pre>$base_URI = '; print_r($baseURI); print ' { '.$subURI.' | '.(isset($_GET['URI']) ? $_GET['URI'] : NULL).' }</pre>';

	$FSbrowser = new FSbrowser($baseURI, $label);
	#/*debug*/ print '<pre>'; print_r($FSbrowser); print '</pre>';
	if(isset($_POST['action']) && $_POST['action'] == 'FSbrowser.connect'){ FSbrowser::connect($_POST['URI'], $_POST['label'], TRUE); }
	#if(!isset($_GET['URI'])){ print $FSbrowser->build_connect(); }
	print $FSbrowser->build($subURI);
	#/*debug*/ print '<pre>$->scandir('.$subURI.') ='; print_r( $FSbrowser->handler->scandir($subURI) ); print '</pre>';
}
?>