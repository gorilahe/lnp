<?php

/**
 * Page 简单的分页类
 * @author xanxng <xanxng@gmail.com>
 */
class lnp_Page{
	/**
	 * {pageurl} 链接URL
	 * {pagenum} 某页数字
	 * {totalpage} 总页数
	 * {curpage} 当前页
	 */
	private static $cfg = array(
		'start' => '<div class="page">', /* 开始代码 */
		'prev' => '<a class="prev" href="{pageurl}" target="_self">上一页</a>', /* 上一页 */
		'next' => '<a class="nxt" href="{pageurl}" target="_self">下一页</a>', /* 下一页 */
		'first' => '<a class="first" href="{pageurl}" target="_self">{pagenum}...</a>', /* 翻到第一页 */
		'last' => '<a class="last" href="{pageurl}" target="_self">...{pagenum}</a>', /* 翻到最后一页 */
		'current' => '<strong>{pagenum}</strong>', /* 当前页 */
		'page' => '<a href="{pageurl}" target="_self">{pagenum}</a>', /* 其它列 */
		'end' => '</div>', /* 结束代码 */
		'showlinks' => 9, /* 显示每列数 */
	);
	
	/**
	 * 配置
	 * @param array $config 配置信息
	 */
	public static function setConfig($config=array()){
		self::$cfg = $config + self::$cfg;
	}
	
	/**
	 * 生成分页
	 * @param int $num 内容总数
	 * @param int $perpage 每页内容数
	 * @param int $curpage 当前页码
	 * @param string $mpurl URL规则
	 * @param array $config 新配置
	 */
	public static function page($num, $perpage, $curpage, $mpurl, $config=array()) {
		if($config) {
			self::$cfg = $config + self::$cfg;
		}
		
		$page = (int)self::$cfg['showlinks'];
		
		if($num > $perpage) {

			$offset = floor($page * 0.5);

			$pages = ceil($num / $perpage);
			$curpage = $curpage > $pages ? $pages : $curpage;
			
			if($page > $pages) {
				$from = 1;
				$to = $pages;
			} else {
				$from = $curpage - $offset;
				$to = $from + $page - 1;
				if($from < 1) {
					$to = $curpage + 1 - $from;
					$from = 1;
					if($to - $from < $page) {
						$to = $page;
					}
				} else if($to > $pages) {
					$from = $pages - $page + 1;
					$to = $pages;
				}
			}
			
			$multipage = $curpage > 1 ? str_replace(array('{pageurl}', '{pagenum}'), array(($curpage - 1 == 1) ? str_replace('{page}', 1, preg_replace('/\[.*?\]/', '', $mpurl)) : str_replace('{page}', $curpage-1, preg_replace('/\[(.*?)\]/', '\\1', $mpurl)), $curpage-1), self::$cfg['prev']) : '';
			
			$multipage .= ($curpage - $offset > 1 && $pages > $page) ? str_replace(array('{pageurl}', '{pagenum}'), array(str_replace('{page}', 1, preg_replace('/\[.*?\]/', '', $mpurl)), 1), self::$cfg['first']) : '';
			
			for($i = $from; $i <= $to; $i++) {
				$multipage .= str_replace(array('{pageurl}', '{pagenum}'), array(($i == 1) ? str_replace('{page}', 1, preg_replace('/\[.*?\]/', '', $mpurl)) : str_replace('{page}', $i, preg_replace('/\[(.*?)\]/', '\\1', $mpurl)), $i), $i == $curpage ? self::$cfg['current'] : self::$cfg['page']);
			}
			
			$multipage .= $to < $pages ? str_replace(array('{pageurl}', '{pagenum}'), array(($pages == 1) ? str_replace('{page}', 1, preg_replace('/\[.*?\]/', '', $mpurl)) : str_replace('{page}', $pages, preg_replace('/\[(.*?)\]/', '\\1', $mpurl)), $pages), self::$cfg['last']) : '';
			
			$multipage .= $curpage < $pages ? str_replace(array('{pageurl}', '{pagenum}'), array(($curpage + 1 == 1) ? str_replace('{page}', 1, preg_replace('/\[.*?\]/', '', $mpurl)) : str_replace('{page}', $curpage+1, preg_replace('/\[(.*?)\]/', '\\1', $mpurl)), $curpage+1), self::$cfg['next']) : '';

			$multipage = $multipage ? self::$cfg['start'].$multipage.self::$cfg['end'] : '';
			
		}
		if($multipage){
			$multipage = str_replace(array('{totalpages}', '{curpage}'), array($realpages, $curpage), $multipage);
		}
		return $multipage;
	}
}

