<?php

class lnp_Validate{
	
	public static function isEmpty($val) {
		return empty($val);
	}
	
	public static function isNumeric($val) {
		return is_numeric($val);
	}
	
	public static function isUrl($val) {
		return preg_match('/^(http|https):\/\/[\w]+\.[\w]+[\S]*/', $val);
	}
	
	public static function isEmail($val) {
		return preg_match('/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/', $val);
	}
	
	
}