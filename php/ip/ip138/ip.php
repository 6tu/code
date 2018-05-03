<?php
set_time_limit(0);
header("content-Type: text/html; charset=Utf-8");
$ips = file_get_contents('./ip.txt');
$array = explode("\r\n",$ips);
$n = count($array);
$ipinfo = '';
$dbip = '';
for($i=0; $i < $n; $i++){
$ip = $array[$i];
$url138 = 'http://ip138.com/ips138.asp?ip='.$ip.'&action=2';
$ipaddr138 = get_ipaddr($url138);

//$urldbip = 'http://127.0.0.1/dbip/dbip.php?ip=' . $ip;
//$dbip = get_dbip($urldbip);
$ipinfo .= $ip . '  ' . $ipaddr138 . '   ||  ' . $dbip . " \r\n<br>";
}
echo $ipinfo;


function get_ipaddr($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    //curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    $result = curl_exec($ch);
    curl_close($ch);
	
	$array = @explode("<td",$result);
	$arr = @explode("</td",$array['23']);

	$ipaddr = strip_tags('<td'.$arr['0']);
	$ipaddr = mb_convert_encoding($ipaddr, "UTF-8", "GBK"); 
	$ipaddr = str_replace(array("本站数据：","参考数据1：","参考数据2："), ', ', $ipaddr);
    return $ipaddr;
}

function get_dbip($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
	$result = mb_convert_encoding($result, "GBK", "UTF-8"); 
    $array = explode("countryName",strip_tags($result));
    $ipaddr = @str_replace("\r\n", ', ', $array['1']);
    $ipaddr = str_replace(array("city", "stateProv"), array("城市", "省份"), $ipaddr);
    $ipaddr = '国家'.$ipaddr;
	$result = mb_convert_encoding($result, "UTF-8", "GBK"); 
    return $ipaddr;
}