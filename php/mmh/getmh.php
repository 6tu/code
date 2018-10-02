<?php
/**
 *
 * 对比文件的sha512之后更新文件
 * 文件末端附有定时执行 shell 脚本mmh-cron.sh,每小时执行一次
 * 
 */
set_time_limit(0);
error_reporting(1);
// echo date_default_timezone_get() . "\r\n<br>";

if(!empty($_GET['date']) and strstr($_GET['date'], 'date')){

    // echo highlight_file("date.php");

    // echo date('Ymd-His', time());
    // echo date("Y-n-j  H:i:s", time()) . "<br>\r\n";
    // date_default_timezone_set('Asia/Shanghai');        #北京时间
    // date_default_timezone_set ('America/New_York');    #美东时间
    // date_default_timezone_set ("America/Los_Angeles"); # 美西时间
    // date_default_timezone_set ("America/Chicago");
    // date_default_timezone_set ("America/Phoenix");
    // date_default_timezone_set ("America/Anchorage");
    // date_default_timezone_set ("America/Adak");
    // date_default_timezone_set ("Pacific/Honolulu");
    // date_default_timezone_set ("America/Denver");

    date_default_timezone_set ("Etc/GMT+6");             #比林威治标准时间慢6小时
    echo date("Y-n-j", time());
    // echo "<br>\r\n";

    // $hashed = md5(uniqid(microtime(true),true));
    // file_put_contents("log.txt",  date('Ymd-His', time())." ".$hashed.PHP_EOL, FILE_APPEND);

    exit(0);
}

date_default_timezone_set('America/New_York');

$y = date("Y", time());
$m = date("n", time());
$d = date("j", time());
$d = 32;
$lm = $m-1;
if($lm === 0){
    $y = $y-1;
    $lm = 12;
}
$lm0 = $lm;
if ($lm0 < 10) $lm0 = '0'.$lm0;

$monthday = cal_days_in_month(CAL_GREGORIAN, $lm, $y);

# 定义目录
$mhdata = '/var/www/mmh/mhdata/';
$a = 'archives';
$a_path = $mhdata . $a;
$date_path = $a_path . '/' . $y . $lm0;
$log_path = $date_path . '/log-update';
if(!file_exists($log_path)) mkdir($log_path, 0777, true);
$alog = $a . '_' . $y . $lm0 . '.log';

if(($d === 2) and !file_exists($a_path.'/'.$alog) and empty($_GET['name'])){
    
    $fnkey = '';
    for($day = 1; $day < $monthday + 1; $day++){
		
        # 定义文件名
        $afn = $y . '-' . $lm . '-' . $day . '-t.zip';
        $sha512 = $afn . '.sha512';
        $update = $afn . '_update.log';
        $p7mzip = 'p7m_' . $afn . '.b64.zip';

        $locurl = $_SERVER['REQUEST_SCHEME'] . '://' .  $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '?name=' . $afn;
        //echo $arch_url;
        $a_array = GetPage($locurl);
    
        $akey = file_get_contents($mhdata. $sha512);
        $fnkey .= $afn . '  ' . $akey . "\r\n\r\n";
        
        if(file_exists($mhdata.$sha512)) rename($mhdata.$sha512, $date_path . '/' . $sha512);
        
        
        if(file_exists($mhdata.$p7mzip)) rename($mhdata.$p7mzip, $date_path . '/' . $p7mzip);
        
        if(file_exists($mhdata.'log/'.$update)) rename($mhdata.'log/'.$update, $log_path . '/' . $update);
    }
    file_put_contents($a_path.'/'.$alog, $fnkey);

    echo 'done';
    exit(0);
}



# 用浏览器访问
if(empty($_GET['name']) and !strstr($_SERVER['HTTP_USER_AGENT'], 'Wget')){
    form_html();
    exit(0);
}




/** 远端参数 **/

# 发送到 E-mail
$phpmailer = false;                   # true为发送,false为不发送
$to = 'safeboat@126.com';
$mailpara = array(
    'smtphost' => 'smtp.126.com',     # 指定主和备份SMTP服务器
    'username' => 'safeboat@126.com', # SMTP用户名
    'password' => 'qq0000000',        # SMTP密码
    'smtpsecure' => 'ssl',            # 接受`ssl`和`tls`加密
    'smtpport' => 994,                # SMTP服务器TCP端口
    );

# 上传到专用远端
$up2remote = false;                   # true为发送,false为不发送
$up2remotepara = 'file';
$up2remoteurl = "http://hk.6tu.me/mmh/index.php";

# 上传到对象服务器
$up2qiniu = true;                     # true为发送,false为不发送
$qiniupara = array(
    'qiniupath' => __DIR__ . '/qiniu/upload_to_qiniu.php',
    'qiniuurl' => 'http://oold3s5tj.bkt.clouddn.com/',
    'accessKey' => 'KzBWtGa-Qsxd2zA_SbYkcxi9Evw0fRNgQY5ax9T6',
    'secretKey' => 'F8JQ4riqVfQmgCyDWya9Oi5TYBtNpOKBToYUxEyh',
    'bucket' => 'yisuo',
    );

/** 设置文件名和URL **/

$mh = base64_decode('aHR0cDovL3d3dy5taW5naHVpLm9yZy8=') . 'mh/articles';

$mhdir = 'mhdata/';  # 文件存储路径

# 默认的文件名和URL
$fn = date("Y-n-j", time()) . '-t.zip';
// $fn_array = explode('-', $fn);
// $time1 = $fn_array[0] . '/' . $fn_array[1] . '/' . $fn_array[2];
$time1 = date("Y/n/j", time());
$mhurl = $mh . '/' . $time1 . '/' . $fn;

