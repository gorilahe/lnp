<?php

class lnp_Pinyin {
	private static $pinyins = array();
	
	public static function getPinyin($str, $split='') {
		$restr = '';
		$str = trim($str);
		$slen = strlen($str);
		if($slen < 2)
		{
			return $str;
		}
		if(count(self::$pinyins) == 0)
		{
			$fp = fopen(dirname(__FILE__).'/pinyin/pinyin.dat', 'r');
			while(!feof($fp))
			{
				$line = trim(fgets($fp));
				self::$pinyins[$line[0].$line[1]] = substr($line, 3, strlen($line)-3);
			}
			fclose($fp);
		}
		for($i=0; $i<$slen; $i++)
		{
			if(ord($str[$i])>0x80)
			{
				$c = $str[$i].$str[$i+1];
				$i++;
				if(isset(self::$pinyins[$c]))
				{
					if($i>1){
						$restr .= $split.self::$pinyins[$c];
					} else{
						$restr .= self::$pinyins[$c];
					}
					
				}else
				{
					$restr .= "_";
				}
			}else if( preg_match("/[a-z0-9]/i", $str[$i]) )
			{
				$restr .= $str[$i];
			}
			else
			{
				$restr .= "_";
			}
			
		}
		
		return $restr;
	}
}