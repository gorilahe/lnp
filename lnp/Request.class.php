<?php

/**
 * Request 请求类
 * @author xanxng <xanxng@gmail.com>
 */
class lnp_Request {
	private static $data = array();
	private static $ajaxvar = 'inajax';
	private static $cookiepre = '';
	
	public function __construct() {
		//$this->init();
	}
	
	public static function setCookiePre($str=''){
		self::$cookiepre = $str;
	}
	
	/**
	 * 初始化
	 * 对输入参数进行基本过滤
	 * 
	 * 对POST与GET数据进行合并，POST优先
	 */
	public static function init() {
		if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
			self::_stripslashes($_GET);
			self::_stripslashes($_POST);
			self::_stripslashes($_COOKIE);
			self::_stripslashes($_REQUEST);
			//self::_stripslashes($_ENV);
			//self::_stripslashes($_SERVER);
		}
		
		self::$data = $_POST + $_GET;
	}
	
	/**
	 * 
	 * @return 返回data
	 */
	public static function data($k=null, $default=null){
		if($k === null){
			return self::$data;
		}
		if(isset(self::$data[$k])) {
			return self::$data[$k];
		}
		return $default;
		
	}
	
	/**
	 * 获取GET数据
	 * 
	 */
	public static function get($k=null, $default=null) {
		if($k === null){
			return $_GET;
		}
		if(isset($_GET[$k])) {
			return $_GET[$k];
		}
		return $default;
	}
	
	public static function post($k=null, $default=null) {
		if($k === null){
			return $_POST;
		}
		if(isset($_POST[$k])) {
			return $_POST[$k];
		}
		return $default;
	}
	
	public static function cookie($k=null, $default=null) {
		if($k === null){
			return $_COOKIE;
		}
		
		$k = self::$cookiepre.$k;
		if(isset($_COOKIE[$k])) {
			return $_COOKIE[$k];
		}
		return $default;
	}
	
	public static function server($k, $default=null) {
		if(isset($_SERVER[$k])) {
			return $_SERVER[$k];
		}
		return $default;
	}
	
	public static function session($k, $default=null) {
		if(isset($_SESSION[$k])) {
			return $_SESSION[$k];
		}
		return $default;
	}
	
	public static function env($k, $default=null) {
		if(isset($_ENV[$k])) {
			return $_ENV[$k];
		}
		return $default;
	}
	
	public static function files($k) {
		if(isset($_FILES[$k])) {
			return $_FILES[$k];
		}
		return null;
	}
	
	public static function getString($k, $default='') {
		if(isset(self::$data[$k])) {
			return trim((string)self::$data[$k]);
		}
		return $default;
	}
	
	public static function getNoHtml($k, $default=''){
		if(isset(self::$data[$k])) {
			return htmlspecialchars(trim((string)self::$data[$k]));
		}
		return htmlspecialchars($default);
	}
	
	public static function getInt($k, $default=0) {
		if(isset(self::$data[$k])) {
			return (int)self::$data[$k];
		}
		return $default;
	}
	
	public static function getFloat($k, $default=0.0) {
		if(isset(self::$data[$k])) {
			return (float)self::$data[$k];
		}
		return $default;
	}
	
	public static function getArray($k, $default=array()) {
		if(isset(self::$data[$k])) {
			return (array)self::$data[$k];
		}
		return $default;
	}
	
	public static function getBool($k, $default=FALSE) {
		if(isset(self::$data[$k])) {
			return (bool)self::$data[$k];
		}
		return $default;
	}
	
	public static function getNumeric($k, $default=0) {
		if(isset(self::$data[$k]) && is_numeric(self::$data[$k])) {
			return (float)self::$data[$k];
		}
		return $default;
	}
	
	public static function getChecked($k, $default=null) {
		if(isset(self::$data[$k])){
			return self::$data[$k];
		}
		return $default;
	}
	
	public static function isPost() {
		return self::server('REQUEST_METHOD') === 'POST';
	}
	
	public static function isGet() {
		return self::server('REQUEST_METHOD') === 'GET';
	}
	
	public static function setAjaxVar($var) {
		self::$ajaxvar = $var;
	}
	
	public static function isAjaxRequest() {
		return self::getVal(self::$ajaxvar) || self::server('HTTP_X_REQUESTED_WITH') === 'xmlhttprequest';
	}
	
	/**
	 * 获取当前页相对地址路径，不带域名与端口
	 * 
	 */
	public static function getRequestUrl() {
		if($base = self::server('REQUEST_URI')) {
		} else if($base = self::server('QUERY_STRING')) {
			$base = '?'.$base;
		}
		return $base;
	}
	
	/**
	 * 获取当前页地址URL
	 * 
	 */
	public static function getCurrentUrl() {
		$scheme = self::server('HTTPS') === 'on' ? 'https://' : 'http://';
		$port = self::server('SERVER_PORT');
		if(!$port || ($port == '80' && $scheme === 'http://') || ($port == 443 && $scheme === 'https://')) {
			$port = '';
		} else {
			$port = ':'.$port;
		}
		
		return $scheme.self::server('HTTP_HOST').$port.self::getRequestUrl();
	}
	
	private static function _stripslashes(&$var) {
		if(is_array($var)) {
			foreach($var as $k=>$v) {
				self::_stripslashes($var[$k]);
			}
		} else {
			$var = stripslashes($var);
		}
		return $var;
	}
	
	/**
	 * 获取用户IP
	 * 
	 */
	public static function clientIp(){
		static $ip = '';
		if($ip){
			return $ip;
		}
		
		$ip = self::server('REMOTE_ADDR');
		
		if($hci = self::server('HTTP_CLIENT_IP')) {
			$ip = $hci;
		} else if($xip = self::server('HTTP_X_FORWARDED_FOR')) {
			if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
				$ip = (string)$xip;
			}
		
		}
		$ip = htmlspecialchars($ip);
		return $ip;
	}
	
	
}

