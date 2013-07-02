<?php
require_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'FSnode.php');

/*You need to load each extension before FSnode::load_URI() can detect */ FSnode::load_extension(TRUE);

if(!isset($_GET['URI'])){ $_GET['URI'] = 'file:'.FSnode::Product_base(); }

#html:form
print '<form method="get"><input name="URI" value="'.$_GET['URI'].'" style="width: 300px;"/><input type="submit" value="&gt;" /></form>';

#debug
print '<pre>';

print "<strong>FSnode::parse_url('".$_GET['URI']."')</strong> = ".print_r(FSnode::parse_url($_GET['URI']), TRUE); #.";\n";

print "<strong>FSnode::get_FSnode_extension_by_URI('".$_GET['URI']."')</strong> = <em>".print_r(FSnode::get_FSnode_extension_by_URI($_GET['URI']), TRUE)."</em>;\n";

print "<strong>\$fs = FSnode('".$_GET['URI']."');</strong> \t&rarr;";
$fs = FSnode((string) $_GET['URI']);
print_r($fs);

if(is_object($fs)){
	print "\n<strong>\$ls = \$fs->scan('./');</strong> \t&rarr;";
	$ls = $fs->scan('./');
	print_r($ls);
}

print '</pre>';
?>