<?php

/**
 * Captcha 验证码类
 * @todo 支持中文等其他类型验证码
 * @author xanxng <xanxng@gmail.com>
 */
class lnp_Captcha {
    private $cfg = array(
		'bgcolor'=>'ffffff', /* 背景颜色 */
		'length'=>4, /* 生成字符数量 */
		'width'=>130, /* 图片宽度 */
		'height'=>45, /* 图片高度 */
		'writedot'=>false, /* 添加干扰点 */
		'writeline'=>true, /* 添加干扰线 */
		'padding'=>5, /* 边距 */
		'size'=>30, /* 字体大小 */
		'seed'=>'346789ABCDEFGHJKLMNPQRTUVWXYabcdefhjkmnpwxy', /* 文字范围 */
		'ttl'=>120, /* 有效时间 */
		'valuekey'=>'lnp_captcha_key', /* session储存字条的key名 */
		'ttlkey'=>'lnp_cpatcha_ttl', /* session储存有效时间的key名 */
		'font'=>array(
			'AntykwaBold',
			'Ding-DongDaddyO',
			'Duality',
			'FetteSteinschrift',
			'StayPuft'
		), /* 字体列表 */
	);

    private $im = null;
	
	/**
	 * 构架函数
	 * @param array $config 配置信息
	 */
    public function __construct($config = array()) {
        isset($_SESSION) || session_start();

        $this->init($config);
    }
	
	/**
	 * 合并配置
	 * @param array $config 配置信息
	 */
    public function init($config=array()) {
        if($config) {
			$this->cfg = $config + $this->cfg;
		}
	}
	
	/**
	 * 生成并显示验证码
	 * @param string $type 生成文件类型,gif,png,jpg...
	 */
    public function display($type = 'png') {
        $this->creatImage();
        $type = strtolower($type);
        $func = "image$type";

        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Content-type: image/$type");

        $func($this->im);
        imagedestroy($this->im);
    }
	
	/**
	 * 生成验证码图片，但不显示
	 * 
	 */
    private function creatImage() {
        $this->im = imagecreate($this->cfg['width'], $this->cfg['height']);
		
		//背景颜色转换 see:http://php.net/manual/zh/function.imagecolorallocate.php
		$int = hexdec($this->cfg['bgcolor']);
        imagecolorallocate($this->im, 0xFF & ($int >> 0x10), 0xFF & ($int >> 0x8), 0xFF & $int);
        
        $word = $this->makeWord();

        $_SESSION[$this->cfg['valuekey']] = $word;
        $_SESSION[$this->cfg['ttlkey']] = time() + $this->cfg['ttl'];
		
		//得到字体
		$font = is_array($this->cfg['font']) ? $this->cfg['font'][array_rand($this->cfg['font'])] : $this->cfg['font'];
		$fontpath = dirname(__FILE__).'/captchafont/'.$font.'.ttf';
		
		//生成文字
        for ($i = 0; $i < $this->cfg['length']; $i++) {
            $text = substr($word, $i, 1);
            $x = $this->cfg['padding'] + $i * $this->cfg['size'];
            $y = rand(0.6 * $this->cfg['height'], 0.8 * $this->cfg['height']);
            $color = imagecolorallocate($this->im, rand(50, 200), rand(50, 200), rand(50, 200));
            imagettftext($this->im, $this->cfg['size'], rand(-20,20), $x, $y, $color, $fontpath, $text);
        }

        $this->cfg['writedot'] && $this->writeDot();
		$this->cfg['writeline'] && $this->writeLine();
    }
	
	/**
	 * 生成验证码字符串
	 * 
	 */
    protected function makeWord() {
        $str = str_shuffle(str_repeat($this->cfg['seed'], $this->cfg['length']));
        return substr($str, 0, $this->cfg['length']);
    }
	
	/**
	 * 添加干扰点
	 * 
	 */
    private function writeDot() {
        $pointLimit = rand(300, 400);
        for ($i = 0; $i < $pointLimit; $i++) {
            $x = rand($this->cfg['padding'], $this->cfg['width'] - $this->cfg['padding']);
            $y = rand($this->cfg['padding'], $this->cfg['height'] - $this->cfg['padding']);
            $color = imagecolorallocate($this->im, rand(0,255), rand(0,255), rand(0,255));

            imagesetpixel($this->im, $x, $y, $color);
        }
	}
	
	/**
	 * 添加干扰线
	 * 
	 */
	private function writeLine() {
		$lineLimit = rand(4, 8);
        for($i = 0; $i < $lineLimit; $i++) {
            $x1 = rand($this->cfg['padding'], $this->cfg['width'] - $this->cfg['padding']);
            $y1 = rand($this->cfg['padding'], $this->cfg['height'] - $this->cfg['padding']);
            $x2 = rand($x1, $this->cfg['width'] - $this->cfg['padding']);
            $y2 = rand($y1, $this->cfg['height'] - $this->cfg['padding']);

            imageline($this->im, $x1, $y1, $x2, $y2, rand(50, 155));
        }
	}
	
	/**
	 * 验证验证码
	 * @param string $value 待验证字符
	 * @param bool $case 区别大小写
	 * 
	 */
    public function check($value, $case = false) {
        $expire = $_SESSION[$this->cfg['ttlkey']];
        $captcha = $_SESSION[$this->cfg['valuekey']];

        unset($_SESSION[$this->cfg['valuekey']]);
        unset($_SESSION[$this->cfg['ttlkey']]);

        if($expire && time() > $expire) {
            return false;
        }
		
		if(empty($captcha)){
			return false;
		}

        if($case) {
			return strcmp($value, $captcha) === 0 ? true : false;
		}
		return strcasecmp($value, $captcha) === 0 ? true : false;
	}
	
	/**
	 * 获取已生成的验证码值
	 * 
	 */
	public function getWord(){
		return isset($_SESSION[$this->cfg['valuekey']]) ? $_SESSION[$this->cfg['valuekey']] : null;
	}

}

//$i = new lnp_Captcha(array('writeline'=>0,'writedot'=>1));
//$i->display();
//echo $i->getSeed();
//var_dump($i->check($i->getSeed()));