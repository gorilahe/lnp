<?php

/**
 * Cache缓存类
 * @author xanxng <xanxng@gmail.com>
 */
class lnp_Session{
	private static $cfg = array();
	private static $obj = null;
	
	/**
	 * 初始化
	 * 调用一次即可
	 */
	public static function init($config=array()) {
		if(is_object(self::$obj)) {
			return self::$obj;
		}
		
		if(is_string($config)) {
			$config = include $config;
		}
		self::$cfg = $config;
		//默认使用系统默认配置
		if(empty(self::$cfg['adapter'])) {
			return;
		}
		
		$classname = 'lnp_Session_'.self::$cfg['adapter'];
		self::$obj = new $classname(self::$cfg);
		session_set_save_handler(
			array(self::$obj, 'open'),
			array(self::$obj, 'close'),
			array(self::$obj, 'read'),
			array(self::$obj, 'write'),
			array(self::$obj, 'destroy'),
			array(self::$obj, 'gc')
		);
		return self::$obj;
	}
	

}

