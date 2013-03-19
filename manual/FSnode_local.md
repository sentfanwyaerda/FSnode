#FSnode_local
The local implementation **FSnode** is modelled after [PHP](http://php.net/) their [Filesystem Functions](http://www.php.net/manual/en/ref.filesystem.php) and [Directory Functions](http://www.php.net/manual/en/ref.dir.php). A multitude of listed functions are available as methods, with the same name and attributes and behaviour.
```php
$data = file_get_contents('index.html');
# equals:
$fsnode = new FSnode_local(); /*or*/ $fsnode = FSnode::load_URI('./');
$data = $fsnode->file_get_contents('index.html');
# and more generic:
$data = $fsnode->read('index.html');
```

*see the [Class Diagram](https://github.com/sentfanwyaerda/FSnode/blob/master/manual/Class_Diagram.md) for the complete list of usable methods.*