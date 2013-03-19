<?php
$set = array(
	'file:/var/www/',
	'git+file:/var/www/',
	'/var/www/',
	'file:R:\development\FSnode\extension\\',
	'http://www.google.com/translate/',
	'git://github.com/sentfanwyaerda/FSnode.git',
	'mailto:pietje@puk.nl',
	'mailto:jsmith@example.com?subject=A%20Test&body=My%20idea%20is%3A%20%0A',
	'ftp://user001:secretpassword@private.ftp-servers.example.com/mydirectory/myfile.txt#section3',
	'ftp://localhost/',
	'localhost',
	'127.0.0.1',
	'imap://michael@example.org/INBOX',
	'callto:0123456789',
	'smb://workgroup;user:password@server/share/folder/file.txt',
	'javascript:document.reload()',
	'isbn:978-3-16-148410-0?json',
	'dropbox://user:pass@dropbox.com/path/file.ext',
	'dropbox://user@myserver.com:pass/path/file.ext',
	'mysql://username:password@hostname:3306/databasename',
	'mysql://localhost/database?unix_socket=/var/lib/mysql/socket',
	'postgres:///dbname?host=/path/to/socket',
	'geoip:192.168.1.1',
	'feed:https://example.com/rss.xml',
	'\\\\My_Computer\share_1\path\to\file.ext',
	'wordpress://user:pass@localhost/wordpress/p892.xml',
	'http://localhost/archive/history-2013-01.zip/index.html#content'
);


#for FSnode::parse_url() ( an extended version of parse_url() )
require_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'FSnode.php');

FSnode::load_extension(TRUE);

print '<table>';
foreach($set as $URI){
	#/*fixes*/ $URI = str_replace(array(':///'), array('://localhost/'), $URI);
	$sub = FSnode::parse_url($URI);
	print '<tr><td valign="top"><pre><b>'.$URI.'</b><br/><em>'.FSnode::rebuild_url($sub).'</em><br/><br/>'.(FSnode::get_FSnode_extension_by_URI($URI) ? '&rArr; opens with <u>FSnode_'.FSnode::get_FSnode_extension_by_URI($URI).'</u>' : NULL).(count(FSnode::get_FSnode_hooks_by_URI($URI)) > 0 ? ' +hooks: <i>'.implode('</i> & <i>', FSnode::get_FSnode_hooks_by_URI($URI)).'</i>' : NULL).'</pre></td><td valign="top"><pre>';
	foreach($sub as $n=>$v){ print '['.$n.str_repeat(' ', 13-strlen($n)).']: '.(is_array($v) ? substr(print_r($v, TRUE), 7, -3) : (string) $v).'<br/>'; }
	print "</pre></td></tr>\n";
}
print '</table>';
?>