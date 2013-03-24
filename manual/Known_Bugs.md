Known Bugs
==========
While FSnode is still in its infant stage, you will encounter bugs. You can report bugs by sending an email to: fsnode *at* sent *dot* wyaerda *dot* org.

##Open
- **[FSbrowser](https://github.com/sentfanwyaerda/FSnode/blob/master/manual/FSbrowser.md)** can't open/preview/ ~~status~~ files. It treats every URL as a directory. *Will be resolved in v0.3!*
- **$fs->scan('./')** does not always mount to the correct corresponding directory. ~~The URI *file:/some/where/* works, but */some/where/* not.~~ It appears to be resulting from the need to work with an absolute path / realpath; **FSnode::_filename_attach_prefix** might be instable.
- **FSnode_ftp** has connection and listing issues on the testing ftp-server. e.g. **FSnode_ftp::connect()** and **FSnode_ftp::scan()**
- **$FSfile = FSnode::get()** and **FSnode::put($FSfile)**, see untested **FSfile**
- **FSnode::rebuild_url()** supports only the most regular URI-forms

##Untested
- **class [FSnode_ftp](https://github.com/sentfanwyaerda/FSnode/blob/master/manual/FSnode_ftp.md)**
- **FSnode [Hooks](https://github.com/sentfanwyaerda/FSnode/blob/master/manual/Hooks.md)**
- **class [FSfile](https://github.com/sentfanwyaerda/FSnode/blob/master/manual/FSfile.md)** and **class [FSarchive](https://github.com/sentfanwyaerda/FSnode/blob/master/manual/FSarchive.md)**

##Resolved
- ~~**FSnode::load_URI()** will not handle other URI's then 'file:/'.~~ #v0.2.11 (a.k.a. v0.3.0 beta)

##Security Issus:
- **[FSbrowser](https://github.com/sentfanwyaerda/FSnode/blob/master/manual/FSbrowser.md)** allows you to mount *file:/* (your root directory) to browse your files. In future versions FSbrowser might even allow you to read/write files. During [installation](https://github.com/sentfanwyaerda/FSnode/blob/master/manual/Installation.md) you are encouraged to delete or disable FSbrowser, MANUALLY! *FSbrowser is provided ONLY for development and testing.*
	- **FSbrowser** will only be loaded when **ALLOW_FSbrowser** is set **TRUE**. *see [Installation](https://github.com/sentfanwyaerda/FSnode/blob/master/manual/Installation.md)*
