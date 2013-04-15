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
	public function Version($f=FALSE){ return '0.3.2'; }
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
			/*fix*/ $subURI = str_replace(DIRECTORY_SEPARATOR, '/',  preg_replace("#^".$this->handler->realpath('/')."#", '', $this->handler->realpath($subURI)) );
			/*fix*/ if(substr($subURI, 0, 1) != DIRECTORY_SEPARATOR){ $subURI = DIRECTORY_SEPARATOR.$subURI; }
			/*debug*/ print '<!-- FSbrowser::build.$subURI: '.$subURI.' ['.$this->handler->is_dir($subURI).'] -->'."\n";
			if($this->handler->is_dir($subURI)){
				$list = $this->handler->scandir(str_replace(array('[',']','{','}'), array('\[','\]','\{','\}'), $subURI));
				/*fix: on scandir error */ if(!is_array($list)){ $list = array(); }
				foreach($list as $f){
					if($this->handler->realpath($subURI. DIRECTORY_SEPARATOR .$f) && !($f == '.')){
					$lines[$f] = parse_template('templates/line.html', array(
						'file:name.full' => ($f == '..' ? 'Parent Directory' : $f.($this->handler->is_dir($subURI. DIRECTORY_SEPARATOR .$f) ? DIRECTORY_SEPARATOR : NULL)),
						'file:url' => preg_replace('#[/'.DIRECTORY_SEPARATOR.']+#', '/', './FSbrowser.php?URI='.($f == '..' ? DIRECTORY_SEPARATOR .$this->label.dirname($subURI).DIRECTORY_SEPARATOR: DIRECTORY_SEPARATOR .$this->label.$subURI. DIRECTORY_SEPARATOR .$f.($this->handler->is_dir($subURI. DIRECTORY_SEPARATOR .$f) ? DIRECTORY_SEPARATOR : NULL)) ),
						'file:type.class' => ($f == '..' ? 'parent-directory' : ($this->handler->is_dir($subURI. DIRECTORY_SEPARATOR .$f) ? 'directory' : strtolower(preg_replace("#^(.*)[.]([a-z0-9]+)$#i", "\\2", $f)).' '.preg_replace("#[^a-z0-9-]#i", "-", $this->handler->mime_content_type($subURI. DIRECTORY_SEPARATOR .$f)) )),
						'file:modified.last' => date('d-M-Y H:i', $this->handler->filemtime($subURI. DIRECTORY_SEPARATOR .$f) ),
						'file:size' => ($this->handler->file_exists($subURI. DIRECTORY_SEPARATOR .$f) && !$this->handler->is_dir($subURI. DIRECTORY_SEPARATOR .$f) ? $this->_humanizeFileSize( $this->handler->filesize($subURI. DIRECTORY_SEPARATOR .$f)) : NULL),
						'file:description' => $this->handler->mime_content_type($subURI. DIRECTORY_SEPARATOR .$f),
						'actions(file)' => NULL,
						));
					}
				}
				ksort($lines);
			}
			
			$set = array('title'=>'Index of '.preg_replace('#[/]+#', '/', $this->handler->relativepath($subURI).($this->handler->is_dir($subURI) ? DIRECTORY_SEPARATOR : NULL)),'connection:status'=>($this->handler->is_connected() ? 'connected' : 'unconnected'),'include:lines()'=>implode($lines),'footer'=>NULL,'URI'=>$this->URI,'label'=>$this->label,'meta-information'=>$this->build_URI_info($subURI));
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
	function build_URI_info($subURI=NULL){
		$fullURI = $subURI.($this->handler->is_dir($subURI) ? '/' : NULL);
		$set = array(
		#	'' => $this->handler->($URI),
			'subURI' => $subURI,
			'URI' => $this->URI,
		#	'URI*' => $this->handler->parse_url($this->URI),
			'realpath' => $this->handler->realpath($fullURI),
			'realpath_URI' => $this->handler->realpath_URI($fullURI),
			'relativepath' => $this->handler->relativepath($fullURI),
		);
		foreach(array('fileatime'=>2,'filectime'=>2,'filegroup'=>1,'fileinode'=>1,'filemtime'=>2,'fileowner'=>1,'fileperms'=>1,'filesize'=>3,'filetype'=>1,'file_exists'=>9,'is_dir'=>9,'is_executable'=>9,'is_file'=>9,'is_readable'=>9,'is_writeable'=>9,'disk_free_space'=>3,'disk_total_space'=>3,'mime_content_type'=>1 /*,'stat'=>5*/) as $method=>$action){
			$set[$method] = $this->handler->$method($subURI);
			switch($action){
				case 2: /*timestamp*/ $set[$method] = ($set[$method] ? date("Y-m-d H:i:s", $set[$method]) : NULL); break;
				case 3: /*filesize*/ $set[$method] = ($set[$method] >= 0 ? self::_humanizeFileSize($set[$method]) : NULL); break;
				case 5: /*array*/ $set[$method] = print_r($set[$method], true); break;
				case 9: /*bool*/ $set[$method] = self::_bool_toString($set[$method]); break;
				default: /*do nothing*/
			}
		}
		/*fix*/ $set['raw'] = print_r($set, true);
		return parse_template('templates/information.html', $set);
	}
	
	
	#Additional functions
	private function _bool_toString($bool, $T="yes", $F="no", $E="error"){
		return (is_string($bool) ? $bool :(is_bool($bool) ? ($bool === TRUE ? $T : $F) : $E));
	}
	private function _humanizeFileSize($size){
		if ($size < 1024) {
			return $size .'B';
		} elseif ($size < 1048576) {
			return round($size / 1024, 2) .'Kb';
		} elseif ($size < 1073741824) {
			return round($size / 1048576, 2) . 'Mb';
		} elseif ($size < 1099511627776) {
			return round($size / 1073741824, 2) . 'Gb';
		} elseif ($size < 1125899906842624) {
			return round($size / 1099511627776, 2) .'Tb';
		} elseif ($size < 1152921504606846976) {
			return round($size / 1125899906842624, 2) .'Pb';
		} elseif ($size < 1180591620717411303424) {
			return round($size / 1152921504606846976, 2) .'Eb';
		} elseif ($size < 1208925819614629174706176) {
			return round($size / 1180591620717411303424, 2) .'Zb';
		} else {
			return round($size / 1208925819614629174706176, 2) .'Yb';
		}
	}

}

if(defined('ALLOW_FSbrowser') && ALLOW_FSbrowser === TRUE ){
	/*fix*/ FSnode::load_extension(TRUE);
	
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
	if(isset($_POST['action']) && $_POST['action'] == 'FSbrowser.connect'){ FSbrowser::connect($_POST['URI'], $_POST['label'], TRUE); if(isset($_POST['debug'])){ $_SESSION['debug'] = 'true'; } else { unset($_SESSION['debug']); } }
	#if(!isset($_GET['URI'])){ print $FSbrowser->build_connect(); }
	print $FSbrowser->build($subURI);
	#/*debug*/ print '<pre>$->scandir('.$subURI.') ='; print_r( $FSbrowser->handler->scandir($subURI) ); print '</pre>';
	
	/*debug*/ if(isset($_GET['debug']) || isset($_SESSION['debug'])){ print '<pre>$FSbrowser = '; print_r($FSbrowser); print '</pre>';}
}
?>