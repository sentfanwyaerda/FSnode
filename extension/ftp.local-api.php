<?php
// input: $_POST = [chroot?, path, action, value]

/* CONFIGURE: */
require_once(dirname(dirname(__FILE__)).'/FSnode.settings.php');
//$base = dirname(__FILE__).DIRECTORY_SEPARATOR; // "/";
//$base = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR;

$secret = trim( (defined("FSnode_TEMP_DIRECTORY") && file_exists(FSnode_TEMP_DIRECTORY.'/secret.txt') ? file_get_contents(FSnode_TEMP_DIRECTORY.'/secret.txt') : 'SECRET') );
//$secret = "SECRET"; //NOTE: the default content of secret.txt has strlen[7], not [6]! #bugreport

/* SCRIPT: */
function p($a, $b=NULL, $c=NULL){ print $a.($b!=NULL ? " (".print_r($b, TRUE).($c!=NULL ? " = ".print_r($c, TRUE) : NULL).")" : NULL)."<br/>\n"; }
$upoch = (string) time();
$set = array_merge(array(
/*default*/	'key'=>md5('SECRET'.$upoch),
/*default*/	'timestamp'=>$upoch,
/*dummy*/	'base'=>$base,
		'chroot'=>'/',
		'path'=>'/',
/*dummy*/	'file'=>FALSE,
		'action'=>'none',
		'value'=>NULL,
	), $_POST);
//*debug*/ p("debug", $set);

if($set['timestamp'] > time()-(24*60*60)
	&& $set['timestamp'] <= time()
	&& md5($secret.$set['timestamp']) == $set['key']
){
	p("request is validated");
}
else{ p("not validated as valid request"); exit; }

if(!isset($set['action'])){ p("no action"); exit; }
if(!isset($set['path'])){ p("no path"); exit; }

$file = preg_replace("#[/]+#", "/", $base.$set['chroot'].$set['path']);
$set['file'] = $file;
if(!file_exists($file)){ p("file does not exist", $file); exit; }

switch(strtolower($set['action'])){
	case 'touch':
		if($set['value'] == NULL){ $set['value'] = time(); }
		touch($file, $set['value']);
		p("touch", $file, $set['value']);
		break;
	default:
		p("action is not implemented, yet", $set['action']);
}
/*debug*/ p("debug", $set);
exit;
?>
