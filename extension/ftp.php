<?php
/****************** DO NOT REMOVE OR ALTER THIS HEADER ******************************
*                                                                                   *
* Product: FSnode                                                                   *
*    FSnode is a node-based Uniform FileSystem handler, written in PHP. It allows   *
*    you to access all kind of file systems in the exact same manner, with the same *
*    simple commands. You can write your web application once and let users switch  *
*    file system/platform; mount through URI.                                       *
*                                                                                   *
* Latest version to download:                                                       *
*    https://github.com/sentfanwyaerda/FSnode                                       *
*                                                                                   *
* Documentation:                                                                    *
*    http://sent.wyaerda.org/FSnode/                                                *
*    https://github.com/sentfanwyaerda/FSnode/blob/master/manual/FSnode_ftp.md      *
*                                                                                   *
* Authors:                                                                          *
*    Sent fan Wy&aelig;rda (fsnode@sent.wyaerda.org) [creator, main]                *
*                                                                                   *
* License: cc-by-nd                                                                 *
*    Creative Commons, Attribution-No Derivative Works 3.0 Unported                 *
*    http://creativecommons.org/licenses/by-nd/3.0/                                 *
*    http://creativecommons.org/licenses/by-nd/3.0/legalcode                        *
*                                                                                   *
****************** CHANGES IN THE CODE ARE AT OWN RISK *****************************/
require_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'FSnode.php');

define('FSnode_FTP_URI_PREFIX', 'ftp:// ftps:// sftp://');
define('FSnode_FTP_SCHEME', 'ftp');

if(!defined('FSnode_TEMP_DIRECTORY')){define('FSnode_TEMP_DIRECTORY', dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR);}

class FSnode_ftp extends FSnode {
	public function Version($a=FALSE){ return (/*!(parent::version(TRUE) == self::version(FALSE)) && */ !($a==FALSE) ? parent::version(TRUE).'-' : NULL).'beta'; }
	public function Product($full=FALSE){ return "FSnode:ftp".(!($full===FALSE) ? " ".self::version(TRUE).(class_exists('Xnode') && method_exists('Xnode', 'Product') ? '/'.Xnode::Product(TRUE) : NULL) : NULL); }
	
	private /*resource*/ $ftp_stream;
	private /*bool*/ $authenticated = FALSE;
	#private $host, $user, $port;
	
	private function _initialize($a=NULL, $b=NULL, $c=NULL, $d=NULL){}
	public /*string*/ function __toString(){ return (string) NULL; }

	/* ignores the following FTP Functions ( http://php.net/manual/en/ref.ftp.php ) functions: ftp_alloc*, ftp_cdup, ftp_chdir, ftp_exec, ftp_fget*, ftp_fput*, ftp_get_option, ftp_get*, ftp_login*, ftp_nb_continue, ftp_nb_fget, ftp_nb_fput, ftp_nb_get, ftp_nb_put, ftp_nlist, ftp_pasv, ftp_put*, ftp_pwd, ftp_quit*, ftp_raw, ftp_rawlist, ftp_set_option, ftp_site, ftp_ssl_connect*, ftp_systype */
	
	
	private function _allowed_methods(){
		#return array('chmod','chgrp','chown','copy','delete','disk_free_space','disk_total_space','file_exists','file_get_contents','file_put_contents','file','fileatime','filectime','filegroup','fileinode','filemtime','fileowner','fileperms','filesize','filetype','is_dir','is_executable','is_file','is_readable','is_writable','is_writeable','mkdir','rename','rndir','stat','touch','unlink');
		return array('chmod','copy','file_get_contents','file_put_contents','file','filemtime','filesize','is_dir','is_file','mkdir','rename','rmdir','unlink','scandir','close','connect','read','write','delete');
	}
	
	/* the following FTP functions are renamed: ftp_chmod > chmod, ftp_close > close, ftp_connect > connect, ftp_delete > delete, ftp_mdtm > filemtime, ftp_mkdir > mkdir, ftp_rename > rename, ftp_rmdir > rmdir, ftp_size > filesize, ftp_nlist > scandir */
	
