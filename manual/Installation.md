#Installation of FSnode
- get the most recent version of **FSnode** through *Git* or use one of the other methods provided by *GitHub*.
```bash
git clone https://github.com/sentfanwyaerda/FSnode.git
```
##Settings
- Set the temporarily directory for FSnode its extensions. Some need read-write-access to buffer the flow of data. A good suggestion is */tmp/FSnode/* or the default:
```php
define('FSnode_TEMP_DIRECTORY', dirname(__FILE__).'/tmp/');
```
- You are allowed to delete the *FIXES*, they are optional.
###Auto loading extensions
By default all extensions are loaded. **$FSnode_loader** is set by default to be *TRUE*, or if unset presumed to be *TRUE*. *./extension/all.php* takes care of loading all --or the selection of-- extensions.
You can make a selection of (by default) available extensions by listing their postfix-name:
```php
$FSnode_loader = array('local', 'ftp');
```
Afterwards you are still able to include (previous excluded) extensions by:
```php
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'extension'.DIRECTORY_SEPARATOR.'my_ext.php');
```
or let FSnode load it for you:
```php
FSnode::load_extension('ftp');
```
##Complete the installation
- On a production server: delete or disable [FSbrowser](https://github.com/sentfanwyaerda/FSnode/blob/master/manual/FSbrowser.md) (for security reasons)
- (re)set the rights of the files and directories
```bash
chmod 0644 ../FSnode -R
chmod +x ../FSnode; chmod +x ../FSnode/extension; chmod +x ../FSnode/manual
chmod +wx ../FSnode/tmp
```