# 由GET变量传递的文件名和URL
if(isset($_GET['name']) and !strstr($_GET['name'], '/')){
    $fn = $_GET['name'];
    $basename = substr($fn, 0, strrpos($fn, '.')); #文件名除去后缀
    $fn_array = explode('-', $basename);
    $time1 = $fn_array[0] . '/' . $fn_array[1] . '/' . $fn_array[2];
    $link = '/' . $time1 . '/' . $_GET['name'];
    $mhurl = $mh . '/' . $time1 . '/' . $_GET['name'];
}else if(isset($_GET['name']) and strstr($_GET['name'], 'http')){
    $url = parse_url(trim($_GET['name']));
    $fn = substr(@$url['path'], strrpos(@$url['path'], "/") + 1);
    $link = $url;
    if($fn == ""){
        $fn = "index.html";
    }
    $mhurl = $_GET['name'];
}

# 由 $fn 设置相关的文件名
$fn_hashed = $mhdir . $fn . '.sha512';
$fn_update = $mhdir . 'log/' . $fn . '_update.log';

$fn_b64 = $fn . '.b64';
$p7m_fn = 'p7m_' . $fn_b64 . '.eml';
$p7m_zip = $mhdir . 'p7m_' . $fn_b64 . '.zip';
// echo $mhurl . '==>' . $p7m_zip . "<br>\r\n";
$filePath = dirname(__FILE__) . '/' . $p7m_zip; # 要上传文件的本地路径

/** 远端参数远端参数建立、更新文件 **/

# 获取数据并校验
$res_array = GetPage($mhurl);
$body = $res_array['body'];

# 通过buffer函数读取二进制流内容
$file_type = $res_array['mime_type'];
$finfo = new finfo(FILEINFO_MIME_TYPE);
$file_type = $finfo -> buffer($body);
if(!strpos($file_type, 'zip')){
    header("Content-type: text/html; charset=GBK");
    $error = '获取数据无效, 当前数据类型是 ' . $file_type;
    echo iconv("UTF-8", "gbk//TRANSLIT", $error);
    exit(1);
}
$hashed = hash('sha512', $body);
$hashedlog = date('Ymd-His', time()) . '  ' . $hashed . "\r\n";

# 判断文件是否存在
if(file_exists($fn_hashed)){
    # echo "The file $fn exists";
    $asc = file_get_contents($fn_hashed);
}else{
    # echo "The file $fn does not exist";
    $asc = '';
}

# log文件表头table headers
if(!file_exists($fn_update)){
    # echo "The file $fn does not exists";
    $th = 'time[' . date_default_timezone_get() . ']    key[sha512]' . "\r\n=================\r\n";
    file_put_contents($fn_update, $th);
}

# 对比值之后，更新文件
if(strcmp($asc, $hashed) !== 0){
    form_html();
    
    # 建立相关文件
    file_put_contents($fn_hashed, $hashed);
    file_put_contents($fn_update, $hashedlog, FILE_APPEND | LOCK_EX);
    file_put_contents($fn_b64, chunk_split(base64_encode($body)), LOCK_EX);
    echo "$link save to $fn_b64 <br>\r\n";
    pkcs7_encrypt($fn_b64);      # 这里建立 $p7m_fn文件
    compress($p7m_fn, $p7m_zip); # 把 $p7m_fn文件压缩到指定的目录
    @unlink($fn_b64);
    @unlink($p7m_fn);

    # 构造并发送Email，本例中仅被保留
    // $ziptype = 'application/x-zip-compressed';
    // $zipdata = file_get_contents($p7m_zip);
    // $zipdata = chunk_split(base64_encode($zipdata));
    // $mhdata = "Content-Type: {$ziptype};name=\"{$zip}\"\n" . "Content-Transfer-Encoding: base64\n\n" . $zipdata . "\n\n" ;
    // mail($to, $p7m_zip, $mhdata);
    
    # 发送文件到远端
    if($phpmailer) smtp_mail($to, $filePath, $mailpara);
    if($up2remote) postfiles($up2remoteurl, $up2remotepara, $filePath);
    if($up2qiniu) upload2qiniu($filePath, $p7m_zip, $qiniupara);
    echo "<br>\r\n File upload succeeded <br>\r\n";
    echo "</body>\r\n</html>";
}else{
    # if(!file_exists($fn)) file_put_contents($fn, $body, LOCK_EX);
    form_html();
    echo '<a href="' . $p7m_zip . '">' . $p7m_zip . "</a>    No need to update<br>\r\n";
    echo "</body>\r\n</html>";
}









# ================ 函数区，基本无需修改 ================#

function form_html(){
    #$time1 = date("/Y/n/j/", time()); 
    $time2 = date("Y-n-j", time());
    $fn = $time2 . '-t.zip';
    //header("Content-type: text/html; charset=utf-8");
    $html = "<html><head><title>GET NEWS</title></head>\r\n<body><center><br>\r\n<form action=\"" . php_self() . "\" method='GET' />\r\n";
    $html .= '<b>PUT FILE NAME : <input type="text" name="name" size=50 value="'.$fn.'" />'."\r\n".'<input type="submit" value="Send" />';
    $html .= "</b>\r\n</form>\r\n<br>\r\n";
    echo $html;
}