	#Filesystem Handlers
	public /*int*/ function chmod($filename, $mode){
		if(!self::is_connected()){ return /*error: not connected*/ FALSE; } else { return ftp_chmod($this->ftp_stream, (int) $mode, (string) self::realpath($filename)); }
	}
	public /*experimental*/ function chgrp($filename, $group=NULL){
		/*fix*/ if($group === NULL){ $group = $this->user; }
		return self::execute('chgrp '.$group.' '.self::realpath($filename));
	}
	public /*experimental*/ function chown($filename, $user=NULL){
		/*fix*/ if($user === NULL){ $user = $this->user; }
		return self::execute('chown '.$user.' '.self::realpath($filename));
	}
	
	public /*bool*/ function copy($source, $dest){ return self::write( (string) self::realpath($dest), self::read( (string) self::realpath($source) ) ); }
	
	public /*dummy*/ function disk_free_space(){ }
	public /*dummy*/ function disk_total_space(){ }
	
	public /*dummy*/ function file_exists($filename){
		$buff = self::rawlist(dirname($filename).DIRECTORY_SEPARATOR, FALSE, basename($filename));
		return (isset($buff['filename']) && isset($buff['filesize']) && strlen($buff['filename']) > 0 && $buff['filesize'] > -1 );
	}
	public /*string*/ function file_get_contents($filename){ return self::read( (string) self::realpath($filename) ); } #alias
	public /**/ function file_put_contents($filename, $data){ return self::write( (string) self::realpath($filename), $data); } #alias
	public /*array*/ function file($filename){ return explode("\n", self::file_get_contents( (string) $filename )); }
		
	public /*experimental*/ function fileatime($filename){ return self::filemtime($filename); }
	public /*experimental*/ function filectime($filename){ return self::filemtime($filename); }
	public /*experimental*/ function filegroup($filename){ $buff = self::rawlist(dirname($filename).DIRECTORY_SEPARATOR, FALSE, basename($filename)); return $buff['group'];  }
	public /*dummy*/ function fileinode($filename){ }
	public /*int*/ function filemtime($filename){
		if(!self::is_connected()){ return /*error: not connected*/ FALSE; }
		else {
			if(self::is_dir($filename)){
				$buff = self::rawlist(dirname($filename).DIRECTORY_SEPARATOR, FALSE, basename($filename));
				#/*fix*/ if(substr($filename, -1, 1) !== DIRECTORY_SEPARATOR){ $filename .= DIRECTORY_SEPARATOR.'.'; }
				#$buff = self::rawlist(dirname(dirname($filename)).DIRECTORY_SEPARATOR, FALSE, basename(dirname($filename)));
				return $buff['filemtime'];
			}
			else{
				return ftp_mdtm($this->ftp_stream, (string) self::realpath($filename) );
			}
		}
	}
	public /*experimental*/ function fileowner($filename){ $buff = self::rawlist(dirname($filename).DIRECTORY_SEPARATOR, FALSE, basename($filename)); return $buff['owner']; }
	public /*experimental*/ function filerights($filename){
		$prefix = '-';
		if(self::is_dir($filename)){ $prefix = 'd'; }
		elseif(self::is_link($filename)){ $prefix = 'l'; }
		$buff = self::rawlist(dirname($filename).DIRECTORY_SEPARATOR, FALSE, basename($filename));
		return $prefix.$buff['perms'];
	}
	public /*experimental*/ function fileperms($filename){ return '0x'.decoct(self::rights2fileperms(self::filerights($filename))); }
	public /*int*/ function filesize($filename){
		if(!self::is_connected()){
			return /*error: not connected*/ FALSE;
		}
		else {
			return ftp_size($this->ftp_stream, (string) self::realpath($filename) );
		}
	}
	public /*dummy*/ function filetype($filename){ }
	
	public /*bool*/ function is_dir($filename){
		/*fix*/ if(substr($filename, -1, 1) !== DIRECTORY_SEPARATOR){ $filename .= DIRECTORY_SEPARATOR; }
		$buff = self::rawlist(dirname($filename).DIRECTORY_SEPARATOR, FALSE, basename($filename));
		return ($buff['flag'] == 'd' ? TRUE : FALSE); 
	}
	public /*experimental*/ function is_executable($filename){ return self::_perms_analyse($filename, 'x'); }
	public /*experimental*/ function is_file($filename){ $buff = self::rawlist(dirname($filename).DIRECTORY_SEPARATOR, FALSE, basename($filename)); return ($buff['flag'] == '-' ? TRUE : FALSE); /*return is_file(self::realpath_URI($filename));*/ }
	public /*experimental*/ function is_link($filename){ $buff = self::rawlist(dirname($filename).DIRECTORY_SEPARATOR, FALSE, basename($filename)); return ($buff['flag'] == 'l' ? TRUE : FALSE); }
	public /*experimental*/ function is_readable($filename){ return self::_perms_analyse($filename, 'r'); }
	#public /*dummy*/ function is_uploaded_file($filename){ }
	public /*experimental*/ function is_writable($filename){ return self::_perms_analyse($filename, 'w'); }
	public /*experimental*/ function is_writeable($filename){ return self::is_writable( $filename ); } #alias
	
