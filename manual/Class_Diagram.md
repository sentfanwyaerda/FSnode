# FSnode Class Diagram

### Variables
- $fsnode->URI

### Methods
- (bool) FSnode::connect()
- (bool) FSnode::close()
- $data = FSnode::read($filename)
- (int) FSnode::write($filename, $data)
- (bool) FSnode::delete($filename)
- (array) FSnode::scan($directory, $sorting_order=SCANDIR_SORT_ASCENDING)

- (mixed) FSnode::execute($line=NULL)
- (bool) FSnode::refresh($tag=NULL)

### Status Methods (borrowed from LOCAL)
- (int) FSnode::fileatime($filename)
- (int) FSnode::filectime($filename)
- (int) FSnode::filegroup($filename)
- (int) FSnode::fileinode($filename)
- (int) FSnode::filemtime($filename)
- (int) FSnode::fileowner($filename)
- (int) FSnode::fileperms($filename)
- (int) FSnode::filesize($filename)
- (int) FSnode::filetype($filename)

- (bool) FSnode::file_exists($filename)
- (bool) FSnode::is_dir($filename)
- (bool) FSnode::is_executable($filename)
- (bool) FSnode::is_file($filename)
- (bool) FSnode::is_readable($filename)
- (bool) FSnode::is_writable($filename)
- (bool) FSnode::is_writeable($filename)

- (float) disk_free_space($directory=NULL)
- (float) disk_total_space($directory=NULL)

### Status Methods (other)
- (string) FSnode::mime_content_type($filename)

### LOCAL Methods
- (bool) chmod($filename, $mode)
- (bool) chgrp($filename, $group)
- (bool) chown($filename, $user)

- (bool) copy($source, $destination)
- file_get_contents($filename)
- file_put_contents($filename, $data)

- mkdir($pathname, $mode=0777, $recursive=FALSE)
- rename($oldname, $newname)
- rmdir($directory)
- unlink()

- stat($filename)
- touch($filename)

- scandir($directory)
- realpath($filename)

### Service Methods
- FSnode::FSnode()
- private FSnode::_hook()
- FSnode::add_hook($hook, $auto_load=FALSE)
- FSnode::load_extension($extension);
- FSnode::AutoUpdate()

### Meta Methods
- private FSnode::_initialize()
- $fsnode = FSnode::URI_load($URI)
- private FSnode::_validate_URI($URI)
- private FSnode::_allowed_methods()
- FSnode::method_exists($method)
- (string) FSnode::__toString()
- private FSnode::_filename_attach_prefix($filename)

### Header Methods
- FSnode::Version()
- FSnode::Product_url()
- FSnode::Product()
- FSnode::License()
- FSnode::License_url()
- FSnode::Product_base()
- FSnode::Product_file()

### [Library](https://github.com/sentfanwyaerda/FSnode/blob/master/manual/Library.md) Methods
- $arguments = FSnode::parse_url($url, $component=-1)
- (string) FSnode::rebuild_url( $arguments );