# 上传文件到七牛云对象存储服务器
function upload2qiniu($filePath, $key, $qiniupara){
    $reqpath   = $qiniupara['qiniupath'];
    $puburl    = $qiniupara['qiniuurl'];
    $accessKey = $qiniupara['accessKey'];
    $secretKey = $qiniupara['secretKey'];
    $bucket    = $qiniupara['bucket'];

    require_once $reqpath;
    // $filePath                              #本地文件
    // $key = $p7m_zip;                       #上传到七牛后保存的文件名

    if($err !== null){
        var_dump($err);
    }else{
        // var_dump($ret);
        echo 'Successfully uploaded to <a href="' . $puburl . $ret['key'] . '">qiniu</a>';
    }
}

# 返回值网页内容，报头，状态码，mime类型和编码 charset
function GetPage($url){
    
    if(!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    else $lang = 'zh-CN,zh;q=0.9';
    if(!empty($_SERVER['HTTP_REFERER'])) $refer = $_SERVER['HTTP_REFERER'];
    else $refer = 'https://www.google.com/?gws_rd=ssl';
    
    if(!empty($_SERVER['HTTP_USER_AGENT'])) $agent = $_SERVER['HTTP_USER_AGENT'];
    else $agent = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36';
    
    # $agent = 'Wget/1.18 (mingw32)';
    # $agent = 'Wget/1.17.1 (linux-gnu)';
    
    
    # echo "<pre>\r\n" . $agent . "\r\n" . $refer . "\r\n" . $lang . "\r\n\r\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, $agent);
    curl_setopt($ch, CURLOPT_REFERER, $refer);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept-Language: " . $lang));
    
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);   // follow redirects
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);      // set referer on redirect

    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    
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

# 上传文件到远端
function postfiles($url, $para, $file){
    header('content-type:text/html;charset=utf8');
    $data[$para] = new CurlFile($file);
    
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_POST,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
    $result = curl_exec($ch);
    curl_close($ch);
    $result = explode('<br><br>', $result);
    
    echo $result[1] ."\r\n<br>";
}

# 发送邮件函数
function smtp_mail($to, $filePath, $mailpara){
    # 导入PHPMALLER类到全局命名空间中
    # 这些必须位于脚本的顶部，而不是函数内
    $attachment = $filePath;
    $filename = substr($filePath, strrpos($filePath,$find)+1);
    $path_class = getcwd() . '/phpmailer/';
    require_once $path_class . "class.phpmailer.php";
    require_once $path_class . "class.smtp.php";
    $mail = new PHPMailer(true);                              #设置`true`启用例外规则
    try{
        $mail -> setLanguage('zh_cn', $path_class . 'language/');
        # 服务器设置
        $mail -> SMTPDebug = 0;                               #设置2为输出详细调试信息
        $mail -> isSMTP();                                    #使用远程SMTP，而非本地sendmail
        $mail -> SMTPAuth = true;                             #EnableSMTP认证
        $mail -> Host = $mailpara['smtphost'];                #指定主和备份SMTP服务器
        $mail -> Username = $mailpara['username'];            #SMTP用户名
        $mail -> Password = $mailpara['password'];            #SMTP密码
        $mail -> SMTPSecure = $mailpara['smtpsecure'];        #接受`ssl`和`tls`加密,
        $mail -> Port = $mailpara['smtpport'];                #SMTP服务器TCP端口
        # 邮件的发送和接受者
        $mail -> setFrom($mailpara['username'], 'SendNews');     #发件地址
        $mail -> addAddress($to, 'info');                        #收件地址
        # $mail->addAddress($mailpara['username']);              #名称非必需
        $mail -> addReplyTo($mailpara['username'], 'SendNews');  #回复地址
        # $mail->addCC('cc@example.com');
        # $mail->addBCC('bcc@example.com');
        # 邮件内容
        $mail -> CharSet = 'utf-8';
        $mail -> Encoding = 'quoted-printable';
        $mail -> isHTML(true);                                # 发送HTML格式邮件
        $mail -> Subject = '今日看点';
        $mail -> Body = ' 时刻关注 <b>今日看点!</b>';
        $mail -> AltBody = 'This is the body in plain text for non-HTML mail clients';
        # 附件
        $mail -> addAttachment($attachment);                  #添加附件
        # $mail->addAttachment('/tmp/image.jpg','new.jpg');   #重命名文件名，可选
        $mail -> send();
        echo '<a href="' . $filename . '">' . $filename . "</a> has been sent <br>\r\n";
    }
    catch(Exception$e){
        echo 'Message could not be sent. Mailer Error:', $mail -> ErrorInfo;
    }
}

# if(!function_exists('ereg')){
function ereg($pattern, $subject, & $matches = []){
    return preg_match('/' . $pattern . '/', $subject, $matches);
}
# }

# 获取当前PHP文件名
function php_self(){
    $php_self = substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/') + 1);
    return $php_self;
}