	private /*bool*/ function _perms_analyse($filename, $right="r" /*r|w|x*/, $level="owner" /*owner|group|[world|anonymous]*/){
		#if(!in_array(strtolower($right), array('r','w','x'))){ return /*error*/ NULL; }
		$buff = self::rawlist(dirname($filename).DIRECTORY_SEPARATOR, FALSE, basename($filename));
		$perms = $buff['perms'];
		if(preg_match("#^[0]?[0-7]{3}$#", $perms)){
			/*fix for 0777 style perms*/ $perms = self::fileperms2rights(decoct($perms));
			}
		switch(strtolower($level)){
			case 'owner': 
				if(self::parse_url($this->URI, 'user') != $buff['owner']){
					$perms = str_repeat('-', 3).substr($perms, -6);
				}
			case 'group': /*should also test if 'user' is part of 'group' */
				if(self::parse_url($this->URI, 'user') != $buff['owner']){
					$perms = str_repeat('-', 3).substr($perms, -6, 3).str_repeat('-', 3);
				}
				break;
			case 'anonymous': case 'world': default:
				$perms = str_repeat('-', 6).substr($perms, -3);
		}
		//*debug*/ print '<!-- '.$right.'-check '.$buff['perms'].' '.$filename.' ('.$level.': '.$perms.') -->'."\n";
		return (bool) preg_match("#".$right."#i", $perms);
	}
	
	public /*bool*/ function is_connected($and_authenticated=TRUE){
		return ($and_authenticated===TRUE ? (is_resource($this->ftp_stream) && $this->authenticated === TRUE ? TRUE : FALSE) : (is_resource($this->ftp_stream) ? TRUE : FALSE));
	}
	public /*string*/ function realpath($filename=NULL){
		return preg_replace('#[/]+#', '/', $this->parse_url($this->URI, 'path').($filename===NULL ? '/' : (substr($filename, 0, 1) == '/' ? $filename : '/'.$filename)));
	}
	
	public /*string*/ function mkdir($directory){
		if(!self::is_connected()){
			return /*error: not connected*/ FALSE;
		}
		else {
			return ftp_mkdir($this->ftp_stream, (string) self::realpath($directory) );
		}
	}
	public /*bool*/ function rename($oldname, $newname){
		if(!self::is_connected()){
			return /*error: not connected*/ FALSE;
		}
		else {
			return ftp_rename($this->ftp_stream, (string) self::realpath($oldname), (string) self::realpath($newname) );
		}
	}
	public /*bool*/ function rmdir($directory){
		if(!self::is_connected()){
			return /*error: not connected*/ FALSE;
		}
		else {
			return ftp_rmdir($this->ftp_stream, (string) $directory );
		}
	}
	public /*experimental*/ function stat($filename){
		$buff = self::rawlist(dirname($filename).DIRECTORY_SEPARATOR, FALSE, basename($filename));
		$set = array(
			'dev' => NULL,
			'ino' => 0,
			'mode' => $buff['chmode'],
			'nlink' => NULL,
			'uid' => 0,
			'gid' => 0,
			'rdev' => NULL,
			'size' => $buff['filesize'],
			'atime' => strtotime($buff['filemtime']),
			'mtime' => strtotime($buff['filemtime']),
			'ctime' => strtotime($buff['filemtime']),
			'blksize' => -1,
			'blocks' => -1
			);
		return $set;
	}
	public /*experimental*/ function touch($filename, $timestamp=FALSE){
		/*fix*/ if($timestamp === NULL){ $timestamp = time(); }
		return self::execute('touch'.($timestamp !== FALSE ? ' -t '.date('YmdHi', strtotime($timestamp)) : NULL).' '.self::realpath($filename));
		/*not implemented yet*/
	}
	public /*dummy*/ function unlink($path){ return self::delete($path); } #dummy
	
