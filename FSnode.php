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

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'FSnode.settings.php');

if(!class_exists('Xnode')){
	class Xnode { }
}

#for use of hooks:
define('PREFIX', 'prefix');
define('POSTFIX', 'postfix');
define('ITTERATION', 'itteration');
define('FAIL', 'fail');
if(!defined('FSnode_ALLOW_CODE_EXECUTE')){ define('FSnode_ALLOW_CODE_EXECUTE', FALSE); }

function FSnode($a=NULL, $b=FALSE, $c=FALSE, $d=FALSE){
	#assume $a is an URI, then $b is (bool ? get result/document : connected $FSnode object). Ignore $c and $d.
	$fs = FSnode::URI_load($a);
	if(is_bool($b)){ return ($b ? $fs->read() : $fs); }
	else{ return /*error*/ FALSE; }
}

class FSnode extends Xnode {
	public function Version($f=FALSE){ return '0.3.0'; }
	public function Product_url($u=FALSE){ return ($u === TRUE ? "https://github.com/sentfanwyaerda/FSnode" : "http://sent.wyaerda.org/FSnode/?version=".self::Version(TRUE).'&license='.str_replace(' ', '+', self::License()) );}
	public function Product($full=FALSE){ return "FSnode".(!($full===FALSE) ? (is_array($full) ? '(extended with '.preg_replace('#(, )([A-Z]+)$#i', ' and \\2', strtoupper(implode(', ', self::list_FSnode_extensions()))).') ' : NULL)." ".self::version(TRUE).(class_exists('Xnode') && method_exists('Xnode', 'Product') ? '/'.Xnode::Product(TRUE) : NULL) : NULL); }
	public function License($with_link=FALSE){ return ($with_link ? '<a href="'.self::License_url().'">' : NULL).'cc-by-nd 3.0'.($with_link ? '</a>' : NULL); }
	public function License_url(){ return 'http://creativecommons.org/licenses/by-nd/3.0/'; }
	public function Product_base(){ return dirname(__FILE__).DIRECTORY_SEPARATOR; }
	public function Product_file($full=FALSE){ return ($full ? self::Product_base() : NULL).basename(__FILE__); }
	public function AutoUpdate(){
		if(FSnode_ALLOW_CODE_EXECUTE){
			$fsnode = FSnode('file:'.FSnode::Product_base());
			$fsnode->add_hook('git');
			#backup FSnode.settings.php
			$fsnode->refresh('origin');
			#restore FSnode.settings.php
		}
		else{
			return FALSE;
		}
	}
		
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
	public function add_hook($hook, $auto_load=FALSE){
		if(!($auto_load===FALSE) && !class_exists( 'FSnode_'.$hook )){ self::load_FSnode_extension($hook); }
	
		if(class_exists( 'FSnode_'.$hook) && !in_array($hook, $this->hooks)){ $this->hooks[] = (string) $hook; return TRUE; }
		else{ return FALSE; }
	}
	public function load_extension($ext=FALSE){ return self::load_FSnode_extension($ext); }
	public function load_FSnode_extension($ext=FALSE){
		if($ext === TRUE){ /*load all extensions*/
			foreach(scandir(FSnode_EXTENSION_DIRECTORY) as $f){
				if(!in_array($f, array('.', '..', 'all.php')) && preg_match("#(.*).php$#i", $f, $buffer)){
					FSnode::load_FSnode_extension($buffer[1]);
				}
			}
		}
		elseif($ext === FALSE){ return FALSE; }
		elseif(is_array($ext)){
			$bool = TRUE;
			foreach($ext as $buffer){
				$bool = ($bool && FSnode::load_FSnode_extension($buffer) );
			}
			return $bool;
		}
		else{
			$p = FSnode_EXTENSION_DIRECTORY.preg_replace("#[^a-z0-9_]#i", "", $ext).'.php';
			if(file_exists($p)){
				require_once($p);
			} else{ return FALSE; }
		}
		return TRUE;
	}
	public /*array*/ function list_FSnode_extensions(){
		$set = array();
		$classlist = get_declared_classes();
		foreach($classlist as $d){
			if(preg_match("#^FSnode_(.*)$#i", $d, $buffer) && class_exists("FSnode_".strtolower($buffer[1])) && (defined('FSnode_'.strtoupper($buffer[1]).'_URI_PREFIX') || defined('FSnode_'.strtoupper($buffer[1]).'_SCHEME')) ){
				$set[] = strtolower($buffer[1]);
			}
		}
		return $set;
	}
	
