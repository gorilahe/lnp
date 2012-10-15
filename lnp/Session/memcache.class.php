<?php

/**
 * session memcache缓存类
 * @author xanxng <xanxng@gmail.com>
 */
class lnp_Session_memcache {
	private $cfg = array(
		'host'=>'127.0.0.1',
		'port'=>11211,
		'prefix'=>'lnpsession_'
	);
	private $obj = null;
	private $expiretime = 180;
	
	/**
	 * 初始化配置信息
	 * @param mixed $config 配置信息:如果为string类型则表示配置文件
	 */
	public function __construct($config=array()) {
		if($config) {
			$this->cfg = $config + $this->cfg;
		}
		$this->expiretime = session_cache_expire()*60;
	}
	
	public function open($savePath, $sessionName) {
		if(is_object($this->obj)) {
			return ture;
		}
		$this->obj = new Memcache;
		$this->obj->addServer($this->cfg['host'], $this->cfg['port']);
		return true;
	}
	
	public function close() {
		return true;
	}
	
	public function read($sessionId) {
		return $this->obj->get($this->cfg['prefix'].$sessionId);
	}
	
	public function write($sessionId, $data) {
		return $this->obj->set($this->cfg['prefix'].$sessionId, $data, $this->expiretime);
	}
	
	public function destroy($sessionId) {
		return $this->obj->delete($this->cfg['prefix'].$sessionId);
	}
	
	public function gc($lifetime) {
		return true;
	}

}