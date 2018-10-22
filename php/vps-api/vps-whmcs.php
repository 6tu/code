<?php

$url = 'https://secure.hostsolutions.ro';
$dologin = $url . '/dologin.php';
$clientarea = $url . '/clientarea.php';
$details = $clientarea . '?action=productdetails&id=5246';

$cookie_dir = './cookie/';
if(!is_dir($cookie_dir)) mkdir($cookie_dir,0777);
$cookie_file = $cookie_dir . time() . '.cookie';
setcookie("PHPSESSID", "vc0heoa6lfsi3gger54pkns152");
$token = getResponse($clientarea, [], $cookie_file);
preg_match('/<input type="hidden" name="token" value="(.*)"/U', $token, $match);
// print_r($match);
$post['token'] = $match[1];
$post['username'] = 'admin@liuyun.org';
$post['password'] = 'password';

$login = getResponse($dologin, $post, $cookie_file);
$pd = getResponse($details, $data=[], $cookie_file);

unlink($cookie_file);

# 数据索取完毕

# 删除js
$preg = "/<script[\s\S]*?<\/script>/i";
$html = preg_replace($preg, "", $pd, -1);
unset($pd);

# 删除冗余
$pd_array = explode('<!-- Container for main page display content -->' , $html);
$pd_array1 = explode('<div class="tab-pane fade in" id="tabDownloads">' , $pd_array[1]);
$pd_array2 = explode('</div></div></div>' , $pd_array1[0]);
$pd_array3 = explode('<div>' , $pd_array2[0] . $pd_array2[3] . '</div>'); # 分三个部分，重整理


$pd_array4 = explode('<center>' , $pd_array3[1]);
$pd_array5 = explode('<tr class="orowcolor">' , $pd_array4[3]);
$pd_array6 = explode('<div class="col-sm-10 col-sm-offset-1">' , $pd_array3[2]);
$ut = str_replace(':' , '在线时间 : ' , $pd_array5[3]);
// print_r($pd_array3);
// exit;
$html = $pd_array3[0] . $pd_array6[0] . $ut . $pd_array4[1] . $pd_array6[1];

# 多个空格转为一个空格
$html = preg_replace ("/\s(?=\s)/", "\\1", $html);
$html = str_replace("\t", ' ', $html);
$html = str_replace("\r\n", '', $html);
$html = str_replace("\n", '', $html);
$html = str_replace('<div class="col-sm-7 text-left">', '<p>', $html);
$html = str_replace('要求撤銷', '', $html);
$html = str_replace('其他資訊', '', $html);
$html = str_replace('資源使用情形', '', $html);
$html = (strip_tags($html, '<br><p><h4><b>'));

$html = str_replace(' <', '<', $html);
$html = str_replace('> ', '>', $html);
$html = str_replace('</h4>', ' ', $html);
$html = str_replace('<h4>', '<br>', $html);
$html = str_replace('  ', '<br>', $html);
$html = str_replace('<br />', '<br>', $html);
$html = str_replace('> ', '>', $html);

$html = str_replace('</b><br>', ' ', $html);
$html = str_replace('<br>', "\r\n", $html);
$html = str_replace('</b><br>', ' ', $html);
$html = str_replace('<b>', '', $html);
$html = str_replace('<p>', ' ', $html);
$html = str_replace('</p>', '', $html);
$html = str_replace('<p class="text-muted">', '', $html);
$html = str_replace("\r\n\r\n\r\n", "\r\n", $html);
$html = str_replace("\r\n\r\n", "\r\n", $html);

$html = str_replace("伺服器資訊\r\n", "\r\n<b>伺服器資訊</b>", $html);
$html = str_replace('OS Template', "<b>其他資訊</b>\r\nOS Template ", $html);
$html = str_replace('在线时间', "\r\n<b>資源使用情形</b>\r\n在线时间", $html);
$html = "<b>產品詳情</b>" . $html;

echo "<pre>\r\n\r\n" . $html ;


function getResponse($url, $data = [], $cookie_file = '', $timeout = 3){
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
    unlink($cookie_file);
}