	private $URI = NULL;
	public /*FSnode*/ function URI_load($URI, $auto_connect=TRUE){
		$extension = FSnode::get_FSnode_extension_by_URI($URI);
		//*debug*/ print '<!-- FSnode extension of "'.$URI.'" is '.print_r($extension, TRUE).' -->'."\n";
		if($extension === FALSE){ return FALSE; }
				
		#if(isset($this)){ $o =& $this; } else {
			$ext = "FSnode_".strtolower($extension);
			$FSnode = new $ext();
		#}
		$FSnode->URI = $URI;
		
		$hooks = FSnode::get_FSnode_hooks_by_URI($URI);
		if(is_array($hooks)){
			foreach($hooks as $hook){ $FSnode->add_hook($hook); }
		}
		
		if($auto_connect === TRUE){ $FSnode->connect($URI); }
		return $FSnode;
	}
	public /*string|FALSE*/ function get_FSnode_extension_by_URI($URI){
		$set = self::parse_url($URI);
		
		foreach(self::list_FSnode_extensions() as $extension){
			if(defined('FSnode_'.strtoupper($extension).'_SCHEME') && isset($set['scheme'])){
				$schemes = explode(' ', constant('FSnode_'.strtoupper($extension).'_SCHEME'));
				$s = explode('+', $set['scheme']);
				if(in_array(end($s), $schemes)){ return $extension; }
			}
			if(defined('FSnode_'.strtoupper($extension).'_URI_PREFIX')){
				$pattern = explode(' ', constant('FSnode_'.strtoupper($extension).'_URI_PREFIX'));
				foreach($pattern as $prefix){
					if(preg_match("#^(".str_replace(array('\s', '\\'), array('[a-z]', '\\\\'), $prefix).")#i", $URI)){ return $extension; }
				}
			}			
		}
		return FALSE;
	}
	public /*array*/ function get_FSnode_hooks_by_URI($URI){
		$hooks = array();
		$set = self::parse_url($URI);
		if(isset($set['scheme'])){
			$hooks = explode('+', $set['scheme']);
			/*untested*/ array_pop($hooks);
		}
		return $hooks;
	}
	/*public|private*/ /*string*/ function _filename_attach_prefix($filename=NULL){
		if(isset($this->URI)){
			$chroot = self::parse_url($this->URI, 'path');
		 	if(file_exists($chroot) /*&& is_dir($chroot)*/){
				/*fix*/ if(substr($chroot, -1) == '/'){ $chroot = substr($chroot, 0, -1); }
				if(!is_dir($chroot)){ $chroot = dirname($chroot); }
			
			
				if(!preg_match("#^(".$chroot.")#i", $filename)){ #check for prefix: do not double prefix
					$filename = $chroot.(!preg_match("#^[/\\/]#i", $filename) ? DIRECTORY_SEPARATOR : NULL).$filename;
				}
				#/*debug*/ print "<!-- \n\t".$filename."\n=\t".realpath($chroot)."\n=\t".realpath($filename)."\n -->\n";
				if(!(substr(realpath($filename), 0, strlen(realpath($chroot))) == realpath($chroot))){ return FALSE; /*out of chroot*/ }
			}
		}	
		return (string) $filename;
	}
	
