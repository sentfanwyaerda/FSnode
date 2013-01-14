#Hooks
**FSnode** allows you to add hooks to particular tasks. A hooks allows you to add additional behaviour to a particular action on the filesystem.
A good example is when you want to automatically commit the change to Git repository, after an file edit. In this case you want a POSTFIX-hook on *FSnode::write()* saying 'git add $filename'. You will find *FSnode_Git::postfix_hook_write()* exactly do that for you.

You will need to initialize your **FSnode** like:
```php
$fs = FSnode('file+git:/path/to/mount/');
```
or more explicite by:
```php
$fs = FSnode('file:/path/to/mount/'); $fs->add_hook('git');
/*or*/ $fs = FSnode('file:/path/to/mount/', array('git'));
```

##Implement a hook in your extension:
- Add a method in your extensions class:
```php
class FSnode_my_ext extends FSnode_local{
	function prefix_hook_read($args=array()){
		$filename = $args['filename'];
		#...
	}
}
```
- Enable your extension specific methods to initialize hooks:
```php
class FSnode_my_ext extends FSnode{
	function read($filename){
		$this->_hook(__METHOD__, array('filename'=>$filename), PREFIX); #calls for HOOK::prefix_hook_read()
		$result = #...
		$this->_hook(__METHOD__, array('filename'=>$filename,'result'=>$result), POSTFIX); # HOOK::postfix_hook_read();
		return $result;
	}
}
```