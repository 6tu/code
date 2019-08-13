<?php
/*
# dnsapi(Hurricane Electric hook script) for php
#
#-- dns_he_add() - Add TXT record

# 用法

# # 将数据写入文件。或者数据库
# $key = 'HE_info';
# create_account($dns, $key);

# $full_domain = 'test.example.com';
# $txt_value = 'L29wdC9sYW1wcC9sYW1wcCByZWxvYWRhcGFjaGUkk';
# $zone_id = dns_he_find_zone($full_domain);
# echo $full_domain .'and'. $txt_value .'will be added to '. $zone_id;
# echo dns_he_add($dns, $full_domain, $txt_value, $zone_id);

*/

# Please set user credentials .
$dns['HE_info']     = "Using DNS-01 Hurricane Electric hook";
$dns['HE_url']      = "https://dns.he.net/";
$dns['HE_username'] = "user";
$dns['HE_password'] = 'password';

# 添加TXT记录
function dns_he_add($dns, $full_domain, $txt_value, $zone_id){
    $data['email']    = $dns['HE_username'];
    $data['pass']     = $dns['HE_password'];
    $data['account']  = '';
    $data['menu']     = 'edit_zone';
    $data['Type']     = 'TXT';
    $data['TTL']      = '300';
    $data['Priority'] = '';
    $data['Name']     = $full_domain;
    $data['Content']  = $txt_value;
    $data['hosted_dns_zoneid']     = $zone_id;
    $data['hosted_dns_recordid']   = '';
    $data['hosted_dns_editzone']   = '1';
    $data['hosted_dns_editrecord'] = 'Submit';

    $response = getResponse($dns['HE_url'], $data, $cookie_file = '');
    $body = $response['body'];
    if(strpos($body, 'Successfull') !== false) $rec = "TXT record added successfully.";
    else $rec = "Couldn't add the TXT record.";
    echo "\r\n<br>". $rec ."\r\n<br>";
    return "\r\n". $rec;
}

# 移除TXT记录
function dns_he_rm($dns, $full_domain, $txt_value, $zone_id){

    # Find the record id to clean
    $info = "Cleaning up after DNS-01 Hurricane Electric hook";
    $url = $dns['HE_url'];
    $data['email'] = $dns['HE_username'];
    $data['pass']  = $dns['HE_password'];
    $data['menu']  = "edit_zone";
    $data['hosted_dns_zoneid']   = $zone_id;
    $data['hosted_dns_editzone'] = "";
    $response = getResponse($url, $data, $cookie_file = '');
    $body = $response['body'];
    unset($response);

    preg_match("'<table(.+)</table>'s", $body, $arr);
    if($arr){  
        $body = $arr["0"];
    }
    unset($arr);
    // echo $txt_value . "<br>\r\n" . $body . "<br>\r\n";

    # 匹配 ID 和 TXT
    if(strpos($body, $txt_value) == false) echo ("\r\n <br> The txt record is not found,just skip. <br> \r\n");

    $arr = explode('<tr', $body);
    $str = '';
    $n = count($arr);
    for($i = 0; $i < $n; $i++){
        if(strpos($arr[$i], $full_domain) == !false && strpos($arr[$i], $txt_value) == !false){
            $str = '<tr' . $arr[$i];
        }
    }
    unset($arr);
    if(empty($str)) die("Can not find $full_domain .");

    $id = substr($str, strpos($str, "dns_tr") + 8, 20);
    $preg = "/\d+/";
    preg_match_all($preg, $id, $arr);
    $record_id = $arr[0][0];
    echo $id . $record_id;
    unset($arr);
    if(empty($record_id)) echo("Can not find record id .");

    # Remove the record
    $data['hosted_dns_editzone']    = "1";
    $data['hosted_dns_recordid']    = $record_id;
    $data['hosted_dns_delrecord']   = "1";
    $data['hosted_dns_delconfirm']  = "delete";
    $response = getResponse($url, $data, $cookie_file = '');
    $body = $response['body'];

    if(strpos($body, 'Successfull') !== false) $rec = "Record removed successfully.";
    else $rec = "Could not clean(remove) up the record. Please go to HE administration interface and clean it by hand.";
    echo $rec;
    return "\r\n" . $rec;
}

# 查找根域名对应的 hosted_dns_zoneid
function dns_he_find_zone($domain, $dns){

    $n = substr_count($domain, '.');
    $arr = explode('.', $domain, $n);
    $root_domain = $arr[$n -1];
    unset($arr);

    $url = $dns['HE_url'];
    $data['email'] = $dns['HE_username'];
    $data['pass'] = $dns['HE_password'];
    $response = getResponse($url, $data, $cookie_file = '');
    $body = $response['body'];
    unset($response);

    if(strpos($body, 'Incorrect') !== false or strpos($body, 'Password') !== false){
        die("Unable to login to dns.he.net please check username and password. <br>\r\n");
    }

    $preg = "/<script[\s\S]*?<\/script>/i";
    $body = preg_replace($preg, "", $body, -1);
    preg_match("'<table(.+)</table>'s", $body, $arr);
    if($arr) $html = $arr["0"]; 
    unset($arr);
    echo "<br>\r\n". $dns['HE_info'] ."<br>\r\n";
    if(strpos($html, $root_domain) !== false){
        echo "<br>\r\n These are the zones on this HE account. <br>\r\n";
        $domain_array = explode($root_domain, $html);
        $html = '';
        $n = count($domain_array);
        for($i = 0; $i < $n; $i++){
            if(strpos($domain_array[$i], 'hosted_dns_zoneid') !== false) $html = $domain_array[$i];
        }
        if(preg_match('|(\d+)|', $html, $r)) return $r[1];
    }else{
        die("Can not get zone names. <br>\r\n");
    }
}

# 将数据写入文件。或者数据库
function create_account($dns, $key){
    if(!file_exists('account.conf.php')){
        file_put_contents('account.conf.php', "<?php\r\n\r\n");
        $dns_str = "\$dns_json = '" . json_encode($dns) . "';\r\n";
        file_put_contents('account.conf.php', $dns_str, FILE_APPEND);
    }else{
        include('account.conf.php');
        if(strpos($dns_json, $key) == false){
            $dns_str = "\$dns_json .= '" . json_encode($dns) . "';\r\n";
            file_put_contents('account.conf.php', $dns_str, FILE_APPEND);
        }
    }
}

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
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);       # 302 重定向
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);          # 301 重定向
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);    # 取cookie的参数是
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);   # 发送cookie

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
