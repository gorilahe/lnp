<?php

define('LNP_MTIME', microtime(true));
define('LNP_TIME', $_SERVER['REQUEST_TIME']);
defined('LNP_DEBUG') || define('LNP_DEBUG', 1);

@ini_set('register_globals', FALSE);
function_exists('set_magic_quotes_runtime') && set_magic_quotes_runtime(FALSE);
date_default_timezone_set('Asia/Shanghai');

defined('LNP_PATH') || define('LNP_PATH', dirname(__FILE__).'/');

defined('LNP_CTL_PATH') || define('LNP_CTL_PATH', LNP_PATH.'ctl/');
defined('LNP_MOD_PATH') || define('LNP_MOD_PATH', LNP_PATH.'mod/');
defined('LNP_DATA_PATH') || define('LNP_DATA_PATH', LNP_PATH.'data/');

//error_reporting(E_ALL^E_NOTICE);
set_error_handler(array('lnp', 'errorHandler'), E_ALL^E_NOTICE);
spl_autoload_register(array('lnp', 'autoLoad'));
register_shutdown_function(array('lnp', 'shutDown'));

class lnp {
	
	public static function run($config=array()) {
		if($config) {
			lnp_Config::init($config);
		}
		lnp_Request::init();
		//lnp_Session::init();
	}
	
	public static function errorHandler($errno, $errstr, $errfile, $errline, $errcontext){
		$str = date('Y-m-d H:i:s')."\t$errno\t$errstr\t$errfile\t$errline\n";
		
		is_dir(LNP_DATA_PATH.'log/php') || mkdir(LNP_DATA_PATH.'log/php', 0777, true);
		
		$prev = LNP_DATA_PATH.'log/php/error_'.date('Ymd');
		$path =  $prev.'.log';
		
		//分割日志，2M
		if(is_file($path) && filesize($path) > 2097152) {
			$path = $prev.'_'.time().'.log';
		}
		
		error_log($str, 3, $path);
		
		return !LNP_DEBUG;
	}
	
	public static function autoLoad($classname) {
		if(!preg_match('/^[a-z0-9_]+$/i', $classname)) {
			die('Class name invalid!');
		}
		$p = substr($classname, 0, 3);
		if($p == 'mod') {
			$file = LNP_MOD_PATH.str_replace(array('mod_','_'),array('', '/'), $classname).'.php';
		} else if($p == 'ctl') {
			$file = LNP_CTL_PATH.str_replace(array('ctl_','_'),array('', '/'), $classname).'.php';
		} else if($p == 'lnp') {
			$file = LNP_PATH.str_replace('_','/',$classname).'.class.php';
		} else {
			$file = LNP_PATH.'plugin/'.$classname.'/'.$classname.'.class.php';
		}
		
		if(is_file($file)) {
			require $file;
		} else {
			//echo $file;
			//var_dump(debug_backtrace());
			die('Load class('.$classname.') error!');
		}
	}
	
	public static function shutDown() {
		//echo 'down';
		
	}
	
}




//lnpLog::log('ddd');
//lnpSession::init(array('adapter'=>'memcache'));
//session_start();
//$_SESSION['DD'] = 'DDDDD1';
//$_SESSION['DDd'] = 'DDDDD2';
//unset($_SESSION['DD']);
//$_SESSION['DD'] = null;
//echo $_SESSION['DD'].'<br>';
//echo $_SESSION['DDd'];