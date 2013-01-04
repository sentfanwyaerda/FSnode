FSnode
======
FSnode is a node-based Uniform FileSystem handler, written in [PHP](http://php.net/). It allows you to access all kind of filesystems in the exact same manner, with the same simple commands. You can write your webapplication once and let users switch filesystem/platform; 'mount' through URI.

###Example:
```php
/*initiate*/ $fs = new FSnode();
  /*or*/ $fs = FSnode::load_URI('file://path/to/here/');
  /*or*/ $fs = FSnode('file://path/to/here/');
/*to read a file*/ $raw = $fs->read('README.md');
/*to save a file*/ $fs->write('README.md', $raw);
```

###Extensions available:
- local filesystem ( *file:/* )
- FTP filesystems ( *ftp://* )
- ...more comming soon, like: Dropbox, Zip/gz/bz, Git

**Manual:** see *./manual/* ([on Github](https://github.com/sentfanwyaerda/FSnode/blob/master/manual/Introduction.md))


**License:** [cc-by-nd](http://creativecommons.org/licenses/by-nd/3.0/)
