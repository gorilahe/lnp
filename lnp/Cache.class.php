<?php

/**
 * Cache缓存类
 * @author xanxng <xanxng@gmail.com>
 */
class lnp_Cache {
	private static $inited = false;//是否已初始化
	private static $cfg = array(
		'adapter'=>'file'
	);//配置信息
	private static $obj = null;//生成对象缓存
	
	/**
	 * 初始化配置信息
	 * @param mixed $config 配置信息:如果为string类型则表示配置文件
	 */
	public static function init($config=array()) {
		if(empty($config)) {
			$config = lnp_Config::get('cache');
		} else if(is_string($config)) {
			$config = include $config;
		}
		
		self::$cfg = $config;
		self::$inited = true;
	}
	
	/**
	 * 对象实例化
	 * @param array $config 如果不为空，则可用于生成实例，否则只是单例
	 * 
	 */
	public static function factory($config=array()) {
		if($config) {
			$classname = 'lnp_Cache_'.$config['adapter'];
			return new $classname($config);
		}
		
		if(is_object(self::$obj)) {
			return self::$obj;
		}
		
		self::$inited || self::init();
		//var_dump(self::$cfg);
		if(empty(self::$cfg['adapter'])) {
			die('Cache adapter error');
		}
		
		$classname = 'lnp_Cache_'.self::$cfg['adapter'];
		self::$obj = new $classname(self::$cfg);
		return self::$obj;
	}
	
	/**
	 * 添加修改
	 * @param string $key 健
	 * @param mixed $val 值
	 */
	public static function set($key, $val, $ttl=0) {
		return self::factory()->set($key, $val, $ttl);
	}
	
	/**
	 * 获取值
	 * @param string $key 健
	 */
	public static function get($key) {
		return self::factory()->get($key);
	}
	
	/**
	 * 删除
	 * @param string $key 健
	 */
	public function delete($key) {
		return self::factory()->delete($key);
	}
	
	/**
	 * 清空
	 * 
	 */
	public function flush() {
		return self::factory()->flush();
	}
	
}

