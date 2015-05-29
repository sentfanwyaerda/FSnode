<?php 
require_once(dirname(__FILE__).'/FSmirror.php');

$local = FSnode::URI_load('file:'.dirname(dirname(dirname(__FILE__))).'/test-mirror/a/');
$remote = FSnode::URI_load('file:'.dirname(dirname(dirname(__FILE__))).'/test-mirror/b/');

$ignore = FSmirror::ignore(array('^.git','^.htaccess$'));
$recursive = TRUE;

$target = '';



$mirror = new FSmirror($local, $remote, $ignore, $recursive);

print '<pre>';
//print_r($local);
//print_r($remote);
//print_r($mirror);

print_r($mirror->scandir($target) );
print 'COMPARE: '; print_r($compare = $mirror->compare($target) ); //print json_encode($compare);
//print 'PUSH: '; print_r($mirror->push($target) );
//print 'PULL: '; print_r($mirror->pull($target) );
print 'SYNC: '; print_r($mirror->sync($target) );


print_r($mirror);
print '</pre>';
?>