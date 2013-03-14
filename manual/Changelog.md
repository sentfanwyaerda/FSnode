###v0.2.10 (a.k.a. v0.3.0 alpha)
- added **FSnode::list_FSnode_extensions()**, **FSnode::get_FSnode_extension_by_URI()**, **FSnode::get_FSnode_hooks_by_URI()**, which will be used for dynamic mounting each **FSnode** object.
- each extension will be linked to an URI by scheme **FSnode_%_SCHEME** and by prefix **FSnode_%_URI_PREFIX**

###v0.2.9
- **ALLOW_FSbrowser** determines if **FSbrowser** is usable
- bug-fixes in **FSnode::parse_url()** (only remaining known bug is on 'feed:https://example.com/rss.xml', but 'feed+https://example.com/rss.xml' works!)

###v0.2.8
- expanded the analysed elements **FSnode::parse_url()** can give; filename, directory, archive, database, table, namespace, resource, separator, masterdivider, divider, assigner. And allows for returning a selection of those elements.
- adds **FSnode::mime_content_type()** method to the default class diagram

###v0.2.7
- added detection in **FSnode::parse_url()** for files inside compressed archives.
- have been wondering about how **[parse_url()](http://php.net/parse_url)** maps the [URI_scheme](http://en.wikipedia.org/wiki/URI_scheme#Examples). Desided **FSnode::parse_url()** will have to return more *component* types: authority, directory, filename, extension, server (scheme://host:port), *(array)* query. In case of a compressed archive, the archive will be the directory and the internal path noted as filename.
	- should start wondering about security: implement a method to seperate pass from the complete URI, or encrypt within, to be saved..

###v0.2.6
- renamed *settings.php* to *FSnode.settings.php*
- Settings: the location of the extension-map will be set by **FSnode_EXTENSION_LOCATION**
- adds **FSnode::autoUpdate()** to allow for getting the more recent version of **FSnode**
- adds **FSnode::execute()** and **FSnode::refresh()** methods to the default class diagram
