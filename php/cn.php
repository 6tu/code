<?php

# 强制域名访问
if($_SERVER['HTTP_HOST'] !== 'gce.6tu.me'){
    Header("HTTP/1.1 301 Moved Permanently");
    header('Location: http://gce.6tu.me' .$_SERVER['REQUEST_URI']);
}

# 强制 HTTPS
//$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')) ? 'https://' : 'http://';
//if(isset($_SERVER['HTTPS']) && $_SERVER["HTTPS"] <> "on"){
//    //echo '<html><meta http-equiv="refresh" content="0;url=http://gce.6tu.me/cn/cn.php"></html> ';
//    //Header("HTTP/1.1 301 Moved Permanently");
//    header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
//}

#****** 仅对中国 IP 加速
$ip_china  = './china-ip.json';
$ip_remote = $_SERVER["REMOTE_ADDR"];

if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'){
$jsurl_array = array(
    'https://statics-1252761862.cos-website.ap-shanghai.myqcloud.com/js/jquery-1.11.3.min.js',
    );
}else{
    $jsurl_array = array(
    'http://qcdn.popc.net/js/jquery-1.11.3.min.js',
    'http://qncdn.popc.net/js/jquery-1.11.3.min.js',
    );
}

if(check_is_china_ip($ip_remote, $ip_china)){
    $key = array_rand($jsurl_array, 1);
    $jsurl = $jsurl_array[$key];
    $speed = true;
}else{
    $jsurl = './js/jquery-1.11.3.min.js';
    $speed = false;
}

echo $jsurl;

# ****** 从json格式数据中检测IP是否来自中国
function check_is_china_ip($ip, $ipjson){
    $ip_addr = explode('.', $ip);
    if(count($ip_addr) < 4) return false;
    $a1 = (int)$ip_addr[0];
    $a2 = (int)$ip_addr[1];
    $a3 = (int)$ip_addr[2];
    $a4 = (int)$ip_addr[3];
    $s_china = file_get_contents($ipjson);
    $tb_china = json_decode($s_china, 1);
    unset($s_china);
    if(!isset($tb_china[$a1][$a2]) || count($tb_china[$a1][$a2]) == 0) return false;
    $a = $a3 * 256 + $a4;
    foreach($tb_china[$a1][$a2] as $d){
        if($a >= $d['s'] && $a <= $d['e']){
            return true;
        }
    }
    return false;
}
