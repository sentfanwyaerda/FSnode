 <?php
require_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'FSnode.php');

if(!isset($_GET['URI'])){ $_GET['URI'] = 'ftp://localhost/'; }

#html:form
print '<form method="get"><input name="URI" value="'.$_GET['URI'].'" style="width: 300px;"/><input type="submit" value="&gt;" /></form>';

print '<style> .error { color: red; } .succes { color: green; } .action { color: blue; } </style>';

#debug
print '<pre>';
$set = FSnode::parse_url($_GET['URI']);
print $_GET['URI'].' => '; print_r($set);

if(!isset($set['host']) || (isset($set['scheme']) && !in_array($set['scheme'], array('ftp','sftp','ftps')))){
	print '<span class="error">[your URI is not a refenece to an FTP server]</span>'; exit;
}

if(isset($set['scheme']) && in_array($set['scheme'], array('sftp','ftps'))){
	$ftp = ftp_ssl_connect($set['host'], (isset($set['port']) ? $set['port'] : 21));
}
else{
	$ftp = ftp_connect($set['host'], (isset($set['port']) ? $set['port'] : 21));
}
print '<span class="action">:connect:</span> $ftp <span class="action">['.(isset($set['scheme']) && in_array($set['scheme'], array('sftp','ftps')) ? 'secure ' : NULL).'host=</span>'.$set['host'].(isset($set['port']) ? ' <span class="action">port=</span>'.$set['port'] : NULL).'<span class="action">]</span> = '; print_r($ftp); print (!($ftp===FALSE) ? ' <span class="succes">[connected]</span>' : ' <span class="error">[failed]</span>')."\n";

if(!($ftp===FALSE)){

	$login = ftp_login($ftp, (isset($set['user']) ? $set['user'] : 'anonymous'), (isset($set['pass']) ? $set['pass'] : NULL));
	print '<span class="action">:login:</span> $login <span class="action">[user=</span>'.(isset($set['user']) ? $set['user'] : 'anonymous').' <span class="action">pass=</span>'.(isset($set['pass']) ? $set['pass'] : NULL).'<span class="action">]</span> = '; print_r($login); print ($login === TRUE ? ' <span class="succes">[authenticated]</span>' : ' <span class="error">[failed]</span>')."\n";

	$current = ftp_pwd($ftp);
	print '<span class="action">:current:</span> = '; print_r($current); print "\n";

	$list = ftp_nlist($ftp, $current);
	print '<span class="action">:list:</span> = '; print_r($list); print "\n";

	print '<span class="action">:close:</span> = '; print_r(ftp_close($ftp));

}

print '</pre>';
?>