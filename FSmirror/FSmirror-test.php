<?php 
require_once(dirname(dirname(__FILE__)).'/extension/ftp.php');
require_once(dirname(__FILE__).'/FSmirror.php');

$local = FSnode::URI_load((isset($_GET['local']) ? $_GET['local'] : 'file:'.dirname(dirname(dirname(__FILE__))).'/test-mirror/a/'));
$remote = FSnode::URI_load((isset($_GET['remote']) ? $_GET['remote'] : 'file:'.dirname(dirname(dirname(__FILE__))).'/test-mirror/b/'));

$ignore = FSmirror::ignore(explode(' ', (isset($_GET['ignore']) ? $_GET['ignore'] :'^.git ^.htaccess$') )); //array('^.git','^.htaccess$')
$recursive = (isset($_GET['recursive']) ? (bool) $_GET['recursive'] : TRUE);

$target = (isset($_GET['target']) ? $_GET['target'] : '');

$action = explode(' ', strtolower( (isset($_GET['action']) ? $_GET['action'] : 'compare sync') ));


$mirror = new FSmirror($local, $remote, $ignore, $recursive);

print '<pre>';
print '$local = '; print_r($local);
print "\n".'$remote = '; print_r($remote);
print "\n".'$mirror = '; print_r($mirror);
print "\n<hr/>\n";

print_r($mirror->scandir($target) );

if(in_array('compare', $action)){
	print 'COMPARE: '; print_r($compare = $mirror->compare($target) ); //print json_encode($compare);
}
if(in_array('push', $action)){
	print 'PUSH: '; print_r($mirror->push($target) );
}
if(in_array('pull', $action)){
	print 'PULL: '; print_r($mirror->pull($target) );
}
if(in_array('sync', $action)){
	print 'SYNC: '; print_r($mirror->sync($target) );
}

print "\n<hr/>\n";
print_r($mirror);

print '</pre>';
?>
