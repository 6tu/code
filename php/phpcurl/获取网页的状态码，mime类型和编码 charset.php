<?php
# 获取网页的状态码，mime类型和编码 charset

$url = 'http://ysuo.org';
$res_array = GetPage($url);

if(empty($res_array[0])) die('<br><br><center><b>连接超时</b></center>');
$headers = explode("\r\n", $res_array[0]);
$status = explode(' ', $headers[0]);
$headers[0] = str_replace('HTTP/1.1', 'HTTP/1.1:', $headers[0]);
foreach($headers as $header){
    if(stripos(strtolower($header), 'content-type:') !== FALSE){
        $headerParts = explode(' ', $header);
        $mime_type = trim(strtolower($headerParts[1]));
        if(!empty($headerParts[2])){
            $charset_array = explode('=', $headerParts[2]);
            $charset = trim(strtolower($charset_array[1]));
        }else{
            $charset = preg_match("/<meta.+?charset=[^\w]?([-\w]+)/i",$res_array[1],$temp) ? strtolower($temp[1]):"";
        }
    }
}
# 如果$headers为空，则连接时。如果$headers第一行没有200，则连接异常。
if($status[1] !== '200') die("连接异常，状态码： $status[1]\r\n$res_array[0]\r\n\r\n");
// echo "<pre>\r\n$res_array[0]\r\n\r\n" . "$status[1]\r\n$mime_type\r\n$charset\r\n\r\n";
// header($res_array[0]);
if(strstr($mime_type,'text/html')){
    $body = mb_convert_encoding ($res_array[1],'utf-8',$charset);
}
# $body = preg_replace('/(?s)<meta http-equiv="Expires"[^>]*>/i', '', $body);
echo $body;

# 使用CURL取得URL内容，包括头信息
# 简单的理解为 请求连接超时CURLOPT_CONNECTTIMEOUT
# 响应数据传输时允许时间CURLOPT_TIMEOUT
function GetPage($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,4);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    $res_array = explode("\r\n\r\n", $result, 2);
    return $res_array;
}