	public function parse_url($url, $component=-1){
		if(!($component === -1) && is_string($component) && defined('PHP_URL_'.strtoupper($component))){ $component = constant('PHP_URL_'.strtoupper($component));}
	
		$cfext = array('zip',/*tar.*/'gz','tar','bz','rar','iso','gzip','7z'); #compressed file extensions
		$buffer = explode('://', $url); $scheme = (isset($buffer[0]) && isset($buffer[1]) ? $buffer[0] : NULL);
		if(preg_match("#[\.](".implode('|', $cfext).")[/]#i", $url)){ #in case you request a file from an compressed archive
			$set = parse_url($url);
			if(preg_match("#^((.*)[\.](".implode('|', $cfext)."))([/](.*))$#", $set['path'], $buffer)){
				$set['fullpath'] = $set['path'];
				$set['path'] = $buffer[1];
				$set['path-query'] = $buffer[4];
				if(!preg_match("#".$buffer[3]."#", $set['scheme'])){ $set['scheme'] = $buffer[3].'+'.$set['scheme']; }
				$set['pathtype'] = 'archive';
			}
			//return $set;
		}
		elseif(substr($url, 0, 2) == '\\\\'){
			$set = parse_url($url);
			if(preg_match("#^//([^/]+)(.*)$#i", str_replace('\\', '/', $set['path']), $buffer)){
				$set['scheme'] = 'smb';
				$set['host'] = $buffer[1];
				$set['path'] = /*str_replace('/', '\\',*/ $buffer[2] /*)*/;
			}
		}
		elseif(preg_match("#^([0-9]{1,3}[.][0-9]{1,3}[.][0-9]{1,3}[.][0-9]{1,3}|localhost)$#i", $url)){ $set = array('host'=>$url); }
		elseif(preg_match("#[:]#", $scheme)){
			$set = parse_url(str_replace($scheme, str_replace(':', '+', $scheme), $url));
		}
		elseif($set = parse_url($url /*, $component*/)){ #in all regular cases #component will be selected by FSnode::parse_url not its PHP-counterpart
			//return $set;
		} 
		elseif(preg_match("#^([a-z0-9+.-]+)[\:]([/]+)([^\?\#]+)([\?]([^\#]+))?([\#](.*))?$#i", $url, $buffer_one)){ #in failures of php::parse_url(), like email-address-usernames, or 'postgres:///'
			$set = array();
			$set['scheme'] = $buffer_one[1];
			$hierarchical_prefix = $buffer_one[2];
			$hierarchical = $buffer_one[3];
			#/*debug*/ $set['b_one'] = print_r($buffer_one, TRUE);
			if(strlen($hierarchical_prefix) == 2){
				preg_match("#^([^/]+)(.*)$#i", $hierarchical, $buffer_two);
				$authority = $buffer_two[1];
				#scenario user:pass@domain:port gets filtered by parse_url, assume scheme based authentication on central server with an (emailaddress) user.
				switch($set['scheme']){
					#extend for more cases!!!
					case 'dropbox': $set['host'] = 'dropbox.com'; break;
					case 'imap+gmail': case 'gmail': $set['host'] = 'gmail.com'; break;
					default: #do nothing
				}
				if(preg_match("#^([^:]+)[:](.*)#i", $authority, $buffer_three) && /*is emailaddress*/ preg_match("#^([a-z0-9_-]+)@([a-z0-9.-]+)$#i", $buffer_three[1]) ){
					$set['user'] = $buffer_three[1];
					$set['pass'] = $buffer_three[2];
				} else {
					$set['user'] = $authority;
				}
				$set['path'] = $buffer_two[2];
			}
			elseif($set['scheme'] == 'postgres' && strlen($hierarchical_prefix) == 3){
				$set['host'] = 'localhost';
				$set['path'] = $hierarchical;
			}
			else{
				$set['path'] = $hierarchical_prefix.$hierarchical;
			}
			if(isset($buffer_one[5])){ $set['query'] = $buffer_one[5]; }
			if(isset($buffer_one[7])){ $set['fragment'] = $buffer_one[7]; }
			//return $set;
		}
		else { return FALSE; }
		
		/*fix*/ if(!isset($set) || !is_array($set)){ $set = array(); }
		
		/*debug*/ $set = array_merge(array('original' => $url, 'pathtype' => NULL), $set);
		
		#analyses of $set['path']:
		if(isset($set['scheme']) && in_array($set['scheme'], array('mysql','postgres') )){ $set['pathtype'] = 'database'; }
		elseif(preg_match("#[/\\\/]#i", $url)){ $set['pathtype'] = 'directory'; }
		else { $set['pathtype'] = 'urn'; } #mixed,urn,directory,archive,database,email,.. 
		
		
		$set['separator'] = ($set['pathtype'] == 'urn' ? ':' : '/');
		switch($set['pathtype']){
			case 'urn':
				if(isset($set['path']) && preg_match("#^[".$set['separator']."]?(([^".$set['separator']."]+)[".$set['separator']."])?(.*)$#i", $set['path'], $buffer)){
					if(isset($buffer[2]) && strlen($buffer[2]) > 0){ $set['namespace'] = $buffer[2]; }
					/*redundancy*/ elseif(isset($set['scheme']) && $set['scheme'] != 'urn'){ $set['namespace'] = $set['scheme'];}
					$set['resource'] = $buffer[3];
				}
				break;
			case 'database':
				if(preg_match("#^[".$set['separator']."]?([^".$set['separator']."]+)([".$set['separator']."](.*))?$#i", $set['path'], $buffer)){
					$set['database'] = $buffer[1];
					if(isset($buffer[3])){ $set['table'] = $buffer[3]; }
				}
				break;
			default:
				$set['pathtype'] = 'directory';
				foreach(array('.zip','.tgz','.tar.gz','.bz','.7z') as $ext){
					if(isset($set['path']) && preg_match("#".$ext."#i", $set['path'])){ $rawpath = preg_replace("#".$ext."#i", "×", $set['path']); $rawext = $ext; }
				}
				if(isset($rawpath) && preg_match("#^([^×]+)[×][".$set['separator']."]?(.*)$#i", $rawpath, $buffer)){
					$set['archive'] = $buffer[1];
					$set['archivetype'] = substr($rawext, 1);
					$set['fullarchive'] = $set['archive'].$rawext;
					#/*fix*/ if(substr($set['fullarchive'], 0,1) == $set['separator']){ $set['fullarchive'] = substr($set['fullarchive'],1); }
					/*fix*/ if(substr($set['fullarchive'], -1) == $set['separator']){ $set['fullarchive'] = substr($set['fullarchive'],0,-1); }
					$remainingpath = (isset($set['path-query']) ? $set['path-query'] : $buffer[2]);
					$set['pathtype'] = 'archive';
				}
				/*fix*/ if(!isset($remainingpath)){ $remainingpath = (isset($set['path']) ? $set['path'] : NULL); }
				/*fix*/ $remainingpath = str_replace('\\','/', $remainingpath);
				if(substr($remainingpath, -1) == '/'){ $set['directory'] = substr($remainingpath, 0, -1); }
				else{
					$set['directory'] = dirname($remainingpath);
					$set['filename'] = basename($remainingpath);
				}
				/*fix*/ if(isset($set['path']) && preg_match("#[\\\]#", $set['path'])){ $set['directory'] = str_replace('/', '\\', $set['directory']); $set['separator'] = '\\'; }
				if(isset($set['filename']) && preg_match("#^(.*)[.]([^.]+)$#i", $set['filename'], $buffer)){
					$set['filetype'] = $buffer[2]; #extension
					//$set['filemime'] = #?
				}
		}
		
		#analyses of $set['query']:
		//$set['assigner'] = (=|:);
		//$set['divider'] = (&|;)
		//$set['masterdivider'] =  ($set['pathtype'] == 'urn' ? ';' : '?');
		if(isset($set['query'])){
			parse_str($set['query'], $set['queryexpanded']);
			$set['queryamount'] = count($set['queryexpanded']);
		}
		
		#analyses of $set['user']
		//$set['anonymous'] = (yes|no);
		//$set['emailaddress'] = (yes|no);
		
		if($component === -1 || (is_array($component) && count($component) == 0) || !is_array($set)){ return $set; }
		elseif(is_array($component)){
			$subset = array();
			foreach($component as $key){
				switch($key){
					case PHP_URL_SCHEME: $subset['scheme'] = (string) $set['scheme']; break;
					case PHP_URL_HOST: $subset['host'] = (string) $set['host']; break;
					case PHP_URL_PORT: $subset['port'] = (int) $set['port']; break;
					case PHP_URL_USER: $subset['user'] = (string) $set['user']; break;
					case PHP_URL_PASS: $subset['pass'] = (string) $set['pass']; break;
					case PHP_URL_PATH: $subset['path'] = (string) $set['path']; break;
					case PHP_URL_QUERY: $subset['query'] = (string) $set['query']; break;
					case PHP_URL_FRAGMENT: $subset['fragment'] = (string) $set['fragment']; break;
					default: 
						if(isset($set[$key])){ $subset[strtolower($key)] = $set[$key]; }
						#else ignores $key
				}
			}
			return $subset;
		}
		else{
			/*fix*/ foreach(array('scheme','host','port','user','pass','path','query','fragment') as $c){ if(!isset($set[$c])){ $set[$c] = NULL; }}
			if(isset($set[strtolower($component)])){ return $set[strtolower($component)]; }
			switch($component){
				case PHP_URL_SCHEME: return (string) $set['scheme']; break;
				case PHP_URL_HOST: return (string) $set['host']; break;
				case PHP_URL_PORT: return (int) $set['port']; break;
				case PHP_URL_USER: return (string) $set['user']; break;
				case PHP_URL_PASS: return (string) $set['pass']; break;
				case PHP_URL_PATH: return (string) $set['path']; break;
				case PHP_URL_QUERY: return (string) $set['query']; break;
				case PHP_URL_FRAGMENT: return (string) $set['fragment']; break;
				default:
					if(isset($set[strtolower($component)])){ return $set[strtolower($component)]; }
			}
		}
		return $set;
	}
	public function rebuild_url($arg=array()){
		if(isset($arg['user']) && /*is_emailaddress*/ preg_match("#^[a-z0-9_-]+[@][a-z0-9.-]+$#i", $arg['user']) && in_array($arg['scheme'], array('dropbox','imap+gmail'))){
					#extend for more cases!!!
			$hierarchal = $arg['scheme'].'://'.$arg['user'].(isset($arg['pass']) ? ':'.$arg['pass'] : NULL).(substr($arg['path'], 0, 1) != '/' ? '/' : NULL).$arg['path'];
		}
		elseif(isset($arg['host'])){
			$hierarchal = (isset($arg['scheme']) ? $arg['scheme'] : 'unknown').'://'.(isset($arg['user']) ? $arg['user'].(isset($arg['pass']) ? ':'.$arg['pass'] : NULL).'@' : NULL).$arg['host'].(isset($arg['port']) ? ':'.$arg['port'] : NULL).(isset($arg['path']) ? (substr($arg['path'], 0, 1) != '/' ? '/' : NULL).$arg['path'] : NULL).(isset($arg['path-query']) ? $arg['path-query'] : NULL);
		}
		else{ $hierarchal = (isset($arg['scheme']) ? $arg['scheme'].':' : NULL).$arg['path']; }
		$str = $hierarchal.(isset($arg['query']) ? '?'.(is_array($arg['query']) ? http_build_query($arg['query']) : $arg['query']) : NULL).(isset($arg['fragment']) ? '#'.$arg['fragment'] : NULL);
		return $str;
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
	
	public /*bool*/ function is_connected(){ return FALSE; }
	
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
		return $result;
	}
	
	#Basic extended
	public /*mixed*/ function execute($line=NULL){
		if(FSnode_ALLOW_CODE_EXECUTE){
			exec($line);
		}
	}
	public /*bool*/ function refresh($tag=NULL){
		return TRUE;
	}
	public /*string*/ function mime_content_type($filename){
		if(self::is_connected() && function_exists('finfo_open') && function_exists('finfo_file') && function_exists('finfo_close') ){
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($finfo, $this->_filename_attach_prefix( (string) $filename ) );
			finfo_close($finfo);
			return $mime;
		}
		return NULL;
	}
}
/*To make sure FSnode_local is loaded*/
if(file_exists(FSnode_EXTENSION_DIRECTORY.'local.php')){ require_once(FSnode_EXTENSION_DIRECTORY.'local.php'); }
?>