	#Directory Handlers
	public /*array*/ function scandir($directory=NULL, $sorting_order=SCANDIR_SORT_ASCENDING){
		return self::_rawlist_scandir($directory, $sorting_order);
	}
	public /*array*/ function _nlist_scandir($directory=NULL, $sorting_order=SCANDIR_SORT_ASCENDING){
		//*debug*/ print '<!-- FSnode_ftp::scandir( '.$directory.' ) -->'."\n";
		if(!self::is_connected()){ return /*error: not connected*/ array(); }
		else { 
			if($directory === NULL){ $directory = '.'; }
			/*realpath fix*/ $directory = self::realpath($directory);
			$list = ftp_nlist($this->ftp_stream, (string) $directory);
			#/*debug*/ print '<!-- directory: '.$directory.' = '; var_dump($list); print ' x '; var_dump(ftp_rawlist($this->ftp_stream, (string) $directory)); print ' -->'."\n";
			/*fix*/ if(strlen($directory) > 2){ $list = array_merge(array('..'), $list);}
			if(!( $sorting_order === SCANDIR_SORT_ASCENDING )){ $list = array_reverse($list); }
			return $list;
		}
	}
	public /*array*/ function _rawlist_scandir($directory=NULL, $sorting_order=SCANDIR_SORT_ASCENDING){
		/*fix*/ if(substr($directory, -1, 1) !== DIRECTORY_SEPARATOR){ $directory .= DIRECTORY_SEPARATOR; }
		if(self::is_connected() && self::is_dir($directory)){
			$list = array();
			$buff = self::rawlist($directory, FALSE, TRUE); //dirname($directory).DIRECTORY_SEPARATOR
			foreach($buff as $i=>$line){ $list[] = $line['filename']; }
			/*fix*/ if(strlen($directory) > 2){ $list = array_merge(array('..'), $list);}
			if(!( $sorting_order === SCANDIR_SORT_ASCENDING )){ $list = array_reverse($list); }
			return $list;
		}
		else{ return FALSE; }
	}
	public /*array*/ function scan($directory=NULL, $sorting_order=SCANDIR_SORT_ASCENDING){
		return self::scandir($directory, $sorting_order);
	}
	
	#Server Handlers
	public /*bool*/ function close(){ if(!self::is_connected()){ return /*error: not connected*/ FALSE; } else { $this->authenticated = FALSE; return ftp_close($this->ftp_stream); } }
	public /*resource*/ function connect($host=TRUE, $user=NULL, $pass=NULL, $port=21, $timeout=90, $secure=NULL){
		if($host === TRUE){ $host = $this->URI; }
		$set = self::parse_url($host); foreach($set as $k=>$v){ if(in_array($k, array('host','user','pass','port'))){ $$k = $v; } }
		if($secure === NULL && isset($set['scheme'])){ $secure = (!($set['scheme'] == 'ftp') ? TRUE : FALSE); }
		
		#if($user === NULL || !is_int($user)){ $port = $user; unset($user); if(!is_int($pass)){ $timeout = $pass; unset($pass); } elseif($pass === NULL){ $timeout = 90;} }
		/*set parameters*/ $this->host = $host; $this->user = $user; $this->port = $port;
		if($secure === TRUE && function_exists('ftp_ssl_connect') ){ $this->ftp_stream = ftp_ssl_connect( (string) $host, (int) $port /*, (int) $timeout*/ ); }
		else{ $this->ftp_stream = ftp_connect( (string) $host, (int) $port /*, (int) $timeout*/ ); }
		if($this->ftp_stream === FALSE){ return NULL; } /*else: login >> */
		if(isset($user) && isset($pass)){
			$this->authenticated = @ftp_login($this->ftp_stream, $user, $pass);
		}
		else{
			$this->authenticated = @ftp_login($this->ftp_stream, 'anonymous', NULL);
		}
		return $this->authenticated;
	}
	
