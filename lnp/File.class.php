<?php

/**
 * File 文件类
 * @author xanxng <xanxng@gmail.com>
 */
class lnp_File {

	/**
	 * 创建目录
	 * @param string $path 目录
	 * @param int $perms 目录权限
	 * @param bool $recursive 是否递归创建
	 */
	public static function mkdir($path, $perms = 0755, $recursive = true) {
		if(is_dir($path)) {
			return true;
		}
		return mkdir($path, $perms, $recursive);
	}
	
	/**
	 * 删除目录
	 * @param string $path 目录
	 * @param bool $deleteme 是否连同自己一起删除
	 */
	public static function rmdir($path, $deleteme = false) {
		if(!is_dir($path)) {
			return true;
		}
		$path = rtrim($path, '/\\');
		$files = scandir($path);
		if($files) {
			foreach($files as $file) {
				if($file == '.' || $file == '..') {
					continue;
				}
				$filepath = $path.'/'.$file;
				if(is_dir($filepath))	{
					self::rmdir($filepath, true);
				} else {
					unlink($filepath);
				}
			}
		}
		if($deleteme) {
			rmdir($path);
		}
		return true;
	}
	
	/**
	 * 删除文件
	 * @param string $file 文件
	 */
	public static function unlink($file) {
		if(!is_file($file) && !is_link($file)) {
			return true;
		}
		
		return unlink($file);
	}
	
	/**
	 * 别名，同$this->unlink
	 */
	public static function delete($file) {
		if(!is_file($file) && !is_link($file)) {
			return true;
		}
		
		return unlink($file);
	}
	
	/**
	 * 写内容入文件
	 * @param string $file 文件
	 * @param string $data 内容
	 * @param bool $append 是否添加到文件末尾
	 */
	public static function write($file, $data, $append=FALSE) {
		if($append){
			return file_put_contents($file, $data, FILE_APPEND);
		}
		return file_put_contents($file, $data);
	}
	
	/**
	 * 读取文件内容
	 * @param string $file 文件
	 */
	public static function read($file) {
		return is_file($file) ? file_get_contents($file) : false;
	}
	
	/**
	 * 获取子目录列表
	 * @param string $path 目录
	 */
	public static function childrenDir($path) {
		if(!is_dir($path)) {
			return false;
		}
		$path = rtrim($path, '/\\');
		$dirs = array();
		$files = scandir($path);
		if($files) {
			foreach($files as $file) {
				if($file == '.' || $file == '..') {
					continue;
				}
				$subpath = $path.'/'.$file;
				if(is_dir($subpath)) {
					$dirs[] = $file;
				}
			}
		}
		return $dirs;
	}
	
	/**
	 * 获取目录下非目录文件列表
	 * @param string $path 目录
	 */
	public static function childrenFile($path) {
		if(!is_dir($path)) {
			return false;
		}
		$path = rtrim($path, '/\\');
		$fs = array();
		$files = scandir($path);
		if($files) {
			foreach($files as $file) {
				if($file == '.' || $file == '..') {
					continue;
				}
				$subpath = $path.'/'.$file;
				if(!is_dir($subpath)) {
					$fs[] = $file;
				}
			}
		}
		return $fs;
	}
	
	/**
	 * 获取文件拓展名
	 * @param string $file 文件名
	 */
	public static function fileExt($file) {
		$file = rtrim($file, '.');
		if(false === $pos = strrpos($file, '.')) {
			return '';
		}
		return substr($file, $pos+1);
	}
	
	/**
	 * 获取文件名
	 * @param string $file 文件路径
	 */
	public static function fileName($file) {
		$ext = self::getFileExt($file);
		if($ext){
			return basename($file, '.'.$ext);
		}
		return basename($file);
	}
	
	/**
	 * 移动文件
	 * @param string $old 原文件
	 * @param string $new 新文件
	 */
	public static function move($old, $new) {
		return rename($old, $new);
	}
	
	/**
	 * 复制文件
	 * @param string $src 原文件
	 * @param string $dst 复制目标
	 */
	public static function copy($src, $dst) {
		return copy($src, $dst);
	}
	
	/**
	 * 列表目录下所有文件和目录, 不包括.和..
	 * @param string $path 目录
	 */
	public static function ls($path) {
		$path = rtrim($path, '/\\');
		if(!is_dir($path)) {
			return false;
		}
		$dirs = array();
		$files = scandir($path);
		if($files) {
			foreach($files as $file) {
				if($file == '.' || $file == '..') {
					continue;
				}
				$subpath = $path.'/'.$file;
				$dirs[] = $file;
			}
		}
		return $dirs;
	}
}

