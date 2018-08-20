<?php
set_time_limit(0);

$srcpath = 'F://dymf/html/';
$filelist = 'F:/q2.txt';
$topath = 'F://dymf2/';

$list = file_get_contents($filelist);
$list_array = explode("\r\n", $list);
print_r($list_array);
foreach($list_array as $v){
	$dir = $srcpath . $v;
	echo $dir . "\r\n";
    $article = file_get_contents($dir);

    $preg = "/<script[\s\S]*?<\/script>/i";
    $html = preg_replace($preg, "", $article, -1);
    file_put_contents($topath . $v, $html);
}
?>