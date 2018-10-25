<?php
# 母鸡数据更新在罗马尼亚时间上午9点，把时间向前推9+个小时
# 这样方便采集数据
date_default_timezone_set('Europe/Bucharest');   # 罗马尼亚时间
$t0 = date('ymdHis',time());
date_default_timezone_set("America/Los_Angeles");# 洛杉矶时间
$t1 = date('ymdHis',time());
 echo "Bucharest: $t0 - Los_Angeles: $t1";

$log_dir = './log/';
if(!is_dir($log_dir)) mkdir($log_dir,0777);
$log = $log_dir . 'vps-' . date('ymd',time()) . '.log';

$cookie_dir = './cookie/';
if(!is_dir($cookie_dir)) mkdir($cookie_dir,0777);
$cookie_file = $cookie_dir . time() . '.cookie';
setcookie("PHPSESSID", "vc0heoa6lfsi3gger54pkns152");

if (file_exists($log)) {
    echo file_get_contents($log);
    exit;
}

$url = 'https://secure.hostsolutions.ro';
$dologin = $url . '/dologin.php';
$clientarea = $url . '/clientarea.php';
$details = $clientarea . '?action=productdetails&id=5246&rrd=0&timeframe=hour&language=chinese';
$details_json = $clientarea . '?action=productdetails&id=5246&api=json&act=vpsmanage';

$token = getResponse($clientarea, [], $cookie_file);
preg_match('/<input type="hidden" name="token" value="(.*)"/U', $token, $match);
// print_r($match);
$post['token'] = $match[1];
$post['username'] = 'admin@liuyun.org';
$post['password'] = 'password';

$login = getResponse($dologin, $post, $cookie_file);
$pd = getResponse($details, $data=[], $cookie_file);
$pd_json = getResponse($details, $data=[], $cookie_file);

unlink($cookie_file);

# 数据索取完毕，先精简 $pd
# 
# 删除js
$preg = "/<script[\s\S]*?<\/script>/i";
$html = preg_replace($preg, "", $pd, -1);
unset($pd);

# 删除冗余
preg_match("|<div class=\"col-md-9 pull-md-right main-content\">([^^]*?)<div class=\"tab-pane fade in\" id=\"tabDownloads\">|u", $html, $pd_array1); # 截取主要部分
$pd_array2 = explode('</div></div></div>' , $pd_array1[1]);
$pd_array3 = explode('<div>' , $pd_array2[0] . $pd_array2[3]); # 分三个部分，重整理
$pd_array4 = explode('<center>' , $pd_array3[1]);
$pd_array5 = explode('<div class="col-sm-10 col-sm-offset-1">' , $pd_array3[2]);
$pd_array6 = explode('<tr class="orowcolor">' , $pd_array4[3]);
$ut = str_replace(':' , '在线时间 ' , $pd_array6[3]);
$html = $pd_array3[0] . $pd_array5[0] . $ut . $pd_array4[1] . $pd_array5[1];
// print_r($pd_array3);
// echo $html;
// exit;

# 多个空格转为一个空格
$html = str_replace('<div class="col-sm-7 text-left">', '<p>', $html);
$html = str_replace('<div class="col-sm-5 text-right">', '<div class="col-sm-5 text-right"><hr>', $html);
$html = (strip_tags($html, '<hr><p><h4><b>'));
$html = preg_replace("/(\r\n|\n|\r|\t)/i", '', $html);
$html = preg_replace ("/\s(?=\s)/", "\\1", $html);
$html = str_replace(array(' <', '> '), array('<', '>'), $html);
$html = str_replace(array('</h4>', '<h4>'), array(' ', "\r\n"), $html);
$html = str_replace(array('</b>', '<b>'), array(' ', "\r\n"), $html);
$html = str_replace(array('<p>', '<hr>'), array(' ', "\r\n"), $html);
$html = str_replace(array('<p class="text-muted">', '</p>'), array("\r\n", ' '), $html);
$html = str_replace(array('RAM', 'ns2.'), array("内存", '  ns2.'), $html);
$html = str_replace(array('要求撤銷', '其他資訊', '資源使用情形'), array("\r\n", '', ''), $html);
$html = str_replace("伺服器資訊 ", "\r\n\r\n<b>伺服器資訊</b>", $html);
$html = str_replace('OS Template', "\r\n\r\n<b>其他資訊</b>\r\nOS Template ", $html);
$html = str_replace('在线时间', "\r\n\r\n<b>資源使用情形</b>\r\n在线时间", $html);
$html = str_replace(" \r\n", "\r\n", $html);
$html = trim($html);
$html = "<pre><br>\r\n<b>產品詳情</b>\r\n" . $html;
echo $html;
echo "</pre><br>\r\n";
file_put_contents($log, $html . "\r\n\r\n$t0" . '[Bucharest]');


