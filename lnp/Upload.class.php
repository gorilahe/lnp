<?php

/**
 * Upload 文件上传类
 * @author xanxng <xanxng@gmail.com>
 */
class lnp_Upload {
	private $cfg = array(
		'savepath'=>'file', /* 储存目录 */
		'allowext'=>'*', /* 允许文件格式，以','分开，或*代码所有 */
		'override'=>false, /* 文件存在时是否覆盖原文件 */
		'maxsize'=>0, /* 上传文件大小限制，0表示无限制，默认单位字节 */
		'rename'=>true, /* 是否对上传文件重命名 */
		'pathtype'=>'date', /* 目录储存方式 ，可选值：date,hash*/
		'dateformat'=>'Y/m/d', /* 当pathtype为date方式时，目录的格式，参考date函数 */
		'pathlevel'=>3, /* 当pathtype为hash方式时，目录层级数，防止一个目录内文件过多 */
	);
	
	private $files = array(); /* 上传的文件信息列表 */
	private $err = array(); /* 错误信息 */
	private $randseed = '1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM'; /* 重命名时可选字符 */
	
	/**
	 * 构造函数
	 * @param array $config 配置信息
	 */
	public function __construct($config=array()) {
		if($config) {
			$this->cfg = $config + $this->cfg;
			if($this->cfg['savepath']) {
				$this->cfg['savepath'] = rtrim($this->cfg['savepath'], '/\\');
			}
		}
	}
	
	/**
	 * 上传保存前过滤获取上传文件信息
	 * 
	 */
	private function getFiles(){
		if(empty($_FILES)){
			$this->files = array();
			return $this->files;
		}
		
		foreach($_FILES as $k => $v) {
			if(empty($v['name']) || empty($v['tmp_name'])) {
				continue;
			}
			
			if(is_array($v['name'])) {
				foreach($v['name'] as $kk => $vv) {
					if(empty($v['name'][$kk]) || empty($v['tmp_name'][$kk])) {
						continue;
					}
					
					$this->files[] = array(
						'name' => $v['name'][$kk],
						'type' => $v['type'][$kk],
						'tmp_name' => $v['tmp_name'][$kk],
						'size' => $v['size'][$kk],
						'error' => $v['error'][$kk],
						'ext' => $this->fileExt($v['name'][$kk])
					);
				}
			} else {
				$this->files[] = $v + array('ext' => $this->fileExt($v['name']));
			}
		}
	}
	
	/**
	 * 上传
	 * 
	 */
	public function upload() {
		$this->getFiles();
		$randlen = strlen($this->randseed)-1;
		
		foreach($this->files as $k => $v) {
			if($v['error'] > 0) {
				unset($this->files[$k]['tmp_name']);
				$this->err[] = $v['error'];
				continue;
			}
			if(!is_uploaded_file($v['tmp_name'])) {
				unset($this->files[$k]['tmp_name']);
				$this->files[$k]['error'] = -98;
				$this->err[] = -98;
				continue;
			}
			if(!$this->checkAllowType($v['ext'])) {
				unset($this->files[$k]['tmp_name']);
				$this->files[$k]['error'] = -97;
				$this->err[] = -97;
				continue;
			}
			if(!$this->checkSize($v['size'])) {
				unset($this->files[$k]['tmp_name']);
				$this->files[$k]['error'] = -96;
				$this->err[] = -96;
				continue;
			}
			
			$relatepath = $this->getSavePath($v['filename']);
			
			if($this->cfg['rename']) {
				$filename = '';
				for($i=0; $i < 16; $i++) {
					$filename .= $this->randseed[rand(0,$randlen)];
				}
				$filename .= '_'.substr(time(), -6).'.'.$v['ext'];
			} else {
				$filename = $v['name'];
			}
			
			$pathdir = $this->cfg['savepath'].'/'.$relatepath;
			is_dir($pathdir) || mkdir($pathdir, 0777, true);
			$path = $pathdir.'/'.$filename;
			
			if(is_file($path) && !$this->cfg['override']) {
				unset($this->files[$k]['tmp_name']);
				$this->files[$k]['error'] = -95;
				$this->err[] = -95;
				continue;
			}
			
			
			if(!move_uploaded_file($v['tmp_name'], $path)) {
				unset($this->files[$k]['tmp_name']);
				$this->files[$k]['error'] = -99;
				$this->err[] = -99;
			}
			$this->files[$k]['path'] = $relatepath.'/'.$filename;
			unset($this->files[$k]['tmp_name']);
		}
		return $this->files;
	}
	
	/**
	 * 文件后缀名
	 * @param string $filename 文件名
	 */
	private function fileExt($filename) {
		$filename = rtrim($filename, '.');
		if(false === $pos = strrpos($filename, '.')) {
			return '';
		}
		return substr($filename, $pos+1);
	}
	
	/**
	 * 获取文件保存的相对路径目录
	 * @param string $filename 文件名
	 */
	private function getSavePath($filename) {
		if($this->cfg['pathtype'] == 'date') {
           $dir = date($this->cfg['dateformat']);
        } else if($this->cfg['pathtype'] == 'hash') {
            $name = md5($filename);
			$dir = '';
			for($i=0, $maxlevel = $this->cfg['pathlevel']; $i < $this->cfg['pathlevel']; $i++) {
				$dir .= substr($name, $i*2, 2);
			}
			
        } else {
            $dir = 'files';
        }
        return $dir;
	}
	
	/**
	 * 校验允许文件类型
	 * @param string $ext 文件后缀名
	 */
	private function checkAllowType($ext){
		if($this->cfg['allowext'] == '*') {
			return true;
		}
		return in_array($ext, explode(',', $this->cfg['allowext']));
	}
	
	/**
	 * 校验允许上传文件大小限制
	 * @param int $size 文件大小
	 */
	private function checkSize($size){
		if($this->cfg['maxsize'] == 0){
			return true;
		}
		return $this->cfg['maxsize'] >= $size;
	}
}

include 'upload.php';
$u = new lnp_Upload(array('allowext'=>'js,txt', 'maxsize'=>12300,'rename'=>0));
var_dump($u->upload()); 