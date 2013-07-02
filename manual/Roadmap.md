Roadmap
=======

###v0.3
- **FSnode_ftp**
- **FSbrowser**

###v0.4
- **FSnode_samba**
- **FSnode_dropbox** encapsuling the [BenTheDesigner](https://github.com/BenTheDesigner/Dropbox) API
- **FSarchive_targz**, **FSarchive_zip**
- **FSnode_[UAPI](https://github.com/sentfanwyaerda/UAPI)**

###v0.5
- **FSnode_git** (as *hook* and *chroot*)

###v0.6
- **FSnode_wordpress**; an API through mapping of XML-documents on top of [Wordpress](http://www.wordpress.org/), maybe even: **FSnode_ghostblog**

###Unassigned
- copy from a FSnode (chroot) to an other FSnode (chroot), like:
	```php
	$local = FSnode("file://some/where/");
	$ftp = FSnode("ftp://user@myserver.ltd/map/");
	$local->copy("example.txt", $ftp->get("/other/copy.txt") );
	/*and/or*/
	$file = /*(FSfile)*/ $local->get("example.txt");
	$file->copy_to($ftp, "/other/copy.txt");
	```
- **FScredentials** (and/or as part of [Heracles](https://github.com/sentfanwyaerda/Heracles)), a database to store authentication information, able to bundle them to an user-account, encrypting the (xml+encrypt?) db, only unlockable with valid password
