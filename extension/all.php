<?php
if(!defined('FSnode_EXTENSIONS_LOADED')){
	foreach(scandir('./') as $f){
		if(!in_array($f, array('.', '..', 'all.php')) && preg_match("#.php$#i", $f) && file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.$f) && (!isset($FSnode_loader) || $FSnode_loader === TRUE || (is_array($FSnode_loader) && in_array(basename($f, '.php'), $FSnode_loader)) )){
			require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.$f);
		}
	}
	define('FSnode_EXTENSIONS_LOADED', TRUE);
}
?>