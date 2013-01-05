# Introduction
FSnode is a node-based Uniform FileSystem handler, written in [PHP](http://php.net/). It allows you to access all kind of file systems in the exact same manner, with the same simple commands. You can write your web application once and let users switch file system/platform; mount through URI.

## History and Philosophy behind FSnode
Last few years, I've been building (private) web applications which use common files to save content. Mainly XML-files, images, but also (more recently) the files my [iOS](http://www.apple.com/ios/)-devices save within my [Dropbox](http://www.dropbox.com/), or those files on my NAS. I ran into the frustration of rewriting the app every time again, when I wanted to switch from using file system. Soon, I realized it should have been way more standardized.
In fact, all file systems have the same (a-like) structure:
```text
#-@---# mount URI #---#
# +- file             #
# +--+ directory/     #
#    +-- file         #
```
Besides the same structure; you have comparable actions. You _scan_ the directories and files, you _read_ a file, you _write_ a file. You want to know if it is a *is_readable* and *is_writeable*, its _filesize_, the last modification time. You might even want to _rename_, _copy_, _delete_ a file or get or change its meta-information: _chmod_, _touch_... or do other directory/file system management.
With this perspective it would not matter if you are browsing through your local files, or a remote location, by which protocol and service. All you do is use FSnode as your file system layer. FSnode loads the required subclass to perform the instructed task; it maps instructions to the file system specific methods. As developer, all you have to know is how to formulate your instruction for FSnode.
When you want to do more complex and/or platform specific instructions, you should look at the documentation of the platform's subclass.

## When to use FSnode
- If you would like to use only one instruction-set to access files. Especially when;
- You would like to build your web-application on several file systems.
- You want to switch between FTP and the local file system, and back, and then with [Dropbox](http://www.dropbox.com/)*. Without bothering which is which.
- When you don't want to bother with rewriting your web-application, to add data-interaction to an (new) other platform*. 
- When you want to approach compressed files as a file system, or do the same with applications like [WordPress](http://www.wordpress.org/)*.

_*) when the FSnode extension is written / complete._

## How to use FSnode
Initiate a FSnode instance
```php
$fs = FSnode('file:/path/to/mount/');
```
next, decide which file you want to access (the equivalent of **scandir($directory)**)
```php
$ls = $fs->scan('./'); #returns (array) of /path/to/mount/./
```
read a file (the equivalent of **file_get_contents($filename)**)
```php
$data = $fs->read('README.md'); # in_array('README.md', $ls) === TRUE
```
write a file (the equivalent of **file_put_contents($filename, $data)**)
```php
$fs->write('README.md', $data);
```
