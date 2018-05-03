<?php
# virtualizor 控制面板 
# 端口一般为 4083

$host = "https://vps.host.ltd:4083/index.php"; # http:// 默认为 4082
$apikey = "JASREWSGRTYTRW5RUD1";
$apipass = "vg5hlljqgfsd657yjhfth6cntnz7r5";

$url = $host . '?act=listvs&api=json&apikey=' . $apikey . '&apipass=' . $apipass;
$html = getipinfo($url);
$array = json_decode($html,true)['vs']['9514'];

echo '<pre>';
//print_r($array);
echo $os = $array['os_name'] . "\r\n";
echo $ip = $array['ips']['s186'] . "\r\n";
echo '剩余流量' . $res_bandwidth = $array['bandwidth'] - $array['used_bandwidth'] . "\r\n";

//$ram = $array['ram'];
//$ram_max = $array['burst'];
//$swap = $array['swap'];
//$array['cpu'];
//cpu核数 $array['cores'];
//cpu使用率 $array['cpu_percent'];

# 用 CURL 获取网页内容
function getipinfo($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
	if($httpCode == 404) return false;
    else{
        return $data;
	}
}
