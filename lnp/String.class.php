<?php

/**
 * String 类
 * @author xanxng <xanxng@gmail.com>
 */
class lnp_String{
	
	public static function safeHtml($str){
		return htmlspecialchars($str);
	}
	
	public static function eacapeHtml($str){
		return htmlspecialchars($str);
	}
	
	public static function addslashes($var){
		if(is_array($var)) {
			foreach($var as $k=>$v) {
				$var[$k] = self::addslashes($var[$k]);
			}
		} else {
			$var = addslashes($var);
		}
		return $var;
	}
	
	public static function stripslashes($var){
		if(is_array($var)) {
			foreach($var as $k=>$v) {
				$var[$k] = self::stripslashes($var[$k]);
			}
		} else {
			$var = stripslashes($var);
		}
		return $var;
	}
	
	public static function strlen($str){
		return strlen($str);
	}
	
	public static function htmlspecialchars($var){
		if(is_array($var)) {
			foreach($var as $k=>$v) {
				$var[$k] = self::htmlspecialchars($var[$k]);
			}
		} else {
			$var = htmlspecialchars($var);
		}
		return $var;
	}
	
	public static function convert($str, $in_charset, $out_charset = 'utf-8'){
		if(empty($str) || $in_charset == $out_charset) {
			return $str;
		}

		if(function_exists('iconv')) {
			return iconv($in_charset, $out_charset.'//IGNORE', $str);
		} else if(function_exists('mb_convert_encoding')) {
			return mb_convert_encoding($str, $out_charset, $in_charset);
		}
		
		return $str;
	}
	
	public static function random($len = 4, $str = '123456789abcdefghklmnpqlmnxyzABCDEFGHKLMNPQLMNXYZ'){
		return substr(str_shuffle(str_repeat($str, $len)), 0, $len);
	}
	
	public static function cutStr($string, $length, $charset='utf-8') {
		if(strlen($string) <= $length) {
			return $string;
		}
		
		$string = trim($string);
		
		if(function_exists('mb_substr')){
			return mb_substr($string, 0, $length, $charset);
		}

		$pre = chr(1);
		$end = chr(1);
		$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array($pre.'&'.$end, $pre.'"'.$end, $pre.'<'.$end, $pre.'>'.$end), $string);

		$strcut = '';
		if(strtolower($charset) == 'utf-8') {

			$n = $tn = $noc = 0;
			$strlen = strlen($string);
			while($n < $strlen) {

				$t = ord($string[$n]);
				if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
					$tn = 1; $n++; $noc++;
				} elseif(194 <= $t && $t <= 223) {
					$tn = 2; $n += 2; $noc += 2;
				} elseif(224 <= $t && $t <= 239) {
					$tn = 3; $n += 3; $noc += 2;
				} elseif(240 <= $t && $t <= 247) {
					$tn = 4; $n += 4; $noc += 2;
				} elseif(248 <= $t && $t <= 251) {
					$tn = 5; $n += 5; $noc += 2;
				} elseif($t == 252 || $t == 253) {
					$tn = 6; $n += 6; $noc += 2;
				} else {
					$n++;
				}

				if($noc >= $length) {
					break;
				}

			}
			if($noc > $length) {
				$n -= $tn;
			}

			$strcut = substr($string, 0, $n);

		} else {
			for($i = 0; $i < $length; $i++) {
				$strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
			}
		}

		$strcut = str_replace(array($pre.'&'.$end, $pre.'"'.$end, $pre.'<'.$end, $pre.'>'.$end), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);

		$pos = strrpos($strcut, chr(1));
		if($pos !== false) {
			$strcut = substr($strcut,0,$pos);
		}
		return $strcut;
	}
	
	public static function authEnCode($string, $key='', $expiry = 0){
		$ckey_length = 4;
		
		$key = md5($key ? $key : '');
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckey_length ? substr(md5(microtime()), 0-$ckey_length) : '';

		$cryptkey = $keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);

		$string = sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
		$string_length = strlen($string);

		$result = '';
		$box = range(0, 255);

		$rndkey = array();
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}

		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}

		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}

		return $keyc.str_replace('=', '', base64_encode($result));
	}
	
	public static function authDeCode($string, $key='', $expiry = 0){
		$ckey_length = 4;	// 随机密钥长度 取值 0-32;
		
		$key = md5($key ? $key : '');
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckey_length ? substr($string, 0, $ckey_length) : '';

		$cryptkey = $keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);

		$string = base64_decode(substr($string, $ckey_length));
		$string_length = strlen($string);

		$result = '';
		$box = range(0, 255);

		$rndkey = array();
		for($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}

		for($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}

		for($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}

		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
		
	}
	
	public static function strtotime($str){
		return strtotime($str);
	}
	
}

