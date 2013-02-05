
###v0.2.7
- added detection in **FSnode::parse_url()** for files inside compressed archives.
- have been wondering about how **[parse_url()](http://php.net/parse_url)** maps the [URI_scheme](http://en.wikipedia.org/wiki/URI_scheme#Examples). Desided **FSnode::parse_url()** will have to return more *component* types: authority, directory, filename, extension, server (scheme://host:port), *(array)* query. In case of a compressed archive, the archive will be the directory and the internal path noted as filename.
	- should start wondering about security: implement a method to seperate pass from the complete URI, or encrypt within, to be saved..

###v0.2.6
- renamed *settings.php* to *FSnode.settings.php*
- Settings: the location of the extension-map will be set by **FSnode_EXTENSION_LOCATION**
- adds **FSnode::autoUpdate()** to allow for getting the more recent version of **FSnode**
- adds **FSnode::execute()** and **FSnode::refresh()** methods to the default class diagram