$arr = json_decode($pd_json, true);
//print_r($arr);
$info = array(  vps => array(
                    title => $arr['title'],
                    vpsid => $arr['vpsid'],
                    vps_name => $arr['info']['vps']['vps_name'],
                    act => $arr['act'],
                    timenow => $arr['timenow'],
                    disable_apicredential => $arr['disable_apicredential'],
                    disable_backup_cp => $arr['disable_backup_cp'],
                    enable_eu_iso => $arr['enable_eu_iso'],
                    disable_webuzo => $arr['disable_webuzo'],
                    osreinstall_limit => $arr['info']['vps']['osreinstall_limit'],
                    io => $arr['info']['vps']['io'],
                    vnc => $arr['info']['vps']['vnc'],
                    band_suspend => $arr['info']['vps']['band_suspend'],
                    tuntap => $arr['info']['vps']['tuntap'],
                    ppp => $arr['info']['vps']['ppp'],
                    network_speed => $arr['info']['vps']['network_speed'],
                    upload_speed => $arr['info']['vps']['upload_speed'],
                    dns_nameserver => $arr['info']['vps']['dns_nameserver'],
                ),
                system => array(
                    status => $arr['info']['status'],
                    uptime => $arr['info']['uptime'],
                    virt => $arr['info']['vps']['virt'],
                    hostsname => $arr['info']['vps']['hostname'],
                    os_name => $arr['info']['vps']['os_name'],
                    fstype => $arr['info']['os']['fstype'],
                    filename => $arr['info']['os']['filename'],
                    size => $arr['info']['os']['size'],
                    ip => $arr['info']['ip']['0'],
                    ip_count => $arr['info']['ip_count'],
                    hostname => $arr['info']['hostname'],
                ),
                bandwidth => array(
                    limit_gb => $arr['info']['bandwidth']['limit_gb'],
                    used_gb => $arr['info']['bandwidth']['used_gb'],
                    free_gb => $arr['info']['bandwidth']['free_gb'],
                    percent => $arr['info']['bandwidth']['percent'],
                    percent_free => $arr['info']['bandwidth']['percent_free'],
                ),
                cpu => $arr['info']['cpu'],
                ram => $arr['info']['ram'],
                disk => $arr['info']['disk'],
                inodes => $arr['info']['inodes'],
            );
echo "<br><pre>\r\n";
print_r($info);
echo "</pre><br>\r\n";


function getResponse($url, $data = [], $cookie_file = '', $timeout = 10){
    if(empty($cookie_file)){
        $cookie_file = '.cookie';
    }
    $url_array = parse_url($url);
    $host = $url_array['scheme'] . '://' . $url_array['host'];
    if(!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    else $lang = 'zh-CN,zh;q=0.9';
    if(!empty($_SERVER['HTTP_REFERER'])) $refer = $_SERVER['HTTP_REFERER'];
    else $refer = $host . '/clientarea.php?incorrect=true';
    if(!empty($_SERVER['HTTP_USER_AGENT'])) $agent = $_SERVER['HTTP_USER_AGENT'];
    else $agent = 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.67 Safari/537.36';
    // echo $lang . "<br>\r\n" . $refer . "<br>\r\n" . $agent . "<br>\r\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    curl_setopt($ch, CURLOPT_REFERER, $refer);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept-Language: " . $lang));
    if(!empty($data)){
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file); # 取cookie的参数是
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file); # 发送cookie
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    try{
        $handles = curl_exec($ch);
        curl_close($ch);
        return $handles;
    }
    catch(Exception $e){
        echo 'Caught exception:', $e -> getMessage(), "\n";
    }
    // unlink($cookie_file);
}
