<?php
set_time_limit(0);
header("content-Type: text/html; charset=utf-8");

$head =<<<HEAD
<!DOCTYPE html>
<!--STATUS OK-->
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="content-type" content="text/html;charset=utf-8">
    <title>批量查询 IP 归属地</title>
    <style type="text/css"></style>
    <script type="text/javascript"></script>
</head>

HEAD;

if(empty($_POST['iplist'])){
    $fip = file_get_contents('allow.txt');
    echo $head . "<body><br><center>\r\n" . html_form($fip) . "</center>\r\n</body>\r\n</html>";
    exit(0);
}

# 初步处理 POST 的数据
$fip = '';
$ip = $_POST['iplist'];
$ip = str_replace(' ', '', $ip);
$ip = str_replace("\t", "", $ip);
$ip = str_replace("\r", "", $ip);
$ip = str_replace("\n\n", "\n", $ip);
$ip = rtrim($ip, "\n");
$ip = ltrim($ip, "\n");

# 按重复次数重排 IP
$ip_array = explode("\n", $ip);
$np = count($ip_array);

$ip_new_array = array();
$na = '';
foreach($ip_array as $ipx){
    $n = @substr_count($ip, $ipx);
    $ip_new_array += array($ipx => $n);
}
arsort($ip_new_array);
$na = count($ip_new_array);
$nn = $np - $na;

# 查询归属地，定义颜色和边框
$ipinfo = "";
$i = 1;
foreach($ip_new_array as $key => $value){
    $url138 = 'http://ip138.com/ips138.asp?ip=' . $key . '&action=2';
    $ipaddr138 = get_ipaddr($url138);
    // $ipaddr138 = '';
    if($i%2) $ipinfo .= "    <tr bgcolor='#99ccff'><td>$value</td><td>$key</td><td>$ipaddr138</td></tr>\r\n";
    else     $ipinfo .= "    <tr bgcolor='#4682B4'><td>$value</td><td>$key</td><td>$ipaddr138</td></tr>\r\n";
    $i++;
}

$body = $head . "<body><br><center>\r\n" . html_form($fip);
# td 合并横行和定义宽度， colspan='1' ， style='width:80px'
# <td>兼容IPv6地址</td><td>映射IPv6地址：</td>
$body .= "
<br><br>本次提交<b> $np </b>行，去除重复IP<b> $nn </b>个，共查询<b> $na </b>个IP，归属地数据来源于 ip138.com<br>
<table cellpadding='0' cellspacing='1' border='0' width='90%'>
    <tr bgcolor='#708090'>
        <td style='width:42px'><strong>重复</strong></td>
        <td align='center'><strong>IP地址</strong></td>
        <td style='text-indent:28px; font-weight:bold;'>本地数据 </td>
        <td style='text-indent:28px; font-weight:bold;'>参考数据1</td>
        <td style='text-indent:28px; font-weight:bold;'>参考数据2</td>
    </tr>\r\n";

echo $body . $ipinfo . "</table>\r\n</center>\r\n</body>\r\n</html>";















function html_form($fip){
    $form = '<form action=' . php_self() . ' method="post" id="usrform"><input type="submit"></form>' . "\r\n";
    $form .= '请在下框内输入<b><font color="#FF0000">一列</font></b> IP/域名 ...<br>' . "\r\n";
    $form .= '<textarea rows="13" cols="35" name="iplist" form="usrform">' . "\r\n";
    $form .= "8.8.8.8\r\n8.8.8.8\r\nbaidu.com\r\n$fip</textarea>\r\n";
    return $form;
}

# 获取当前PHP文件名
function php_self(){
    $php_self = substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/') + 1);
    return $php_self;
}

function get_ipaddr($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    // curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_REFERER, "http://www.ip138.com/");
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // curl_setopt($ch,CURLOPT_COOKIEFILE,$cookie);
    $result = curl_exec($ch);
    curl_close($ch);
    
    $array = @explode("<td", $result);
    $arr = @explode("</td", $array['23']);
    $ipaddr = strip_tags('<td' . $arr['0']);
    $ipaddr = mb_convert_encoding($ipaddr, "UTF-8", "GBK");
    $arr_ipv6 = @explode("兼容IPv6地址", $ipaddr);
    $ipaddr = $arr_ipv6[0];
    $ipaddr = str_replace(array("本站数据：", "参考数据1：", "参考数据2："), array("", "</td><td>", "</td><td>"), $ipaddr);
    //$ipaddr = str_replace(array("兼容IPv6地址：", "映射IPv6地址："), array("</td>\r\n<td>", "</td>\r\n<td>"), $ipaddr);
    return $ipaddr;
}

