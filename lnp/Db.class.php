<?php

/**
 * Db 数据库相关类
 * @author xanxng <xanxng@gmail.com>
 */
class lnp_Db {
	private static $inited = false;
	private static $link = array();
	private static $curlink = NULL;
	private static $cfg = array();
	private static $debug = false;
	private static $lastquery = null;
	private static $lastsql = null;
	
	public static function init($config=array()) {
		if(!empty($config)){
			self::$cfg = $config;
		} else {
			self::$cfg = lnp_Config::get('db', array());
		}
		self::$inited = true;
	}
	
	public static function factory($config=array()) {
		$classname = 'lnp_Db_'.$config['adapter'];
		return new $classname($config);
	}
	
	public static function connect($config=array()){
		if(empty($config['adapter'])){
			die('Database adapter driver error!');
		}
		$classname = 'lnp_Db_'.$config['adapter'];
		self::$curlink = new $classname($config);
		return self::$curlink;
	}
	
	public static function db() {
		if(is_object(self::$curlink)) {
			return self::$curlink;
		}
		self::$inited || self::init();
		return self::connect(self::$cfg);
	}
	
	public static function selectDb($dbname) {
		return self::db()->selectDb($dbname);
	}
	
	public static function setDebug($debug){
		self::$debug = $debug;
	}
	
	public static function t($tbname, $tbpre=NULL){
		if($tbpre === NULL){
			$tbpre = self::$cfg['tbpre'];
		}
		return $tbpre.$tbname;
	}
	
	public static function query($sql) {
		self::$lastsql = $sql;
		return self::db()->query($sql);
	}
	
	public static function fetch($sql){
		return self::db()->fetch($sql);
	}
	
	public static function fetchArray($result){
		return self::db()->fetchArray($result);
	}
	
	public static function r1($sql){
		return self::db()->fetch($sql.' LIMIT 1');
	}
	
	public static function r($sql, $id=NULL){
		return self::db()->fetchAll($sql, $id);
	}
	
	public static function select($table, $field='*', $where='', $order='', $limit='', $id=NULL){
		if(empty($field) && $field !== '0'){
			$field = '*';
		}
		$sql = "SELECT $field FROM ".self::t($table);
		$sql .= self::parseWhere($where);
		if($order){
			$sql .= " ORDER BY $order";
		}
		if($limit){
			$sql .= " LIMIT $limit";
		}
		//echo $sql;
		return self::r($sql, $id);
	}
	
	public static function select1($table, $field='*', $where='', $order=''){
		if(empty($field) && $field !== '0'){
			$field = '*';
		}
		$sql = "SELECT $field FROM ".self::t($table);
		$sql .= self::parseWhere($where);
		if($order){
			$sql .= " ORDER BY $order";
		}
		
		return self::r1($sql);
	}
	
	public static function fetchAll($sql, $id=NULL){
		return self::r($sql);
	}
	
	public static function c($table, $value, $lastid=TRUE) {
		$sql = 'INSERT '.self::t($table).' SET ';
		if(is_array($value)){
			foreach($value as $k => $v){
				$sql .= "`$k`='".self::escape($v)."',";
			}
			$sql = rtrim($sql, ',');
		} else {
			$sql .= $value;
		}
		$rst = self::exec($sql);
		if($lastid){
			return self::lastInsertId();
		}
		return $rst;
	}
	
	public static function insert($table, $value, $lastid=FALSE) {
		return self::c($table, $value, $lastid);
	}
	
	public static function u($table, $value, $where='') {
		$sql = 'UPDATE '.self::t($table).' SET ';
		if(is_array($value)){
			foreach($value as $k => $v){
				$sql .= "`$k`='".self::escape($v)."',";
			}
			$sql = rtrim($sql, ',');
		} else {
			$sql .= $value;
		}
		
		$wheresql = self::parseWhere($where);
		
		return self::exec($sql.$wheresql);
	}
	
	public static function update($table, $value, $where='') {
		return self::u($table, $value, $where);
	}
	
	public static function d($table, $where=NULL){
		if($where === NULL){
			return self::db()->exec('TRUNCATE '.self::t($table));
		}
		$wheresql = self::parseWhere($where);
		return self::db()->exec('DELETE FROM '.self::t($table)." $wheresql");
	}
	
	public static function delete($table, $where=NULL){
		return self::d($table, $where);
	}
	
	public static function replace($table, $value, $field=null) {
		if($field){
			if(is_array($field)){
				$field = '`'.implode('`,`', $field).'`';
			}
			if(is_array($value)){
				$value = self::escape($value);
				$varr = array();
				foreach($value as $v){
					$varr[] = "('".implode("','", $v)."')";
				}
				$value = implode(',', $varr);
			}
			$sql = 'REPLACE INTO '.self::t($table)." ({$field}) VALUES ".$value;die($sql);
			return self::exec($sql);
		}
		
		$sql = 'REPLACE INTO '.self::t($table).' SET ';
		if(is_array($value)){
			foreach($value as $k => $v){
				$sql .= "`$k`='".self::escape($v)."',";
			}
			$sql = rtrim($sql, ',');
		} else {
			$sql .= $value;
		}
		die($sql);
		return self::exec($sql);
	}
	
	public static function escape($str) {
		if(is_array($str)){
			foreach($str as $k => $v){
				$str[$k] = self::escape($str[$k]);
			}
		} else {
			$str = self::db()->escape($str);
		}
		return $str;
	}
	
	public static function exec($sql) {
		self::$lastsql = $sql;
		return self::db()->exec($sql);
	}
	
	public static function queryCnt(){
		return self::db()->queryCnt();
	}
	
	public static function crease($table, $value, $where=NULL){
		$sql = 'UPDATE '.self::t($table).' SET ';
		if(is_array($value)){
			foreach($value as $k => $v){
				$v = self::escape($v);
				$sql .= "$k=$k$v,";
			}
			$sql = trim($sql, ',');
		} else {
			$sql .= $value;
		}
		
		$wheresql = self::parseWhere($where);
		
		return self::exec($sql.$wheresql);
	}
	
	public static function errno() {
		return self::db()->errno();
	}
	
	public static function error() {
		return self::db()->error();
	}
	
	public static function lastInsertId() {
		return self::db()->lastInsertId();
	}
	
	public static function getOne($sql) {
		$data = self::fetch($sql);
		if($data){
			return current($data);
		}
		return NULL;
	}
	
	public static function count($table, $where=''){
		$wheresql = self::parseWhere($where);
		
		$sql = 'SELECT COUNT(*) FROM '.self::t($table).$wheresql;
		
		return self::getOne($sql);
	}
	
	private static function parseWhere($where){
		if(empty($where)){
			return '';
		}
		$wheresql = ' WHERE ';
		if(is_array($where)){
			$and = '';
			foreach($where as $k => $v){
				$wheresql .= "$and `{$k}`='".self::escape($v)."'";
				$and = ' AND ';
			}
		} else {
			$wheresql .= $where;
		}
		return $wheresql;
	}
	
	public static function debug(){
		echo '<strong>SQL ERROR:</strong><br>';
		echo self::errno().': '.self::error().'<br>';
		echo 'SQL: '.self::$lastsql;
	}
	
}
