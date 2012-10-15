<?php

/**
 * memcache cache memcache缓存类
 * 不局限于memcache,支持memcache协议基本上都可以，如ttserver
 * @author xanxng <xanxng@gmail.com>
 */
class lnp_Cache_memcache {
	/**
	 *配置信息，servers支持配置多个服务器,servers=>array(array('host'=>'host1', 'port'=>11211),array('host'=>'host2', 'port'=>11211))
	 */
	private $cfg = array(
		'ttl'=>0,/* 缓存时间 */
		'pre'=>'lnp_',/* key前缀 */
		'compress'=>1,/* 是否压缩存储 */
		'servers'=>array(
			'host'=>'127.0.0.1',
			'port'=>11211
		)
	);
	
	
	private $obj = null;//缓存实例对象
	
	/**
	 * 初始化配置信息
	 * @param array $config 配置信息
	 */
	public function __construct($config=array()){
		if($config) {
			$this->cfg = $config + $this->cfg;//合并配置
		}
		
		$this->obj = new Memcache;
		
		//支持多个memcache服务器
		if(isset($this->cfg['servers']['host'])) {
			$this->obj->addServer($this->cfg['servers']['host'], $this->cfg['servers']['port']);
		} else {
			foreach($this->cfg['servers'] as $v){
				$this->obj->addServer($v['host'], $v['port']);
			}
		}
	}
	
	/**
	 * 添加修改
	 * @param string $key 健
	 * @param mixed $val 值
	 */
	public function set($key, $val, $ttl=0){
		if(!$ttl) {
			$ttl = $this->cfg['ttl'];
		}
		return $this->obj->set($this->cfg['pre'].$key, $val, $this->cfg['compress'] ? MEMCACHE_COMPRESSED : 0, $ttl);
	}
	
	/**
	 * 获取值
	 * @param string $key 健
	 */
	public function get($key){
		return $this->obj->get($this->cfg['pre'].$key);
	}
	
	/**
	 * 删除
	 * @param string $key 健
	 */
	public function delete($key) {
		return $this->obj->delete($this->cfg['pre'].$key);
	}
	
	/**
	 * 清空
	 * 
	 */
	public function flush() {
		return $this->obj->flush();
	}
		
}

