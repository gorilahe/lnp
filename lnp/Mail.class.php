<?php

/**
 * Mail 简单的邮件发送类
 * @todo 可以使用其它方式发送
 * @author xanxng <xanxng@gmail.com>
 */
class lnp_Mail{
	
	/**
	 * 发送邮件
	 * @param string $to 接收人，多个人以','分开
	 * @param string $subject 主题
	 * @param string $message 邮件正文内容
	 * @param array $addheader 邮箱头信息
	 */
	public static function send($to, $subject, $message, $addheader=NULL){
		$subject = '=?UTF-8?B?'.base64_encode(str_replace(array("\r","\n"), '', $subject)).'?=';
		$message = chunk_split(base64_encode(str_replace("\n.", "\n..", $message)));
		
		$orgheader = array(
			'MIME-Version'=>'1.0',
			'Content-Type'=>'text/plain; charset=utf-8',
			'X-Mailer'=>'lnpMailer',
			'Content-Transfer-Encoding'=>'base64'
		);
		
		if($addheader){
			$addheader = array_merge($orgheader, (array)$addheader);
		}
		$header = "";
		foreach($addheader as $k => $v){
			$header .= "$k: $v\r\n";
		}
		$result = @mail($to, $subject, $message, $header);
		return $result;
	}
}

