<?php

/**
 * file cache文件缓存类
 * @author xanxng <xanxng@gmail.com>
 */
class lnp_Cache_file {
	private $cfg = array(
		'path'=>'cache',/* 存储路径 */
		'pre'=>'lnp_',/* key前缀 */
		'ext'=>'.php',/* 文件后缀 */
		'depth'=>2,/* 目录层级, 防止单个目录文件过多 */
		'ttl'=>0,/* 缓存时间 */
	);
	
	/**
	 * 构架函数
	 * @param array $config 配置信息
	 */
	public function __construct($config=array()){
		if($config) {
			$this->cfg = $config + $this->cfg;
		}
		
		$this->cfg['path'] = rtrim($this->cfg['path'], '/\\');//去除目录后的斜线
	}
	
	/**
	 * 添加修改
	 * @param string $key 健
	 * @param mixed $val 值
	 */
	public function set($key, $val, $ttl=0){
		$filepath = $this->rawKey($key);
		$dir = dirname($filepath);
		is_dir($dir) || mkdir($dir, 0777, true);
		if(!$ttl) {
			$ttl = $this->cfg['ttl'];
		}
		$cache = array('data'=>$val,'ttl'=>$ttl,'cachetime'=>time());
		
		return file_put_contents($filepath, "<?php\n return ".var_export($cache, true).";\n");
	}
	
	/**
	 * 获取值
	 * @param string $key 健
	 */
	public function get($key){
		$path = $this->rawKey($key);
		if(is_file($path)){
			$data = include $path;
			//判断存储有效期
			if($data['ttl'] > 0 && $data['cachetime']+$data['ttl'] < time()) {
				$this->delete($key);
				return false;
			}
			
			return $data['data'];
		}
		return false;
	}
	
	/**
	 * 删除
	 * @param string $key 健
	 */
	public function delete($key) {
		$path = $this->rawKey($key);
		if(is_file($path)){
			return unlink($path);
		}
		return false;
	}
	
	/**
	 * 清空
	 * 
	 */
	public function flush() {
		//建议通过系统命令来删除
		return $this->clearDir($this->cfg['path']);
	}
	
	/**
	 * 清除目录内所有文件
	 * @param string $path 目录路径
	 * @param bool   $deleteme 删除自己?(是否连同本目录也删除，用于递归删除目录内的目录)
	 */
	private function clearDir($path, $deleteme = false) {
		if(!is_dir($path)) {
			return true;
		}
		$files = scandir($path);
		if($files) {
			foreach($files as $file) {
				if($file == '.' || $file == '..') {
					continue;
				}
				$filepath = $path.'/'.$file;
				if(is_dir($filepath))	{
					$this->clearDir($filepath, true);
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
	 * 获取key所在路径
	 * @param string $key key
	 */
	private function rawKey($key) {
		$key = md5($this->cfg['pre'].$key);
		$path = $this->cfg['path'];
		for($i = 0; $i < $this->cfg['depth']; $i++) {
			$path .= '/'.substr($key, $i*1, 1);
		}
		$path .= '/'.$key.$this->cfg['ext'];
		return $path;
	}
	
}

