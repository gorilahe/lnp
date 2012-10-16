<?php

/**
 * Image 图像类
 * @author xanxng <xanxng@gmail.com>
 */
class lnp_Image {
	
	public function __construct() {}
	
	/**
	 * 获取图像文件信息
	 * @param array $file 图像文件
	 */
	public static function getImageSize($file) {
		if(is_file($file)) {
			if (false !== $i = @getimagesize($file)) {
				$imageSize2Index = array(0 => '', 1 => 'gif', 2 => 'jpg', 3 => 'png', 4 => 'swf', 5 => 'psd', 6 => 'bmp', 7 => 'tiff', 8 => 'tiff', 9 => 'jpc', 10 => 'jp2', 11 => 'jpx', 12 => 'jb2', 13 => 'swc', 14 => 'iff', 15 => 'wbmp', 16 => 'xbm');
				
				$info = array();
				$info['width'] = isset($i[0]) ? $i[0] : 0;
				$info['height'] = isset($i[1]) ? $i[1] : 0;
				$info['type'] = isset($i[2]) ? $imageSize2Index[$i[2]] : '';
				$info['mime'] = isset($i['mime']) ? $i['mime'] : '';
				$info['bits'] = isset($i['bits']) ? $i['bits'] : 8;
				$info['channels'] = isset($i['channels']) ? $i['channels'] : 3;
				
				return $info;
			}
		}
		return false;
	}
	
	/**
	 * 生成缩略图
	 * @param string $src 原图像
	 * @param string $dst 缩略图生成位置
	 * @param int $dstwidth 缩略图宽度
	 * @param int $dstheight 缩略图高度
	 * @param bool $bestq 缩略图质量，true:高质量 false:质量比较低
	 */
	public static function thumb($src, $dst, $dstwidth, $dstheight, $bestq = true) {
		$srcinfo = self::getImageSize($src);
		if (!$srcinfo || !$srcinfo['width'] || !$srcinfo['height'] || !$srcinfo['mime']) {
			return false;
		}
		
		if ($srcinfo['width'] <= $dstwidth && $srcinfo['height'] <= $dstheight) {
			return $src != $dst ? copy($src, $dst) : true;
		}
		$srcim = self::imageCreateFrom($src, $srcinfo['mime']);
		
		if (!$srcim) {
			return false;
		}
		
		//imagecreatetruecolor函数不支持gif
		if($srcinfo['mime'] == 'image/gif') {
			$dstim = imagecreat($dstwidth, $dstheight);
		} else {
			$dstim = imagecreatetruecolor($dstwidth, $dstheight);
		}
		$result = self::imageCopyResize($dstim, $srcim, 0, 0, 0, 0, $dstwidth, $dstheight, $srcinfo['width'], $srcinfo['height'], $bestq);
		
		if ($result === false) {
			return false;
		}
		
		$return = self::saveImage($dstim, $dst, $srcinfo['mime']);
		
		imagedestroy($srcim);
		imagedestroy($dstim);
		return $return;
	}
	
	/**
	 * 生成水印
	 * @param string $src 原图像
	 * @param string $waterfile 水印图生成位置
	 * @param int $position 水印图在图像中的位置
	 * @param int $alpha 水印质量
	 */
	public static function waterMark($src, $waterfile, $dst = false, $postion = 9) {
		$srcinfo = self::getImageSize($src);
		if (!$srcinfo || !$srcinfo['width'] || !$srcinfo['height'] || !$srcinfo['mime']) {
			return false;
		}
		
		$waterinfo = self::getImageSize($waterfile);
		if (!$waterinfo || !$waterinfo['width'] || !$waterinfo['height'] || !$waterinfo['mime']) {
			return false;
		}
		
		if ($srcinfo['width'] < $waterinfo['width'] + 5 || $srcinfo['height'] < $waterinfo['height'] + 5) {
			return false;
		}
		
		$srcim = self::imageCreateFrom($src);
		$waterim = self::imageCreateFrom($waterfile);
		if (!$srcim || !$waterim) {
			return false;
		}
		
		switch($postion) {
		case 1:
			$x = 5;
			$y = 5;
			break;
		case 2:
			$x = ($srcinfo['width'] - $waterinfo['width']) / 2;
			$y = 5;
			break;
		case 3:
			$x = $srcinfo['width'] - $waterinfo['width'] - 5;
			$y = 5;
			break;
		case 4:
			$x = 5;
			$y = ($srcinfo['height'] - $waterinfo['height']) / 2;
			break;
		case 5:
			$x = ($srcinfo['width'] - $waterinfo['width']) / 2;
			$y = ($srcinfo['height'] - $waterinfo['height']) / 2;
			break;
		case 6:
			$x = $srcinfo['width'] - $waterinfo['width'] - 5;
			$y = ($srcinfo['height'] - $waterinfo['height']) / 2;
			break;
		case 7:
			$x = 5;
			$y = $srcinfo['height'] - $waterinfo['height'] - 5;
			break;
		case 8:
			$x = ($srcinfo['width'] - $waterinfo['width']) / 2;
			$y = $srcinfo['height'] - $waterinfo['height'] - 5;
			break;
		case 9:
			$x = $srcinfo['width'] - $waterinfo['width'] - 5;
			$y = $srcinfo['height'] - $waterinfo['height'] - 5;
			break;
		default:
			$x = rand(5, $srcinfo['width'] - $waterinfo['width'] - 5);
			$y = rand(5, $srcinfo['height'] - $waterinfo['height'] - 5);
			break;
			
		}
		
		self::imageCopyMerge($srcim, $waterim, $x, $y, 0, 0, $waterinfo['width'], $waterinfo['height'], $waterinfo['mime'], 100);
		
		$return = self::saveImage($srcim, $dst ? $dst : $src, $srcinfo['mime']);
		imagedestroy($srcim);
		imagedestroy($waterim);
		return $return;
	}
	
