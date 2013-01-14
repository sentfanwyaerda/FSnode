Known Bugs
==========
While FSnode is still in its infant stage, you will encounter bugs. You can report bugs by sending an email to: fsnode *at* sent *dot* wyaerda *dot* org.

##Open
- **FSnode::load_URI()** will not handle other URI's then 'file:/'. *Will be resolved in v0.3!*
- **[FSbrowser](https://github.com/sentfanwyaerda/FSnode/blob/master/manual/FSbrowser.md)** can't open/preview/status files. It treats every URL as a directory. *Will be resolved in v0.3!*

##Untested
- **class [FSnode_ftp](https://github.com/sentfanwyaerda/FSnode/blob/master/manual/FSnode_ftp.md)**
- **FSnode [Hooks](https://github.com/sentfanwyaerda/FSnode/blob/master/manual/Hooks.md)**

##Resolved

##Security Issus:
- [FSbrowser](https://github.com/sentfanwyaerda/FSnode/blob/master/manual/FSbrowser.md) allows you to mount *file:/* (your root directory) to browse your files. In future versions FSbrowser might even allow you to read/write files. During [installation](https://github.com/sentfanwyaerda/FSnode/blob/master/manual/Installation.md) you are encouraged to delete or disable FSbrowser, MANUALLY! *FSbrowser is provided ONLY for development and testing.*