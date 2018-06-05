<?php
set_time_limit(0);
header("content-Type: text/html; charset=utf-8");

if(empty($_POST['iplist'])){
    echo "<br><center>\r\n";
    echo html_form() . '</center>';
    exit(0);
}

# 初步处理 POST 的数据
// $ip=file_get_contents('ip.txt');
$ip = $_POST['iplist'];
$ip = str_replace(' ', '', $ip);
$ip = str_replace("\t", "", $ip);
$ip = str_replace("\r", "", $ip);
$ip = str_replace("\n\n", "\n", $ip);
$ip = rtrim($ip, "\n");
$ip = ltrim($ip, "\n");

# 按重复次数重排 IP
$ip_array = explode("\n", $ip);
$ip_new_array = array();
foreach($ip_array as $ipx){
    $n = @substr_count($ip, $ipx);
    $ip_new_array += array($ipx => $n);
}
arsort($ip_new_array);
$na = count($ip_new_array);

# 查询归属地
$ipinfo = "";
foreach($ip_new_array as $key => $value){
    $url138 = 'http://ip138.com/ips138.asp?ip=' . $key . '&action=2';
    $ipaddr138 = get_ipaddr($url138);
    $ipinfo .= "<tr><td>$value</td><td>$key</td><td>$ipaddr138</td></tr>\r\n";
}
$html = "<br><center>本次共查询 $na 个IP，归属地数据来源于IP138<br>\r\n==============================\r\n<br><table>\r\n";
$html .= "<tr><td style='width:80px'>重复次数</td><td style='width:160px'>IP地址</td><td>IP归属地</td></tr>\r\n";
echo "<br><center>\r\n";
echo html_form() . '</center>';
echo $html . $ipinfo . "</table></center><br>\r\n";

function html_form(){
    $form = "<form action=" . php_self() . " method=\"post\" id=\"usrform\">\r\n<input type=\"submit\"></form>\r\n";
    $form .= '<small>请在下框内输入一列IP/域名地址...</small><br><textarea rows="13" cols="35" name="iplist" form="usrform">';
    $form .= "8.8.8.8\r\n8.8.8.8\r\nbaidu.com</textarea>";
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
    $ipaddr = str_replace(array("本站数据：", "参考数据1：", "参考数据2："), ',', $ipaddr);
    return $ipaddr;
}

