<?php

date_default_timezone_set("America/Los_Angeles"); # 洛杉矶时间
$t = date('ymdH', time());
$log_dir = './log/';
if(!is_dir($log_dir)) mkdir($log_dir, 0777);

$cookie_dir = './cookie/';
if(!is_dir($cookie_dir)) mkdir($cookie_dir, 0777);
$cookie_file = $cookie_dir . time() . '.cookie';
setcookie("PHPSESSID", "vc0heoa6lfsi3gger54pkns152");

# 由GET变量传递的文件名和URL
if(isset($_GET['url']) and strstr($_GET['url'], 'http')){
    $url = $_GET['url'];
    $array_url = parse_url(trim($_GET['url']));
    $fn = substr(@$array_url['path'], strrpos(@$array_url['path'], "/") + 1);
}else{
    $url = 'https://www.soundofhope.org/gb/category/%E5%8F%A4%E4%BB%8A%E6%96%87%E5%8C%96/%E5%8D%83%E5%8F%A4%E8%8B%B1%E9%9B%84%E4%BA%BA%E7%89%A9/%E5%A6%82%E6%97%A5%E5%A6%82%E4%BA%91-%E6%98%AD%E6%98%AD%E5%9C%A3%E5%90%9B-%E5%B8%9D%E5%B0%A7%E7%9A%84%E6%95%85%E4%BA%8B';
    $fn = '帝尧的故事.html';
}


if(file_exists($fn) and strpos($fn, '.mp3') == false){
    echo file_get_contents($fn);
    exit;
}

if(file_exists($fn) and strpos($fn, '.mp3') !== false){
    $audio =  '<br><br><center><audio controls="controls">' . "\r\n";
    $audio .= '<source src="' . $fn . '" type="audio/mpeg">' . "\r\n";
    $audio .= 'Your browser does not support the audio tag.' . "\r\n";
    $audio .= '</audio></center>';
    echo $audio;
    exit;
}

if(strpos($fn, '.mp3') !== false){
    $res_array = getResponse($url);
    $html = $res_array['body'];
    file_put_contents($fn, $html);
    exit;
}

$res_array = getResponse($url, [], $cookie_file);
// unlink($cookie_file);
# echo '<pre>';
# print_r($res_array);
$html = $res_array['body'];

# 删除 js 标签及相关内容
$preg = "/<script[\s\S]*?<\/script>/i";
$html = preg_replace($preg, "", $html, -1);
$html = preg_replace('#<script[^>]*?>.*?<\/script\s*>#si', '', $html);

# 删除 ul 标签及相关内容
$preg = "/<ul[\s\S]*?<\/ul>/i";
$html = preg_replace($preg, "", $html, -1);
$html = preg_replace('#<script[^>]*?>.*?<\/script\s*>#si', '', $html);

# 提取 title 并制作 head
preg_match("|<title>([^^]*?)</title>|u", $html, $matches);
$title = $matches[1];

$head = '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8">';
$head .= '<title>' . $title . '</title></head><body>';
$head = str_replace(">", ">\r\n", $head);

# 提取 article 内容
$array_body = explode('<h1 class="title">', $html);
$array_article = explode('</p></div>', '<h1 class="title">' . $array_body[1]);
$body = $array_article[0] . "</p></div><br>\r\n\r\n</body></html>\r\n\r\n";

unset($res_array);
unset($array_body);
unset($array_article);

# 提取 mp3 超链接
$pattern = '/data-url(.*?)\.mp3/i';
preg_match_all($pattern, $body, $mp3);
$k16  = substr($mp3[0][0], strripos($mp3[0][0], "'") + 1);
$k128 = substr($mp3[0][1], strripos($mp3[0][1], "'") + 1);

# 多个空格转为一个空格
$body = preg_replace ("/\s(?=\s)/", "\\1", $body);
$body = strip_space_enter($body, '<h1><p><span><a><body><html><br>', $n = 10);

$body = str_replace("</p>", "</p>\r\n", $body);
$body = str_replace("h1", "h4", $body);

$body = str_replace("</a>16K", "16K</a>\r\n", $body);
$body = str_replace("128K<p>", '<a href="' . $k128 . "\">128K</a><p>\r\n", $body);
$body = str_replace("http", "?url=http", $body);

$html = $head . $body;
echo beautify_html($html);
file_put_contents($fn, $html);



# HTML 格式化
function beautify_html($html){
    $tidy_config = array(
        'clean' => false,
        'indent' => true,
        'indent-spaces' => 4,
        'output-xhtml' => false,
        'show-body-only' => false,
        'wrap' => 0
        );
    if(function_exists('tidy_parse_string')){ 
        $tidy = tidy_parse_string($html, $tidy_config, 'utf8');
        $tidy -> cleanRepair();
        return $tidy;
    }
    else return $html;
}

# 删除空格和回车
function strip_space_enter($html, $tags, $n){
	$html = strip_tags($html, $tags);
	$html = trim($html);
    for($i = 0; $i < $n; $i++){
        $html = str_replace(array(" \r\n", " \n", ' <', '> '), array("\r\n", "\n", '<', '>'), $html);
    }
    $html = str_replace(array("\r\n", "\n", "\r", '&nbsp;', "\t",), array(''), $html);
	return $html;
}