# 加密函数h
function pkcs7_encrypt($fn){
    $headers = array("To" => "info@yourshell.info",
        "From" => "webmaster <postmaster@yourshell.info>",
        "Reply-to" => "support@example.com",
        "Subject" => "Test",
        "Date" => date("r"),
        "X-Mailer" => "By news (PHP/" . phpversion() . ")");
    $cwd = getcwd();
    # $cwd = $_SERVER['DOCUMENT_ROOT'];
    $cert = '
-----BEGIN CERTIFICATE-----
MIIENDCCA52gAwIBAgICAUMwDQYJKoZIhvcNAQEFBQAwgdUxNDAyBgNVBAMTK1Jh
bmdlcnMgUGVyc29uYWwgRnJlZSBDZXJ0aWZpY2F0ZSBBdXRob3JpdHkxGjAYBgkq
hkiG9w0BCQEWC2NlcnRAUlBGLkNBMSAwHgYDVQQKExdSYW5nZXJzIE5ldHdvcmtz
IENvLkx0ZDEXMBUGA1UECxMOUEhQIExhYm9yYXRvcnkxETAPBgNVBAcTCFlpbmNo
dWFuMSYwJAYDVQQIEx1OaW5neGlhIEh1aSBBdXRvbm9tb3VzIFJlZ2lvbjELMAkG
A1UEBhMCQ04wHhcNMTAwNzAzMDQwOTMyWhcNMTEwNzAzMDQwOTMyWjCBrTEiMCAG
CSqGSIb3DQEJARYTaW5mb0B5b3Vyc2hlbGwuaW5mbzENMAsGA1UEAxMEd2FsazEL
MAkGA1UEBhMCQ04xJjAkBgNVBAgTHU5pbmd4aWEgSHVpIEF1dG9ub21vdXMgUmVn
aW9uMREwDwYDVQQHEwhZaW5jaHVhbjEgMB4GA1UEChMXUmFuZ2VycyBOZXR3b3Jr
cyBDby5MdGQxDjAMBgNVBAsTBVN0YWZmMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCB
iQKBgQC1z3t8kIIjLYoYlIma0qPJ8sFJZXRdwUo6qATQilkXuUrQEttRJ/EgzDIn
8L7IcfUjxHYAEJPyckEaKosGlNvo3FjJ+XEtVFCjLFqN0FrE7kpq+6FA5bFXLMuq
B5i8FOzVPMnVIr+6n/WGeE+rRGIUTUuNcELJRT9SBbjtsXPQtQIDAQABo4IBNzCC
ATMwHQYDVR0OBBYEFAQI/LaNrga160Q+aNeMeLgDR6oyMIIBAgYDVR0jBIH6MIH3
gBQgFsVoaeBCqmN69Y61GkI4MgbgdqGB26SB2DCB1TE0MDIGA1UEAxMrUmFuZ2Vy
cyBQZXJzb25hbCBGcmVlIENlcnRpZmljYXRlIEF1dGhvcml0eTEaMBgGCSqGSIb3
DQEJARYLY2VydEBSUEYuQ0ExIDAeBgNVBAoTF1JhbmdlcnMgTmV0d29ya3MgQ28u
THRkMRcwFQYDVQQLEw5QSFAgTGFib3JhdG9yeTERMA8GA1UEBxMIWWluY2h1YW4x
JjAkBgNVBAgTHU5pbmd4aWEgSHVpIEF1dG9ub21vdXMgUmVnaW9uMQswCQYDVQQG
EwJDToIBATAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBBQUAA4GBAKMKq9zCJ2Qx
vJ0KAe2yogzFJLCy3GCK0URcHBedGi3Emfwngm7LQoHF/PQg8BmJ3entJDMvYPAs
Rwin+2biKiz/kcrGIsOs2rfgl1ubxZi/fFf+aNbJvhDKKBvXozUZMQQp7+kPbC8u
x7W+ZnmjO8yJXTdYfwmQxv2SOulDZtoe
-----END CERTIFICATE-----';
    $source = $cwd . '/' . $fn;
    $enc = $cwd . '/p7m_' . $fn . '.eml';
    openssl_pkcs7_encrypt($source, $enc, $cert, $headers); #   $headers0 替换 null2018-6-1.html
    echo "pkcs7_encrypt Create p7m_$fn.eml' success <br>\r\n";
    # unlink($enc);
}

# 压缩函数
function compress($txtname, $zipname){
    if (file_exists($zipname)) unlink($zipname);
    if(false !== function_exists("zip_open")){
        $zip = new ZipArchive();
        if ($zip -> open($zipname, ZIPARCHIVE :: CREATE) !== TRUE){
            exit("cannot open <$zipname>\n");
        }
        $zip -> addFile($txtname);
        $zip -> close();
    }else{
        # include('zip.class.php');
        $test = new zip_file($zipname);
        $test -> add_files(array($txtname));
        $test -> create_archive();
    }
    echo "Create a compressed file $zipname success <br>\r\n";
}

