<?php

require_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'FSnode.php');

#FSnode::load_extension('ftp');
FSnode::load_extension(TRUE);

print '<pre>';
print 'extensions: '.print_r(FSnode::list_FSnode_extensions(), TRUE)."\n";
print '</pre>';
?> 