	#Basic
	public /*string*/ function read($filename){
		$contents = FALSE;
		/*realpath fix*/ $filename = self::realpath($filename);
		if(!self::is_connected()){ return /*error: not connected*/ FALSE; }
		else { 
			$temp_file = tempnam(FSnode_TEMP_DIRECTORY, 'fsnode_');
			if($handle = fopen($temp_file, 'w')){
				if(ftp_fget($this->ftp_stream, $handle, (string) $filename, FTP_BINARY )){
					//$contents = fread($handle, filesize($temp_file));
					$contents = file_get_contents($temp_file);
				}
				else{ $contents = FALSE; }
				fclose($handle);
				unlink($temp_file);
			}
			return $contents;
		}
	}
	public /*bool*/ function write($filename, $data){
		/*realpath fix*/ $filename = self::realpath($filename);
		if(!self::is_connected()){ return /*error: not connected*/ FALSE; }
		else { 
			$bool = TRUE;
			$temp_file = tempnam(FSnode_TEMP_DIRECTORY, 'fsnode_');
			file_put_contents($temp_file, $data);
			if(ftp_alloc($this->ftp_stream, filesize($temp_file), $result)){
				if(!ftp_put($this->ftp_stream, (string) $filename, $temp_file, FTP_BINARY )){ $bool = FALSE; }
			} else { $bool = FALSE; /*$bool = $result;*/ }
			unlink($temp_file);
			return $bool;
		}
	}
	public /*bool*/ function delete($path){
		if(!self::is_connected()){
			return /*error: not connected*/ FALSE;
		} else {
			/*realpath fix*/ $path = self::realpath($path);
			return ftp_delete($this->ftp_stream, (string) $path);
		}
	}
	public /*mixed*/ function execute($line){
		if(!self::is_connected()){
			return /*error: not connected*/ FALSE;
		}
		else{
			@ftp_site($this->ftp_stream, $line);
		}
	}
	public /*array|assigned*/ function rawlist($directory, $recursive=FALSE, $assigned=TRUE, $refresh=FALSE){
		/*fix*/ if(substr($directory, -2, 2) == DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR){ $directory = substr($directory, 0, -1); }
		/*fix*/ if(substr($directory, -1, 1) != DIRECTORY_SEPARATOR){ $directory .= DIRECTORY_SEPARATOR; }
		/*fix*/ if($assigned === ""){ $assigned = '/'; }
				
		if(!self::is_connected()){ return /*error: not connected*/ FALSE; }
		else { 
			if($refresh !== FALSE && isset($this->cache[md5($directory)])){
				$buff = $this->cache[md5($directory)];
			}
			else{
				$buff = $this->cache[md5($directory)] = ftp_rawlist($this->ftp_stream, (string) self::realpath($directory), $recursive);
				/*debug*/ $this->cache[md5($directory)]['directory'] = $directory;
				if($directory == '/'){
					$this->cache[md5($directory)][-1] = $buff[-1] = "drwxrwxrwx\t0\t".$this->user."\t".$this->user."\t0\tJan  1  1970\t/";
				}
			}
			/*fix*/ if(!is_array($buff)){ $buff = array(); print '<!--empty buffer on FSnode_ftp::rawlist('.$directory.') '.md5($directory).' -->'."\n"; return FALSE; }
			if($assigned !== FALSE){
				#if( ftp_systype($this->ftp_stream) == 'UNIX')
				$set = array();
				foreach($buff as $i=>$line){
					if(preg_match("#^([dl-])([rwx-]{9})\s+[0-9]+\s+([^ ]+)\s+([^ ]+)\s+([0-9]+[kMGb]{0,2})\s+([a-z0-9: ]{12})\s+(.*)$#i", $line, $buffer)){
						if(preg_match("# -> #i", $buffer[7])){
							$lbuf = explode(' -> ', $buffer[7]);
							$fn = $lbuf[0];
							$set[$fn]['link-target'] = $lbuf[1];
						} else{
							$fn = $buffer[7];
						}
						$set[$fn] = array_merge(array(
							'flag' => $buffer[1],
							'perms' => $buffer[2],
							'chmod' => decoct(self::rights2fileperms($buffer[1].$buffer[2])),
							//'?' => $buffer[@2],
							'owner' => $buffer[3],
							'group' => $buffer[4],
							'filesize' => $buffer[5],
							'filemtime' => strtotime($buffer[6]),
							'filename' => $fn
						), (isset($set[$fn]) && is_array($set[$fn]) ? $set[$fn] : array()));
					}
				}
				//*debug*/ if($assigned == '/'){ $this->log(NULL, __METHOD__, /*__FILE__.':'.*/__LINE__, $set, array($directory, $assigned)); }
				if($assigned === TRUE){
					return $set;
				} elseif( !isset($set[$assigned]) ){ return array(
							'flag' => FALSE,
							'perms' => '---------',
							'owner' => NULL,
							'group' => NULL,
							'filesize' => -1,
							'filemtime' => NULL,
							'filename' => $assigned);
				} else { return $set[$assigned]; }
			}
			else{ return $buff; }
		}
	}
}
?>