# 压缩和解压缩的类 archive
/**
* --------------------------------------------------
* | TAR/GZIP/BZIP2/ZIP ARCHIVE CLASSES 2.1
* | By Devin Doucette
* | Copyright (c) 2005 Devin Doucette
* | Email: darksnoopy@shaw.ca
* +--------------------------------------------------
* | Email bugs/suggestions to darksnoopy@shaw.ca
* +--------------------------------------------------
* | This script has been created and released under
* | the GNU GPL and is free to use and redistribute
* | only if this copyright statement is not removed
* +--------------------------------------------------
*/
class archive
{
    function archive($name)
    {
        $this->options = array('basedir' => ".", 'name' => $name, 'prepend' => "", 'inmemory' => 0, 'overwrite' => 0, 'recurse' => 1, 'storepaths' => 1, 'followlinks' => 0, 'level' => 3, 'method' => 1, 'sfx' => "", 'type' => "", 'comment' => "");
        $this->files = array();
        $this->exclude = array();
        $this->storeonly = array();
        $this->error = array();
    }
    function set_options($options)
    {
        foreach ($options as $key => $value) {
            $this->options[$key] = $value;
        }
        if (!empty($this->options['basedir'])) {
            $this->options['basedir'] = str_replace("\\", "/", $this->options['basedir']);
            $this->options['basedir'] = preg_replace("/\\/+/", "/", $this->options['basedir']);
            $this->options['basedir'] = preg_replace("/\\/\$/", "", $this->options['basedir']);
        }
        if (!empty($this->options['name'])) {
            $this->options['name'] = str_replace("\\", "/", $this->options['name']);
            $this->options['name'] = preg_replace("/\\/+/", "/", $this->options['name']);
        }
        if (!empty($this->options['prepend'])) {
            $this->options['prepend'] = str_replace("\\", "/", $this->options['prepend']);
            $this->options['prepend'] = preg_replace("/^(\\.*\\/+)+/", "", $this->options['prepend']);
            $this->options['prepend'] = preg_replace("/\\/+/", "/", $this->options['prepend']);
            $this->options['prepend'] = preg_replace("/\\/\$/", "", $this->options['prepend']) . "/";
        }
    }
    function create_archive()
    {
        $this->make_list();
        if ($this->options['inmemory'] == 0) {
            $pwd = getcwd();
            chdir($this->options['basedir']);
            if ($this->options['overwrite'] == 0 && file_exists($this->options['name'] . ($this->options['type'] == "gzip" || $this->options['type'] == "bzip" ? ".tmp" : ""))) {
                $this->error[] = "File {$this->options['name']} already exists.";
                chdir($pwd);
                return 0;
            } else {
                if ($this->archive = @fopen($this->options['name'] . ($this->options['type'] == "gzip" || $this->options['type'] == "bzip" ? ".tmp" : ""), "wb+")) {
                    chdir($pwd);
                } else {
                    $this->error[] = "Could not open {$this->options['name']} for writing.";
                    chdir($pwd);
                    return 0;
                }
            }
        } else {
            $this->archive = "";
        }
        switch ($this->options['type']) {
            case "zip":
                if (!$this->create_zip()) {
                    $this->error[] = "Could not create zip file.";
                    return 0;
                }
                break;
            case "bzip":
                if (!$this->create_tar()) {
                    $this->error[] = "Could not create tar file.";
                    return 0;
                }
                if (!$this->create_bzip()) {
                    $this->error[] = "Could not create bzip2 file.";
                    return 0;
                }
                break;
            case "gzip":
                if (!$this->create_tar()) {
                    $this->error[] = "Could not create tar file.";
                    return 0;
                }
                if (!$this->create_gzip()) {
                    $this->error[] = "Could not create gzip file.";
                    return 0;
                }
                break;
            case "tar":
                if (!$this->create_tar()) {
                    $this->error[] = "Could not create tar file.";
                    return 0;
                }
        }
        if ($this->options['inmemory'] == 0) {
            fclose($this->archive);
            if ($this->options['type'] == "gzip" || $this->options['type'] == "bzip") {
                unlink($this->options['basedir'] . "/" . $this->options['name'] . ".tmp");
            }
        }
    }
    function add_data($data)
    {
        if ($this->options['inmemory'] == 0) {
            fwrite($this->archive, $data);
        } else {
            $this->archive .= $data;
        }
    }
    function make_list()
    {
        if (!empty($this->exclude)) {
            foreach ($this->files as $key => $value) {
                foreach ($this->exclude as $current) {
                    if ($value['name'] == $current['name']) {
                        unset($this->files[$key]);
                    }
                }
            }
        }
        if (!empty($this->storeonly)) {
            foreach ($this->files as $key => $value) {
                foreach ($this->storeonly as $current) {
                    if ($value['name'] == $current['name']) {
                        $this->files[$key]['method'] = 0;
                    }
                }
            }
        }
        unset($this->exclude, $this->storeonly);
    }
    function add_files($list)
    {
        $temp = $this->list_files($list);
        foreach ($temp as $current) {
            $this->files[] = $current;
        }
    }
    function exclude_files($list)
    {
        $temp = $this->list_files($list);
        foreach ($temp as $current) {
            $this->exclude[] = $current;
        }
    }
    function store_files($list)
    {
        $temp = $this->list_files($list);
        foreach ($temp as $current) {
            $this->storeonly[] = $current;
        }
    }
    function list_files($list)
    {
        if (!is_array($list)) {
            $temp = $list;
            $list = array($temp);
            unset($temp);
        }
        $files = array();
        $pwd = getcwd();
        chdir($this->options['basedir']);
        foreach ($list as $current) {
            $current = str_replace("\\", "/", $current);
            $current = preg_replace("/\\/+/", "/", $current);
            $current = preg_replace("/\\/\$/", "", $current);
            if (strstr($current, "*")) {
                $regex = preg_replace("/([\\\\^\$\\.\\[\\]\\|\\(\\)\\?\\+\\{\\}\\/])/", "\\\\\\1", $current);
                $regex = str_replace("*", ".*", $regex);
                $dir = strstr($current, "/") ? substr($current, 0, strrpos($current, "/")) : ".";
                $temp = $this->parse_dir($dir);
                foreach ($temp as $current2) {
                    if (preg_match("/^{$regex}\$/i", $current2['name'])) {
                        $files[] = $current2;
                    }
                }
                unset($regex, $dir, $temp, $current);
            } else {
                if (@is_dir($current)) {
                    echo "dir";
                    $temp = $this->parse_dir($current);
                    foreach ($temp as $file) {
                        $files[] = $file;
                    }
                    unset($temp, $file);
                } else {
                    if (@file_exists($current)) {
                        $files[] = array('name' => $current, 'name2' => $this->options['prepend'] . preg_replace("/(\\.+\\/+)+/", "", $this->options['storepaths'] == 0 && strstr($current, "/") ? substr($current, strrpos($current, "/") + 1) : $current), 'type' => @is_link($current) && $this->options['followlinks'] == 0 ? 2 : 0, 'ext' => substr($current, strrpos($current, ".")), 'stat' => stat($current));
                    } else {
                        echo "other error ";
                    }
                }
            }
        }
        chdir($pwd);
        unset($current, $pwd);
        usort($files, array("archive", "sort_files"));
        // prt($files); //die;
        return $files;
    }
    function parse_dir($dirname)
    {
        if ($this->options['storepaths'] == 1 && !preg_match("/^(\\.+\\/*)+\$/", $dirname)) {
            $files = array(array('name' => $dirname, 'name2' => $this->options['prepend'] . preg_replace("/(\\.+\\/+)+/", "", $this->options['storepaths'] == 0 && strstr($dirname, "/") ? substr($dirname, strrpos($dirname, "/") + 1) : $dirname), 'type' => 5, 'stat' => stat($dirname)));
        } else {
            $files = array();
        }
        $dir = @opendir($dirname);
        while ($file = @readdir($dir)) {
            $fullname = $dirname . "/" . $file;
            if ($file == "." || $file == "..") {
                continue;
            } else {
                if (@is_dir($fullname)) {
                    if (empty($this->options['recurse'])) {
                        continue;
                    }
                    $temp = $this->parse_dir($fullname);
                    foreach ($temp as $file2) {
                        $files[] = $file2;
                    }
                } else {
                    if (@file_exists($fullname)) {
                        $files[] = array('name' => $fullname, 'name2' => $this->options['prepend'] . preg_replace("/(\\.+\\/+)+/", "", $this->options['storepaths'] == 0 && strstr($fullname, "/") ? substr($fullname, strrpos($fullname, "/") + 1) : $fullname), 'type' => @is_link($fullname) && $this->options['followlinks'] == 0 ? 2 : 0, 'ext' => substr($file, strrpos($file, ".")), 'stat' => stat($fullname));
                    }
                }
            }
        }
        @closedir($dir);
        return $files;
    }
    function sort_files($a, $b)
    {
        if ($a['type'] != $b['type']) {
            if ($a['type'] == 5 || $b['type'] == 2) {
                return -1;
            } else {
                if ($a['type'] == 2 || $b['type'] == 5) {
                    return 1;
                } else {
                    if ($a['type'] == 5) {
                        return strcmp(strtolower($a['name']), strtolower($b['name']));
                    } else {
                        if ($a['ext'] != $b['ext']) {
                            return strcmp($a['ext'], $b['ext']);
                        } else {
                            if ($a['stat'][7] != $b['stat'][7]) {
                                return $a['stat'][7] > $b['stat'][7] ? -1 : 1;
                            } else {
                                return strcmp(strtolower($a['name']), strtolower($b['name']));
                            }
                        }
                    }
                }
            }
        }
        return 0;
    }
    function download_file()
    {
        if ($this->options['inmemory'] == 0) {
            $this->error[] = "Can only use download_file() if archive is in memory. Redirect to file otherwise, it\r\n\r\nis faster.";
            return;
        }
        switch ($this->options['type']) {
            case "zip":
                header("Content-Type: application/zip");
                break;
            case "bzip":
                header("Content-Type: application/x-bzip2");
                break;
            case "gzip":
                header("Content-Type: application/x-gzip");
                break;
            case "tar":
                header("Content-Type: application/x-tar");
        }
        $header = "Content-Disposition: attachment; filename=\"";
        $header .= strstr($this->options['name'], "/") ? substr($this->options['name'], strrpos($this->options['name'], "/") + 1) : $this->options['name'];
        $header .= "\"";
        header($header);
        header("Content-Length: " . strlen($this->archive));
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: no-cache, must-revalidate, max-age=60");
        header("Expires: Sat, 01 Jan 2000 12:00:00 GMT");
        print $this->archive;
    }
}
class tar_file extends archive
{
    function tar_file($name)
    {
        $this->archive($name);
        $this->options['type'] = "tar";
    }
    function create_tar()
    {
        $pwd = getcwd();
        chdir($this->options['basedir']);
        foreach ($this->files as $current) {
            if ($current['name'] == $this->options['name']) {
                continue;
            }
            if (strlen($current['name2']) > 99) {
                $path = substr($current['name2'], 0, strpos($current['name2'], "/", strlen($current['name2']) - 100) + 1);
                $current['name2'] = substr($current['name2'], strlen($path));
                if (strlen($path) > 154 || strlen($current['name2']) > 99) {
                    $this->error[] = "Could not add {$path}{$current['name2']} to archive because the filename is\r\n\r\ntoo long.";
                    continue;
                }
            }
            $block = pack("a100a8a8a8a12a12a8a1a100a6a2a32a32a8a8a155a12", $current['name2'], sprintf("%07o", $current['stat'][2]), sprintf("%07o", $current['stat'][4]), sprintf("%07o", $current['stat'][5]), sprintf("%011o", $current['type'] == 2 ? 0 : $current['stat'][7]), sprintf("%011o", $current['stat'][9]), "        ", $current['type'], $current['type'] == 2 ? @readlink($current['name']) : "", "ustar ", "\r\n\r\n", "Unknown", "Unknown", "", "", !empty($path) ? $path : "", "");
            $checksum = 0;
            for ($i = 0; $i < 512; $i++) {
                $checksum += ord(substr($block, $i, 1));
            }
            $checksum = pack("a8", sprintf("%07o", $checksum));
            $block = substr_replace($block, $checksum, 148, 8);
            if ($current['type'] == 2 || $current['stat'][7] == 0) {
                $this->add_data($block);
            } else {
                if ($fp = @fopen($current['name'], "rb")) {
                    $this->add_data($block);
                    while ($temp = fread($fp, 1048576)) {
                        $this->add_data($temp);
                    }
                    if ($current['stat'][7] % 512 > 0) {
                        $temp = "";
                        for ($i = 0; $i < 512 - $current['stat'][7] % 512; $i++) {
                            $temp .= "\0";
                        }
                        $this->add_data($temp);
                    }
                    fclose($fp);
                } else {
                    $this->error[] = "Could not open file {$current['name']} for reading. It was not added.";
                }
            }
        }
        $this->add_data(pack("a1024", ""));
        chdir($pwd);
        return 1;
    }
    function extract_files()
    {
        $pwd = getcwd();
        chdir($this->options['basedir']);
        if ($fp = $this->open_archive()) {
            if ($this->options['inmemory'] == 1) {
                $this->files = array();
            }
            while ($block = fread($fp, 512)) {
                $temp = unpack("a100name/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1type/a100symlink/a6magic/a2temp/a32temp/a32temp/a8temp/a8temp/a15\r\n\r\n5prefix/a12temp", $block);
                $file = array('name' => $temp['prefix'] . $temp['name'], 'stat' => array(2 => $temp['mode'], 4 => octdec($temp['uid']), 5 => octdec($temp['gid']), 7 => octdec($temp['size']), 9 => octdec($temp['mtime'])), 'checksum' => octdec($temp['checksum']), 'type' => $temp['type'], 'magic' => $temp['magic']);
                if ($file['checksum'] == 0x0) {
                    break;
                } else {
                    if (substr($file['magic'], 0, 5) != "ustar") {
                        $this->error[] = "This script does not support extracting this type of tar file.";
                        break;
                    }
                }
                $block = substr_replace($block, "        ", 148, 8);
                $checksum = 0;
                for ($i = 0; $i < 512; $i++) {
                    $checksum += ord(substr($block, $i, 1));
                }
                if ($file['checksum'] != $checksum) {
                    $this->error[] = "Could not extract from {$this->options['name']}, it is corrupt.";
                }
                if ($this->options['inmemory'] == 1) {
                    $file['data'] = fread($fp, $file['stat'][7]);
                    fread($fp, 512 - $file['stat'][7] % 512 == 512 ? 0 : 512 - $file['stat'][7] % 512);
                    unset($file['checksum'], $file['magic']);
                    $this->files[] = $file;
                } else {
                    if ($file['type'] == 5) {
                        if (!is_dir($file['name'])) {
                            mkdir($file['name'], $file['stat'][2]);
                        }
                    } else {
                        if ($this->options['overwrite'] == 0 && file_exists($file['name'])) {
                            $this->error[] = "{$file['name']} already exists.";
                            continue;
                        } else {
                            if ($file['type'] == 2) {
                                symlink($temp['symlink'], $file['name']);
                                chmod($file['name'], $file['stat'][2]);
                            } else {
                                if ($new = @fopen($file['name'], "wb")) {
                                    fwrite($new, fread($fp, $file['stat'][7]));
                                    fread($fp, 512 - $file['stat'][7] % 512 == 512 ? 0 : 512 - $file['stat'][7] % 512);
                                    fclose($new);
                                    chmod($file['name'], $file['stat'][2]);
                                } else {
                                    $this->error[] = "Could not open {$file['name']} for writing.";
                                    continue;
                                }
                            }
                        }
                    }
                }
                chown($file['name'], $file['stat'][4]);
                chgrp($file['name'], $file['stat'][5]);
                touch($file['name'], $file['stat'][9]);
                unset($file);
            }
        } else {
            $this->error[] = "Could not open file {$this->options['name']}";
        }
        chdir($pwd);
    }
    function open_archive()
    {
        return @fopen($this->options['name'], "rb");
    }
}
class gzip_file extends tar_file
{
    function gzip_file($name)
    {
        $this->tar_file($name);
        $this->options['type'] = "gzip";
    }
    function create_gzip()
    {
        if ($this->options['inmemory'] == 0) {
            $pwd = getcwd();
            chdir($this->options['basedir']);
            if ($fp = gzopen($this->options['name'], "wb{$this->options['level']}")) {
                fseek($this->archive, 0);
                while ($temp = fread($this->archive, 1048576)) {
                    gzwrite($fp, $temp);
                }
                gzclose($fp);
                chdir($pwd);
            } else {
                $this->error[] = "Could not open {$this->options['name']} for writing.";
                chdir($pwd);
                return 0;
            }
        } else {
            $this->archive = gzencode($this->archive, $this->options['level']);
        }
        return 1;
    }
    function open_archive()
    {
        return @gzopen($this->options['name'], "rb");
    }
}
class bzip_file extends tar_file
{
    function bzip_file($name)
    {
        $this->tar_file($name);
        $this->options['type'] = "bzip";
    }
    function create_bzip()
    {
        if ($this->options['inmemory'] == 0) {
            $pwd = getcwd();
            chdir($this->options['basedir']);
            if ($fp = bzopen($this->options['name'], "wb")) {
                fseek($this->archive, 0);
                while ($temp = fread($this->archive, 1048576)) {
                    bzwrite($fp, $temp);
                }
                bzclose($fp);
                chdir($pwd);
            } else {
                $this->error[] = "Could not open {$this->options['name']} for writing.";
                chdir($pwd);
                return 0;
            }
        } else {
            $this->archive = bzcompress($this->archive, $this->options['level']);
        }
        return 1;
    }
    function open_archive()
    {
        return @bzopen($this->options['name'], "rb");
    }
}
class zip_file extends archive
{
    function zip_file($name)
    {
        $this->archive($name);
        $this->options['type'] = "zip";
    }
    function create_zip()
    {
        $files = 0;
        $offset = 0;
        $central = "";
        if (!empty($this->options['sfx'])) {
            if ($fp = @fopen($this->options['sfx'], "rb")) {
                $temp = fread($fp, filesize($this->options['sfx']));
                fclose($fp);
                $this->add_data($temp);
                $offset += strlen($temp);
                unset($temp);
            } else {
                $this->error[] = "Could not open sfx module from {$this->options['sfx']}.";
            }
        }
        $pwd = getcwd();
        chdir($this->options['basedir']);
        foreach ($this->files as $current) {
            if ($current['name'] == $this->options['name']) {
                continue;
            }
            $timedate = explode(" ", date("Y n j G i s", $current['stat'][9]));
            $timedate = $timedate[0] - 1980 << 25 | $timedate[1] << 21 | $timedate[2] << 16 | $timedate[3] << 11 | $timedate[4] << 5 | $timedate[5];
            $block = pack("VvvvV", 0x4034b50, 0xa, 0x0, isset($current['method']) || $this->options['method'] == 0 ? 0x0 : 0x8, $timedate);
            if ($current['stat'][7] == 0 && $current['type'] == 5) {
                $block .= pack("VVVvv", 0x0, 0x0, 0x0, strlen($current['name2']) + 1, 0x0);
                $block .= $current['name2'] . "/";
                $this->add_data($block);
                $central .= pack("VvvvvVVVVvvvvvVV", 0x2014b50, 0x14, $this->options['method'] == 0 ? 0x0 : 0xa, 0x0, isset($current['method']) || $this->options['method'] == 0 ? 0x0 : 0x8, $timedate, 0x0, 0x0, 0x0, strlen($current['name2']) + 1, 0x0, 0x0, 0x0, 0x0, $current['type'] == 5 ? 0x10 : 0x0, $offset);
                $central .= $current['name2'] . "/";
                $files++;
                $offset += 31 + strlen($current['name2']);
            } else {
                if ($current['stat'][7] == 0) {
                    $block .= pack("VVVvv", 0x0, 0x0, 0x0, strlen($current['name2']), 0x0);
                    $block .= $current['name2'];
                    $this->add_data($block);
                    $central .= pack("VvvvvVVVVvvvvvVV", 0x2014b50, 0x14, $this->options['method'] == 0 ? 0x0 : 0xa, 0x0, isset($current['method']) || $this->options['method'] == 0 ? 0x0 : 0x8, $timedate, 0x0, 0x0, 0x0, strlen($current['name2']), 0x0, 0x0, 0x0, 0x0, $current['type'] == 5 ? 0x10 : 0x0, $offset);
                    $central .= $current['name2'];
                    $files++;
                    $offset += 30 + strlen($current['name2']);
                } else {
                    if ($fp = @fopen($current['name'], "rb")) {
                        $temp = fread($fp, $current['stat'][7]);
                        fclose($fp);
                        $crc32 = crc32($temp);
                        if (!isset($current['method']) && $this->options['method'] == 1) {
                            $temp = gzcompress($temp, $this->options['level']);
                            $size = strlen($temp) - 6;
                            $temp = substr($temp, 2, $size);
                        } else {
                            $size = strlen($temp);
                        }
                        $block .= pack("VVVvv", $crc32, $size, $current['stat'][7], strlen($current['name2']), 0x0);
                        $block .= $current['name2'];
                        $this->add_data($block);
                        $this->add_data($temp);
                        unset($temp);
                        $central .= pack("VvvvvVVVVvvvvvVV", 0x2014b50, 0x14, $this->options['method'] == 0 ? 0x0 : 0xa, 0x0, isset($current['method']) || $this->options['method'] == 0 ? 0x0 : 0x8, $timedate, $crc32, $size, $current['stat'][7], strlen($current['name2']), 0x0, 0x0, 0x0, 0x0, 0x0, $offset);
                        $central .= $current['name2'];
                        $files++;
                        $offset += 30 + strlen($current['name2']) + $size;
                    } else {
                        $this->error[] = "Could not open file {$current['name']} for reading. It was not added.";
                    }
                }
            }
        }
        $this->add_data($central);
        $this->add_data(pack("VvvvvVVv", 0x6054b50, 0x0, 0x0, $files, $files, strlen($central), $offset, !empty($this->options['comment']) ? strlen($this->options['comment']) : 0x0));
        if (!empty($this->options['comment'])) {
            $this->add_data($this->options['comment']);
        }
        chdir($pwd);
        return 1;
    }
}



/** 定时执行 shell 脚本 **/
// #!/bin/bash
// PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin
// export PATH
// 
// # crontab -e
// # cat /etc/crontab
// #分 时 日 月 周
// # centos
// #echo '* */1 * * * /var/www/mmh/mmh.sh > /dev/null 2>&1' >> /var/spool/cron/root
// #crontab /var/spool/cron/root
// 
// # ubuntu
// #echo '* */1 * * * /var/www/mmh/mmh.sh > /dev/null 2>&1' >> /var/spool/cron/crontabs/root
// #crontab /var/spool/cron/crontabs/root
// 
// mmhpath=/var/www/mmh
// test -d $mmhpath || mkdir -p $mmhpath
// 
// # --spider 不下载任何文件。
// /usr/bin/wget --no-check-certificate -O $mmhpath/getmh.log https://ysuo.org/mmh/getmh.php
// rm -rf $mmhpath/getmh.log