# 使用CURL获取网页内容，报头，状态码，mime类型和编码 charset
# CURLOPT_CONNECTTIMEOUT 请求连接超时
# CURLOPT_TIMEOUT 响应数据传输时允许时间
# 支持GET和POST,返回值网页内容，报头，状态码，mime类型和编码 charset
function getResponse($url, $data = [], $cookie_file = ''){

    $url_array = parse_url($url);
    $host = $url_array['scheme'] . '://' . $url_array['host'];
    if(!empty($_SERVER['HTTP_REFERER'])) $refer = $_SERVER['HTTP_REFERER'];
    else $refer = $host . '/';
    if(!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    else $lang = 'zh-CN,zh;q=0.9';
    if(!empty($_SERVER['HTTP_USER_AGENT'])) $agent = $_SERVER['HTTP_USER_AGENT'];
    else $agent = 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.67 Safari/537.36';
    // $agent = 'Wget/1.18 (mingw32)'; # 'Wget/1.17.1 (linux-gnu)';
    // echo "<pre>\r\n" . $agent . "\r\n" . $refer . "\r\n" . $lang . "\r\n\r\n";
	
    if(empty($cookie_file)){
        $cookie_file = '.cookie';
    }
	
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    curl_setopt($ch, CURLOPT_REFERER, $refer);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept-Language: " . $lang));
    if(!empty($data)){
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);   # 302 重定向
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);      # 301 重定向

    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);  # 取cookie的参数是
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file); # 发送cookie
	
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
	
	# try{}catch{}语句
    // try{
    //     $handles = curl_exec($ch);
    //     curl_close($ch);
    //     return $handles;
    // }
    // catch(Exception $e){
    //     echo 'Caught exception:', $e -> getMessage(), "\n";
    // }
    // unlink($cookie_file);

    $res_array = explode("\r\n\r\n", $result, 2);
    $headers = explode("\r\n", $res_array[0]);
    $status = explode(' ', $headers[0]);
    # 如果$headers为空，则连接超时
    if(empty($res_array[0])) die('<br><br><center><b>连接超时</b></center>');
    # 如果$headers状态码为404，则自定义输出页面。
    if($status[1] == '404') die("<pre><b>找不到，The requested URL was not found on this server.</b>\r\n\r\n$res_array[0]</pre>\r\n\r\n");
    # 如果$headers第一行没有200，则连接异常。
    # if($status[1] !== '200') die("<pre><b>连接异常，状态码： $status[1]</b>\r\n\r\n$res_array[0]</pre>\r\n\r\n");\

    if($status[1] !== '200'){
        $body_array = explode("\r\n\r\n", $res_array[1], 2);
        $header_all = $res_array[0] . "\r\n\r\n" . $body_array[0];
        $res_array[0] = $body_array[0];
        $body = $body_array[1];
    }else{
        $header_all = $res_array[0];
        $body = $res_array[1];
    }

    $headers = explode("\r\n", $res_array[0]);
    $status = explode(' ', $headers[0]);
    
    $headers[0] = str_replace('HTTP/1.1', 'HTTP/1.1:', $headers[0]);
    foreach($headers as $header){
        if(stripos(strtolower($header), 'content-type:') !== FALSE){
            $headerParts = explode(' ', $header);
            $mime_type = trim(strtolower($headerParts[1]));
            //if(!empty($headerParts[2])){
            //    $charset_array = explode('=', $headerParts[2]);
            //    $charset = trim(strtolower($charset_array[1]));
            //}
        }
        if(stripos(strtolower($header), 'charset') !== FALSE){
            $charset_array = explode('charset=', $header);
            $charset = trim(strtolower($charset_array[1]));
        }else{
            $charset = preg_match("/<meta.+?charset=[^\w]?([-\w]+)/i", $res_array[1], $temp) ? strtolower($temp[1]):"";
        }
    }
    if(empty($charset)) $charset = 'utf-8';
    if(strstr($charset, ';')){
        $charset_array = '';
        $charset_array = explode(';', $charset);
        $charset = trim($charset_array[0]);
        //$charset = str_replace(';', '', $charset);
    }
    if(strstr($mime_type, 'text/html') and $charset !== 'utf-8'){
        $body = mb_convert_encoding ($body, 'utf-8', $charset);
    }
    # $body = preg_replace('/(?s)<meta http-equiv="Expires"[^>]*>/i', '', $body);    
    
    # echo "<pre>\r\n$header_all\r\n\r\n" . "$status[1]\r\n$mime_type\r\n$charset\r\n\r\n";
    # header($res_array[0]);

    $res_array = array();
    $res_array['header']    = $header_all;
    $res_array['status']    = $status[1];
    $res_array['mime_type'] = $mime_type;
    $res_array['charset']   = $charset;
    $res_array['body']      = $body;
    return $res_array;
}

