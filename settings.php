<?php
/*************
*  SETTINGS  *
*************/
define('FSnode_TEMP_DIRECTORY', dirname(__FILE__).'/tmp/');

/*************
*   FIXES    *
*************/
#fix:pre PHP5.4.0
if(!defined('SCANDIR_SORT_ASCENDING')){ define('SCANDIR_SORT_ASCENDING', -1); }

#This is only required for my personal flavor of FSnode; feel free to remove this line
if(file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Xnode.php')){ require_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Xnode.php'); }

/*************
* EXTENSIONS *
*************/
$FSnode_loader = TRUE; #for only loading a few use: array('local','ftp')
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'extension'.DIRECTORY_SEPARATOR.'all.php');
?>