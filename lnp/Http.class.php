<?php

/**
 * Http http远程类
 * @author xanxng <xanxng@gmail.com>
 */
class lnp_Http {
	private $url = '';
	private $getdata = array();
	private $postdata = array();
	private $cookiedata = array();
	private $results = '';
	private $httpversion = 2;
	private $httpmethod = 'GET';
	private $posttype = 'application/x-www-form-urlencoded';
	private $timeout = 15;
	private $useragent = 'lnpAgent';
	private $httpheader = array();
	private $referer = '';
	private $curloptions = array();
	private $content = '';
	private $retinfo = array();
	
	public function __construct($url=null) {
		if($url !== null) {
			$this->url = $url;
		}
	}
	
	public function setHttpVersion($v) {
		$v = $v === 11 ? 2 : ($v === 10 ? 1 : 0);
		$this->httpversion = $v;
	}
	
	public function setTimeOut($t) {
		$this->timeout = (int)$t;
	}
	
	public function setHttpMethod($method) {
		$method = strtoupper($method);
		$this->httpmethod = in_array($method, array('GET', 'POST', 'PUT')) ? $method : 'GET';
	}
	
	public function setReferer($r) {
		$this->referer = $r;
	}
	
	public function setUserAgent($u) {
		$this->useragent = $u;
	}
	
	public function setHeader($name, $val = '') {
		if(is_array($name)){
			$this->httpheader = $name;
		} else {
			$this->httpheader[$name] = $val;
		}
	}
	
	public function setOption($key, $val){
		$this->curloptions[$key] = $val;
	}
	
	public function setGetData($g) {
		$this->getdata = is_array($g) ? $g : array();
	}
	
	public function setPostData($p) {
		$this->postdata = $p;
	}
	
	public function setCookieData($c) {
		$this->cookiedata = is_array($c) ? $c : array();
	}
	
	private function parseCookie($cookie) {
		$ret = '';
		foreach($cookie as $k => $v) {
			$ret .= $k.'='.urlencode((string)$v).'; ';
		}
		return $ret ? substr($ret, 0, -2) : '';
	}
	
	private function parseHeader($header) {
		$ret = array();
		foreach($header as $k => $v) {
			$ret[] = "$k: $v";
		}
		return $ret;
	}
	
	private function parsePost($post) {
		$ret = '';
		foreach($post as $k => $v) {
			if(is_array($v)) {
				
			}
		}
	}
	
	public function request($url = '') {
		$url = $url ? $url : $this->url;
		$this->curloptions[CURLOPT_TIMEOUT] = $this->timeout;
		$this->curloptions[CURLOPT_HTTP_VERSION] = $this->httpversion;
		$this->curloptions[CURLOPT_REFERER] = $this->referer;//echo $this->referer;
		$this->curloptions[CURLOPT_USERAGENT] = $this->useragent;
		
		if($this->cookiedata) {
			$this->curloptions[CURLOPT_COOKIE] = $this->parseCookie($this->cookiedata);
		}
		
		if($this->postdata) {
			$this->curloptions[CURLOPT_POST] = TRUE;
			$this->curloptions[CURLOPT_POSTFIELDS] = $this->postdata;//$this->parsePost($this->postdata);
		}
		
		if($this->httpheader) {
			$this->curloptions[CURLOPT_HTTPHEADER] = $this->parseHeader($this->httpheader);
			//echo 'header';
		}
		
		$this->curloptions[CURLOPT_RETURNTRANSFER] = TRUE;
		
		$ch = curl_init($url);
		if($ch === FALSE) {
			exit('curl_init_error');
		}
		if(function_exists('curl_setopt_array')) {
			curl_setopt_array($ch, $this->curloptions);
		} else {
			foreach($this->curloptions as $option => $value) {
				curl_setopt($ch, $option, $value);
			}
		}
		
		$this->content = curl_exec($ch);
		$this->retinfo = curl_getinfo($ch);
		$this->errno = curl_errno($ch);
		$this->error = curl_error($ch);
		curl_close($ch);
		return $this->content;
	}
	
