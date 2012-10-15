<?php

/**
 * Log log记录类
 * @author xanxng <xanxng@gmail.com>
 */
class lnp_Log{
	
	private static $cfg = array('savepath'=>'log');
	
	/**
	 *初始配置
	 * @param array $config 配置
	 */
	public static function init($config=array()) {
		if($config) {
			self::$cfg = $config + self::$cfg;
		}
	}
	
	/**
	 * 记录日志
	 * @param string $msg 错误描述
	 * @param string $errtype 错误方式/记录目录
	 */
	public static function log($msg=null, $errtype = 'app') {
		if(empty($msg)) {
			return ;
		}
		if(empty($errtype)){
			$errtype = 'app';
		}
		//$trace = debug_backtrace();
		$dir = self::$cfg['savepath'].'/'.$errtype;
		is_dir($dir) || mkdir($dir, 0777, true);
		
		$prev = $dir.'/'.date('Ymd');
		
		$path =  $prev.'.log';
		if(is_file($path) && filesize($path) > 2097152) {
			$path = $prev.'_'.time().'.log';
		}
		
		error_log($msg."\n", 3, $path);
	}
}

//lnp_Log::log('errr');