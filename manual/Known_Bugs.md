Known Bugs
==========
While FSnode is still in its infant stage, you will encounter bugs. You can report bugs by sending an email to: fsnode *at* sent *dot* wyaerda *dot* org.

##Open
- **[FSbrowser](https://github.com/sentfanwyaerda/FSnode/blob/master/manual/FSbrowser.md)** can't open/preview/ ~~status~~ files. It treats every URL as a directory. *Will be resolved in v0.3!*
- **$fs->scan('./')** does not always mount to the correct corresponding directory. The URI *file:/some/where/* works, but */some/where/* not. Even more problems survicing in **FSnode_ftp** with *ftp://localhost/*.

##Untested
- **class [FSnode_ftp](https://github.com/sentfanwyaerda/FSnode/blob/master/manual/FSnode_ftp.md)**
- **FSnode [Hooks](https://github.com/sentfanwyaerda/FSnode/blob/master/manual/Hooks.md)**

##Resolved
- ~~**FSnode::load_URI()** will not handle other URI's then 'file:/'.~~ #v0.2.11 (a.k.a. v0.3.0 beta)

##Security Issus:
- **[FSbrowser](https://github.com/sentfanwyaerda/FSnode/blob/master/manual/FSbrowser.md)** allows you to mount *file:/* (your root directory) to browse your files. In future versions FSbrowser might even allow you to read/write files. During [installation](https://github.com/sentfanwyaerda/FSnode/blob/master/manual/Installation.md) you are encouraged to delete or disable FSbrowser, MANUALLY! *FSbrowser is provided ONLY for development and testing.*
	- **FSbrowser** will only be loaded when **ALLOW_FSbrowser** is set **TRUE**. *see [Installation](https://github.com/sentfanwyaerda/FSnode/blob/master/manual/Installation.md)*