	public function get($url='', $data=array()){
		$this->httpmethod = 'GET';
		return $this->request($url);
	}
	
	public function post($url, $data=array()){
		$this->httpmethod = 'POST';
		$data && $this->setPostData($data);
		return $this->request($url);
	}
	
	public function getContent() {
		return $this->content;
	}
	
	public function getRetInfo() {
		return $this->retinfo;
	}
	
	public function errno() {
		return $this->errno;
	}
	
	public function error() {
		return $this->error;
	}
	
	public function requestSocket($url) {
		$url = $url ? $url : '/';
		$url .= self::$getdata ? (strpos($url, '?') ? '&'.http_build_query(self::$getdata) : http_build_query(self::$getdata)) : '';
		$urlinfo = parse_url($url);
		$host = isset($urlinfo['host']) ? $urlinfo['host'] : $url;
		$port = isset($urlinfo['port']) ? $urlinfo['port'] : 80;
		$sentdata = (self::$postdata ? 'POST ' : 'GET ').$url.' '.self::$httpversion."\r\n";
		$sentdata .= self::$useragent ? 'User-Agent: '.self::$useragent."\r\n" : '';
		$sentdata .= self::$referer ? 'Referer: '.self::$referer."\r\n" : '';
		
		if(self::$cookiedata) {
			$cookies = 'Cookie: ';
			foreach(self::$cookiedata as $key => $val) {
				$cookies .= $k.'='.urlencode($val).'; ';
			}
			unset($key, $val);
			$sentdata .= rtrim($cookies, '; ')."\r\n";
		}
		
		if(self::$httpheader) {
			foreach(self::$httpheader as $key => $val) {
				$sentdata .= $key.': '.$val."\r\n";
			}
		}
		
		if(self::$postdata) {
			$isput = false;
			$postfield = $putfield = array();
			foreach(self::$postdata as $k => $v) {
				if($k[0] == '@') {
					$isput = true;
					$k = substr($k, 1);
					$putfield[$k] = $v;
				} else {
					$postfield[$k] = $v;
				}
			}
			$postdata = '';
			if($isput && $putfield) {
				$boundary = 'lnphttp';
				$sentdata .= "Content-Type: multipart/form-data; boundary=$boundary\r\n";
				
				foreach($postfield as $k => $v) {
					$postdata .= "--$boundary\r\n";
					$postdata .= "Content-Disposition: form-data; name=\"$k\"\r\n\r\n";
					$postdata .= "$v\r\n";
				}
				
				foreach($putfield as $k => $v) {
					$postdata .= "--$boundary\r\n";
					$postdata .= "Content-Disposition: form-data; name=\"$k\"; filename=\"".basename($v)."\"\r\n\r\n";
					$postdata .= file_get_contents($v)."\r\n"; 
				}
				$postdata .= "--$boundary--";
			} else {
				$sentdata .= "Content-Type: application/x-www-form-urlencoded\r\n";
				$postdata = $postfield ? http_build_query($postfield) : '';
			}
			
			$sentdata .= "Content-Length: ".strlen($postdata)."\r\n";
			$sentdata .= $postdata;
		}
		
		$fp = fsockopen($host, $port, $errno, $errstr, self::$timeout);
		if(!$fp) {
			return false;
		}
		
		fwrite($fp, $sentdata, strlen($sentdata));
	}
}


/*
$curl = new lnpHttp('http://127.0.0.1/.php');
//$curl->setCookieData(array('loginpass'=>'866a6cafcf74ab3c2612a85626f1c706'));
$curl->setPostData(array('password'=>'','doing'=>'login', 'file'=>'@C:\Program Files\xaat.txt'));
$curl->request();
echo $curl->getErrno();
echo $curl->getError();
var_dump($curl->getContent());
var_dump($curl->getRetInfo());

*/