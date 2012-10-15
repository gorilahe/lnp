<?php

/**
 * pdo Db 数据库相关类
 * @author xanxng <xanxng@gmail.com>
 */
class lnp_Db_pdo {
	private $cfg = array(
		'driver'=>'mysql',
		'host'=>'127.0.0.1',
		'port'=>'',
		'dbname'=>'',
		'charset'=>'utf-8',
		'username'=>null,
		'passwd'=>null
	);
	
	private $link = NULL;
	private $querycnt = 0;
	private $lastquery = null;
	
	public function __construct($config = array()) {
		$this->cfg = $config + $this->cfg;
		$this->connect();
	}
	
	public function connect() {
		$option = array();
		$dsn = '';
		if($this->cfg['driver'] == 'mysql') {
			$dsn = 'mysql:';
			$dsn .= $this->cfg['host'] ? 'host='.$this->cfg['host'].';' : '';
			$dsn .= $this->cfg['port'] ? 'port='.$this->cfg['port'].';' : '';
			$dsn .= $this->cfg['dbname'] ? 'dbname='.$this->cfg['dbname'].';' : '';
			if($this->cfg['charset']) {
				$dsn .= 'charset='.$this->cfg['charset'].';';
				$option = array(
					PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.str_replace('-', '', $this->cfg['charset']),
				);
			}
		} else if($this->cfg['driver'] == 'sqlite') {
			$dsn = 'sqlite:';
			$dsn .= $this->cfg['dbname'] ? $this->cfg['dbname'] : ':memory:';
		} else {
			die('Database driver null!');
		}
		
		try {
			$this->link = new PDO($dsn, $this->cfg['username'], $this->cfg['passwd'], $option);
		}  catch (PDOException $e) {
			die('Database connect error['.$e->getCode().']: '.$e->getMessage());
		}

		return $this->link;
	}
	
	public function selectDb($dbname){
		return $this->link->query('USE '.$dbname);
	}
	
	public function errno(){
		return $this->link->errorCode();
	}
	
	public function error(){
		return $this->link->errorInfo();
	}
	
	public function query($sql){
		$this->lastquery = $this->link->query($sql);
		$this->querycnt++;
		
		if(!$this->lastquery || $this->errno() > 0){
			die($this->error());
		}
		return $this;
	}
	
	public function exec($sql){
		
		$result = $this->link->exec($sql);
		
		$this->querycnt++;
		if($this->errno()){
			die($this->error());
		}
		return $result;
	}
	
	public function querycnt(){
		return $this->querycnt;
	}
	
	public function fetch($sql, $type = PDO::FETCH_ASSOC){
		$this->query($sql);
		return $this->lastquery->fetch($type);
	}
	
	public function fetchArray($type = PDO::FETCH_ASSOC){
		return $this->lastquery->fetch($type);;
	}
	
	public function fetchAll($sql, $id=NULL){
		//echo $sql;
		$this->query($sql);
		
		$data = array();
		if($id === NULL) {
			$data = $this->lastquery->fetchAll(PDO::FETCH_ASSOC);
		} else {
			while($r = $this->lastquery->fetch(PDO::FETCH_ASSOC)){
				$data[$r[$id]] = $r;
			}
		}
		return $data;
	}
	
	public function escape($str){
		return addslashes($str);
	}
	
	public function affectedRows(){
		return $this->lastquery->rowCount();
	}
	
	public function lastInsertId(){
		return $this->link->lastInsertId();
	}
	
	public function getAttribute($d){
		return $this->link->getAttribute($d);
	}
}

