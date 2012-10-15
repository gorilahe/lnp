<?php

require dirname(__FILE__).'/sdk.class.php';

$oss = new ALIOSS();
echo '<pre>';
//var_dump($oss->upload_file_by_file('mmimg','tt/test.php',__FILE__));
var_dump($oss->upload_file_by_content('mmimg','tt/test2.php',array('content'=>'fdsfdsfdsf')));

echo '</pre>';