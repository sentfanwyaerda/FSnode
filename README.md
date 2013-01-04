FSnode
======
FSnode is a node-based Uniform FileSystem handler, written in [PHP](http://php.net/). It allows you to access all kind of file systems in the exact same manner, with the same simple commands. You can write your web application once and let users switch file system/platform; *mount* through URI.

###Example:
```php
/*initiate*/ $fs = new FSnode();
  /*or*/ $fs = FSnode::load_URI('file://path/to/here/');
  /*or*/ $fs = FSnode('file://path/to/here/');
/*to read a file*/ $raw = $fs->read('README.md');
/*to save a file*/ $fs->write('README.md', $raw);
```

###Extensions available:
- local file system ( *file:/* )
- FTP file systems ( *ftp://* )
- ...more coming soon, like: Dropbox, Zip/gz/bz, Git

**Manual:** see *./manual/Introduction.md* ([on Github](https://github.com/sentfanwyaerda/FSnode/blob/master/manual/Introduction.md))


**License:** [cc-by-nd](http://creativecommons.org/licenses/by-nd/3.0/)
