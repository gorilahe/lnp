<?php

/**
 * Config 配置类,主要用于存储全局性的配置信息
 * @author xanxng <xanxng@gmail.com>
 */
class lnp_Config {
	private static $data = array();//储存数据
	
	/**
	 * 初始化配置信息
	 * @param mixed $config 配置信息:如果为string类型则表示配置文件
	 */
	public static function init($config = array()) {
		if(empty($config)) {
			return;
		}
		if(is_string($config)) {
			is_file($config) && (self::$data = include $config);
		} else {
			self::$data = $config;
		}
	}
	
	/**
	 * 添加,修改
	 * @param mixed $key 配置健:如果为array类型则为批量添加
	 * @param mixed $val 配置值:如果为$key为array类型则无效
	 */
	public static function set($key, $val=null) {
		if(is_array($key)) {
			foreach($key as $k => $v) {
				self::$data[$k] = $v;
			}
		} else {
			self::$data[$key] = $val;
		}
	}
	
	/**
	 * 获取
	 * @param string $key key
	 * @param mixed $default key值不存在是返回的默认值
	 */
	public static function get($key=null, $default=null) {
		if($key === null) {
			return self::$data;
		}
		
		if(isset(self::$data[$key])) {
			return self::$data[$key];
		}
		return $default;
	}
	
	/**
	 * 删除
	 * @param string $key key
	 */
	public static function delete($key) {
		if(isset(self::$data[$key])) {
			self::$data[$key] = null;
			unset(self::$data[$key]);
			return true;
		}
		return false;
	}
	
}