	/**
	 * 拷贝图像
	 * @param string $src 原图像
	 * @param string $waterfile 水印图生成位置
	 * @param int $position 水印图在图像中的位置
	 * @param int $alpha 水印质量
	 */
	public static function imageCopyResize($dstim, $srcim, $dstx, $dsty, $srcx, $srcy, $dstw, $dsth, $srcw, $srch, $bestq = false) {
		if (!$srcim || !$dstim) {
			return false;
		}
		
		if ($bestq) {
			return imagecopyresampled($dstim, $srcim, $dstx, $dsty, $srcx, $srcy, $dstw, $dsth, $srcw, $srch);
		} else {
			return imagecopyresized($dstim, $srcim, $dstx, $dsty, $srcx, $srcy, $dstw, $dsth, $srcw, $srch);
		}
	}
	
	public static function imageCopyMerge($srcim, $dstim, $sx, $sy, $dx, $dy, $dw, $dh, $mime, $alpha = 100) {
		if ($mime == 'image/png') {
			imagecopy($srcim, $dstim, $sx, $sy, $dx, $dy, $dw, $dh);
		} else {
			imagecopymerge($srcim, $dstim, $sx, $sy, $dx, $dy, $dw, $dh, $alpha);
		}
	}
	
	public static function imageCreateFrom($src, $mime = '') {
		if (!$mime) {
			$temp = self::getImageSize($src);
			if (!$temp || !$temp['width'] || !$temp['height'] || !$temp['mime']) {
				return false;
			}
			$mime = $temp['mime'];
		}
		$imagetype = imagetypes();
		$err = '';
		switch ($mime) {
		case 'image/gif':
			if ($imagetype & IMG_GIF) {
				$srcim = imagecreatefromgif($src);
			} else {
				$err = 'GIF not supported';
			}
			break;
		case 'image/jpeg':
			
			if ($imagetype & IMG_JPG) {
				$srcim = imagecreatefromjpeg($src);
			} else {
				$err = 'JPEG/JPG not supported';
			}
			break;
		case 'image/png':
			if ($imagetype & IMG_PNG) {
				$srcim = imagecreatefrompng($src);
			} else {
				$err = 'PNG not supported';
			}
			break;
		case 'image/wbmp':
			if ($imagetype & IMG_WBMP) {
				$srcim = imagecreatefromwbmp($src);
			} else {
				$err = 'WBMP not supported';
			}
			break;
		case 'image/bmp':
			return false;
			break;
		default:
			return false;
			break;
		}
		
		return $srcim;
	}
	
	public static  function saveImage($dstim, $dst, $mime = 'image/jpeg', $jpgquc = 90) {
		if (!$dst || !$dstim) {
			return false;
		}
		$dir = dirname($dst);
		is_dir($dir) || mkdir($dir, 0777, true);
		switch ($mime) {
		case 'image/gif':
			$return = imagegif($dstim, $dst);
			break;
		case 'image/jpeg':
			$return = imagejpeg($dstim, $dst, $jpgquc);
			break;
		case 'image/png':
			$return = imagepng($dstim, $dst);
			break;
		case 'image/wbmp':
			$return = imagewbmp($dstim, $dst);
			break;
		default:
			$return = false;
			break;
		}
		
		return $return;
	}
	
	public function showImage($im, $mime = 'image/jpeg'){
		header("Content-type: $mime");
		if(is_string($im)){
			if(!is_file($im)) {
				echo $im;
				return;
			}
			$info = self::getimageSize($im);
			$im = self::imageCreateFrom($im);
			$mime = $info['mime'];
		}
		
		switch($mime) {
			case 'image/gif':
				$return = imagegif($im);
				break;
			case 'image/jpeg':
				$return = imagejpeg($im);
				break;
			case 'image/png':
				$return = imagepng($im);
				break;
			case 'image/wbmp':
				$return = imagewbmp($im);
				break;
			default:
				$return = false;
				break;
		}
		$im && imagedestroy($im);
	}
}
