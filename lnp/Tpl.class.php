<?php

class lnp_Tpl{

	function __construct() {
		//$this->template();
	}
	
	public static function init($cfg=array()){
		static $inited = false;
		if($inited){
			return true;
		}
		if(empty($cfg)){
			$cfg = lnpConfig::getData('syscfg', 'tpl');
		}
		self::$defaulttpldir = $cfg['defaulttpldir'];
		self::$tpldir = $cfg['tpldir'];
		self::$objdir = $cfg['objdir'];
		
		self::$echo_start = preg_quote(self::$echo_start, '/');
		self::$echo_end = preg_quote(self::$echo_end, '/');
		self::$noecho_start = preg_quote(self::$noecho_start, '/');
		self::$noecho_end = preg_quote(self::$noecho_end, '/');
		$inited = true;
	}

	public static function assign($k, $v) {
		self::init();
		self::$vars[$k] = $v;
	}
	
	public static function fetch($file, $path=NULL){
		self::init();
		ob_start();
		extract(self::$vars, EXTR_SKIP);
		include self::gettpl($file, $path);
		
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	
	public static function fetchTplCode(){
	
	}

	public static function display($file, $path=null) {
		self::init();
		$content = self::fetch($file, $path);
		header('Content-type: text/html; charset=utf-8');
		//self::$lastcode = $content;
		echo $content;
		return;
	}

	public static function gettpl($file, $path=NULL) {
		self::init();
		//echo $path;
		self::$path = $path;
		if($path){
			$tplfile = rtrim($path, '/').'/'.$file.'.htm';
		} else {
			$tplfile = self::$tpldir.$file.'.htm';
		}//echo $tplfile;
		$objfile = self::$objdir.str_replace('/','_',lnpConfig::getData('sys', 'defaulttheme').'_'.$file).'.php';
		
		if(!is_file($tplfile)) {
			if(!is_file(self::$defaulttpldir.$file.'.htm')){
				die("Template $tplfile not exists!");
			}
			
			$tplfile = self::$defaulttpldir.'/'.$file.'.htm';
		}
		
		if(!is_file($objfile) || (filemtime($objfile) < filemtime($tplfile))) {
			$content = file_get_contents($tplfile);
			$content = self::complie($content);
			$fp = fopen($objfile, 'w');
			fwrite($fp, $content);
			fclose($fp);
		}
		return $objfile;
	}
	
	private static function write(){
		
	}

	private static function complie($template) {
		
		
		$template = preg_replace("/".self::$echo_start."L (.*?)".self::$echo_end."/is", '<?php echo lnpApp::lang("\\1");?>', $template);
		$template = preg_replace("/".self::$echo_start."(.*?)".self::$echo_end."/is", "<?php echo \\1;?>", $template);
		$template = preg_replace('/\{\$([a-zA-Z0-9_\[\]\'"]+)\}/is', "<?php echo $\\1;?>", $template);
		$template = preg_replace('/\{([A-Z_]+)\}/s', "<?php echo \\1;?>", $template);

		
		$template = preg_replace("/".self::$noecho_start."eval (.*?)".self::$noecho_end."/is", '<?php \\1;?>', $template);
		$template = preg_replace("/".self::$noecho_start."for (.*?)".self::$noecho_end."/is", '<?php for(\\1) {?>', $template);
		$template = preg_replace("/".self::$noecho_start."while (.*?)".self::$noecho_end."/is", '<?php while(\\1) { ?>', $template);
		$template = preg_replace("/".self::$noecho_start."var\s+(.*?)\s+(.*?)".self::$noecho_end."/is", '<?php $\\1=\\2; ?>', $template);
		
		$template = preg_replace("/".self::$noecho_start."loop\s+(\S+)\s+(\S+)".self::$noecho_end."/is", '<?php foreach((array)\\1 as \\2){?>', $template);
		$template = preg_replace("/".self::$noecho_start."loop\s+(\S+)\s+(\S+)\s+(\S+)".self::$noecho_end."/is", '<?php foreach((array)\\1 as \\2=>\\3){?>', $template);
		$template = preg_replace("/".self::$noecho_start."\/loop".self::$noecho_end."/is", '<?php } ?>', $template);
		
		$template = preg_replace("/".self::$noecho_start."elseif\s+(.+?)".self::$noecho_end."/is", '<?php } else if(\\1) { ?>', $template);

		$template = preg_replace("/".self::$noecho_start."if\s+(.+?)".self::$noecho_end."/is", '<?php if(\\1) { ?>', $template);

		$template = preg_replace("/".self::$noecho_start."template\s+(\w+?)".self::$noecho_end."/is", '<?php self::gettpl("\\1", self::$path)?>', $template);
		$template = preg_replace("/".self::$noecho_start."template\s+(.+?)".self::$noecho_end."/is", '<?php include self::gettpl(\\1, self::$path);?>', $template);


		$template = preg_replace("/".self::$noecho_start."else".self::$noecho_end."/is", "<?php } else { ?>", $template);
		$template = preg_replace("/".self::$noecho_start."\/if".self::$noecho_end."/is", "<?php } ?>", $template);
		$template = preg_replace("/".self::$noecho_start."\/for".self::$noecho_end."/is", "<?php } ?>", $template);
		$template = preg_replace("/".self::$noecho_start."\/while".self::$noecho_end."/is", "<?php } ?>", $template);

		$template = "<?php if(!defined('LNP_PATH')) exit('Access Denied');?>\r\n$template";
		
		//$template = preg_replace('/\>[\r\n]*\</', '><', $template);
		$template = preg_replace('/\?\>\s*<\?php/', '', $template);
		$template = preg_replace('/\?\>[\r\n]*/', '?>', $template);
		$template = preg_replace('/[\r\n]*<\?php/', '<?php', $template);

		return $template;
	}

}

