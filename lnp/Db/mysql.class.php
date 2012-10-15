<?php

/**
 * mysql Db 数据库相关类
 * @author xanxng <xanxng@gmail.com>
 */
class lnp_Db_mysql{
	private $cfg = array('host'=>'127.0.0.1','port'=>'','dbname'=>'','charset'=>'utf-8','username'=>null,'passwd'=>null);
	private $link = NULL;
	private $queryCnt = 0;
	private $lastquery = null;
	
	public function __construct($config = array()) {
		$this->cfg = $config + $this->cfg;
		$this->connect();
	}
	
	public function connect() {
		if(isset($this->cfg['port']) && $this->cfg['port']) {
			$this->cfg['host'] = $this->cfg['host'].':'.$this->cfg['port'];
		}
		
		if($this->link = @mysql_connect($this->cfg['host'], $this->cfg['username'], $this->cfg['passwd'])){
			mysql_select_db($this->cfg['dbname'], $this->link);
			
		} else {
			$this->link = NULL;
			die('Database connect error['.mysql_errno().']:'.mysql_error());
		}
		
		if($this->errno()) {
			die('Database connect error['.$this->errno().']:'.$this->error());
		}
		if(isset($this->cfg['charset'])) {
			mysql_set_charset($this->cfg['charset'], $this->link);
			mysql_query('SET NAMES '.str_replace('-', '', $this->cfg['charset']), $this->link);
		}
		
		return $this->link;
	}
	
	public function selectDb($dbname){
		return mysql_select_db($dbname, $this->link);
	}
	
	public function errno(){
		return mysql_errno($this->link);
	}
	
	public function error(){
		return mysql_error($this->link);
	}
	
	public function query($sql){
		$this->lastquery = mysql_query($sql, $this->link);
		$this->queryCnt++;
		return $this;
	}
	
	public function exec($sql){
		
		mysql_unbuffered_query($sql, $this->link);
		
		$this->queryCnt++;
		
		return mysql_affected_rows($this->link);
	}
	
	public function queryCnt(){
		return $this->queryCnt;
	}
	
	public function fetch($sql, $result_type = MYSQL_ASSOC){
		$data = array();
		if($this->query($sql)){
			$data = mysql_fetch_array($this->lastquery, $result_type);
			mysql_free_result($this->lastquery);
		}
		return $data;
	}
	
	public function fetchArray($result_type = MYSQL_ASSOC){
		return mysql_fetch_array($this->lastquery, $result_type);
	}
	
	public function fetchAll($sql, $id=NULL){
		$data = array();
		if($this->query($sql)){
			while($r = mysql_fetch_array($this->lastquery, MYSQL_ASSOC)){
				if($id === NULL){
					$data[] = $r;
				} else {
					$data[$r[$id]] = $r;
				}
			}
			mysql_free_result($this->lastquery);
		}
		return $data;
	}
	
	public function escape($str){
		return mysql_real_escape_string($str, $this->link);
	}
	
	public function affectedRows(){
		return mysql_affected_rows($this->link);
	}
	
	public function lastInsertId(){
		return mysql_insert_id($this->link);
	}
}

