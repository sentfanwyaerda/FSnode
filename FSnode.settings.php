<?php
/*************
*  SETTINGS  *
*************/
@define('FSnode_TEMP_DIRECTORY', dirname(__FILE__).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR);
@define('FSnode_EXTENSION_DIRECTORY', dirname(__FILE__).DIRECTORY_SEPARATOR.'extension'.DIRECTORY_SEPARATOR);
@define('ALLOW_FSbrowser', TRUE);
@define('FSnode_ALLOW_CODE_EXECUTE', FALSE);

$base = (isset($base) ? $base : $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR );

/*************
*   FIXES    *
*************/
#fix:pre PHP5.4.0
if(!defined('SCANDIR_SORT_ASCENDING')){ define('SCANDIR_SORT_ASCENDING', (int) 0 ); }

#This is only required for my personal flavor of FSnode; feel free to remove this line
if(file_exists(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Xnode.php')){ require_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Xnode.php'); }

/*************
* EXTENSIONS *
*************/
$FSnode_loader = TRUE; #for only loading a few use: array('local','ftp')
if(file_exists(FSnode_EXTENSION_DIRECTORY.'all.php')){ require_once(FSnode_EXTENSION_DIRECTORY.'all.php'); }
?>
