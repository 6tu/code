<?php
/**
 * 用途:cURL模拟登录Wordpress导出数据
 * 第 98~120 行用于网页超链接中 ID ==> URL 的索引, 另存为 index.php
 */

set_time_limit(0);
date_default_timezone_set('Asia/Shanghai');
$date = date("Y-m-d"); 
customize_flush();

# 设置wordpress地址、账户和密码
$wp_host = 'https://ys138.win/';                 # wordpress地址
$user = 'admin';        	                     # 账号
$passwd = 'passwd';                           # 密码
$xmlfile = 'wordpress.' . $date . '.xml';        # xml 文件名

$static_host = 'ysuo.org';                       # 静态镜像域名，不带http或者https，最后边不加 /
$static_host = $_SERVER['HTTP_HOST'];
$scheme = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'))?'https://':'http://';

$cookie_file = tempnam('./temp', 'cookie');      # cookie临时存储文件
$id_url_log = 'id-url-' . $date . '.log';        # 记录 ID对应 URL的文件
$old_log = 'old.log';                            # 当前最新的第一篇文件地址
$new_log = 'new.txt';                            # 尚未静态化的 URL

/**------- 以下无须设置 -------*/
# 设置要访问的目标
$redirect_url = $wp_host . 'wp-admin/export.php?download=true&content=all&cat=0&post_author=0&post_start_date=0&post_end_date=0&post_status=0&page_author=0&page_start_date=0&page_end_date=0&page_status=0&attachment_start_date=0&attachment_end_date=0&submit=%E4%B8%8B%E8%BD%BD%E5%AF%BC%E5%87%BA%E7%9A%84%E6%96%87%E4%BB%B6';

# 登录获取cookie
$login_url = $wp_host . 'wp-login.php';
$post_fields = 'log=' . $user . '&pwd=' . $passwd . '&rememberme=forever&redirect_to=' . $wp_host . '&testcookie=1';
$result = login($login_url, $cookie_file, $post_fields);
$header = file($cookie_file);
$n = count($header);
if($n < 6) die(' <br><b>Logon failure: unknown user name or bad password</b>');

# 导出数据
$xml = getdb($redirect_url, $cookie_file);
if(file_exists($xmlfile) == true){
	@chmod($xmlfile, 0777);
	@unlink($xmlfile);
}
file_put_contents($xmlfile, $xml);
//@unlink($cookie_file);
echo '<br><div>&nbsp; <b>Successful Data Backup</b><a href=./' . $xmlfile . '>  ' . $xmlfile . '</a>';

/* *
   * 从 wordpress 的备份数据中提取 id 和 url
   * 适用于 wget 提取的 wordpress 静态镜像
   *
*/

if(file_exists($xmlfile) == false) die('backup file is not exists');
$line = explode("\n", $xml);
$ny = count($line);
$xml_item = '';
$id_url_path = '';
for($y = 0;$y < $ny;$y++){
    if(strpos($line[$y], '<item>')) $xml_item .= "<itemline=$y>\r\n";
    if(strpos($line[$y], '<wp:post_id>')){
        $xml_item .= $line[$y] . "\r\n";
        $postid = str_replace(array('<wp:post_id>', '</wp:post_id>', '<![CDATA[', ']]>'), '', $line[$y]);
        $id_url_path .= trim($postid) . ' ';
    }
    if(strpos($line[$y], '<wp:post_date>')){
        $xml_item .= $line[$y] . "\r\n";
        $date = str_replace(array('<wp:post_date>', '</wp:post_date>', '<![CDATA[', ']]>'), '', $line[$y]);
        $postdate = explode(' ', $date);
        $date = str_replace('-', '/', $postdate['0']) . '/';
        $id_url_path .= trim($date);
    }
    if(strpos($line[$y], '<wp:post_name>')){
        $xml_item .= $line[$y] . "\r\n";
        $postname = str_replace(array('<wp:post_name>', '</wp:post_name>', '<![CDATA[', ']]>'), '', $line[$y]);
        $id_url_path .= trim($postname) . "/\r\n";
    }
    if(strpos($line[$y], '</item>')) $xml_item .= "</item>\r\n";
}
// file_put_contents('item.txt',$xml_item); # 精简的 item
if(file_exists($id_url_log) == true) @unlink($id_url_log);
file_put_contents($id_url_log, $id_url_path);     # 包含 id 和 url 
echo '<br><br><div>&nbsp; <a href="' . $id_url_log . '">' . $id_url_log . "</a>  create success <br><br>\r\n";

$xmlpath = getcwd() . $xmlfile;
$user = trim(shell_exec('whoami'));
@chgrp($xmlfile, $user);
@chown($xmlfile, $user);
@chmod($xmlfile, 0000);
@unlink($cookie_file);

/** 
 * 以下代码另存为 index.php
 * 用于网页超链接中 ID ==> URL 的索引
*/

/**
<?php
$static_host = 'ysuo.org';                              # 静态镜像域名，不带http或者https，最后边不加 /
$static_host = $_SERVER['HTTP_HOST'];
$id_url_log = 'id-url-' . '2017-06-21' . '.log';        # 记录 ID对应 URL的文件

# $id_url_path 的数组格式 $arr_url
if(file_exists($id_url_log) == false) die('file is not exists');
$id_url_path = file_get_contents($id_url_log);
$array = explode("\r\n", $id_url_path);
$nz = count($array)-1;
$arr_url = array();
for($z = 0;$z < $nz;$z++){
    $array_url = explode(' ', $array[$z]);
    $arr_url = $arr_url + array($array_url['0'] => $array_url['1']);
}
// print_r($arr_url);
# $arr_url[$_GET['p']] 即是 ID 对应的URL ,这里$static_host/ 后面默认的是 index.php 文件
if(isset($_GET['p'])){
    $scheme = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'))?'https://':'http://';
    $static_url = $scheme . $static_host . '/' . $arr_url[$_GET['p']];
	echo $static_url;
    header('Location:' . $static_url);
}
*/





/* *
   * 生成 wget 尚未提取的 wordpress 日志
   *
*/

# 获取静态镜像的首页，提取当前最新文件的日期。也可以使用目录扫描的办法
$index = file_get_contents($scheme . $static_host);
$arr_index = explode('<article id', $index);
$arr_title = explode('<h3 class="entry-title h4">', $arr_index[1]);
$arr_content = explode('<div class="entry-content', $arr_title[1]);
$arr_link = explode('"', $arr_content[0]);
$link = del13($wp_host . $arr_link[1]);

$arr_old = explode("\r\n", $link);
$n = count($arr_old) - 1;
$oldurl = $arr_old[$n];
$arr_date = explode("/", $oldurl);
$host_url = $arr_date[0].'//'.$arr_date[2].'/';
$date_old = $arr_date[3].$arr_date[4].$arr_date[5];

if(file_exists($old_log) == true) @unlink($old_log);
file_put_contents($old_log, $link);
echo " $link <div>&nbsp; old date: <b>" . $date_old . "</b><br><br>\r\n";

unset($n);
unset($oldurl);
unset($arr_date);
unset($xml);

# 获取$wp_host中没有静态化的文章列表
$arr_new = explode("\r\n", $id_url_path);
$n = count($arr_new);
$url = '';
for($i = 0; $i < $n ; $i++){
	$arr = @explode(" ", $arr_new[$i]);
	$arr_date = explode("/", @$arr[1]);	
	$date_new = $arr_date[0].@$arr_date[1].@$arr_date[2];
	if($date_old <=  $date_new) $url .= trim($host_url) . trim($arr[1]) . "\r\n";
}
unset($id_url_path);
unset($arr_new);
unset($i);
unset($n);
unset($arr);

$url = del13($url);

# 检测$wp_host中的URL是否有效
$arr = explode("\r\n", $url);
$n = count($arr);
$url = '';
for($i = 0; $i < $n ; $i++){
	if(chkurl($arr[$i]) == true) {
		echo '<div>&nbsp; ' . $arr[$i] . "<br>\r\n";
		$url .= $arr[$i] . "\r\n";
	}
}

if(file_exists($new_log) == true) @unlink($new_log);
file_put_contents($new_log, $url);
echo '<br><br><div>&nbsp; <b>done  </b> <a href="' .$new_log. '">' .$new_log. '</a>';



# 相关函数

# 删除首位空格和多余的回车
function del13($str){
	$str = trim($str);
	$str = str_replace("\n\n", "\r\n", $str);
	$str = str_replace("\r\n\r\n", "\r\n", $str);
	return $str;
}

# 分析网页的 HEADER
function array_header($result){
	$array = explode("\r\n\r\n", $result, 2);
	$header = explode("\r\n", $array['0']);
	$array_header = array();
	$n = count($header);
	for ($i = 1; $i < $n; $i++){
		$elements = explode(":", $header[$i], 2);
		$array_header = $array_header + array($elements['0'] => $elements['1']);
	}
	return $array_header;
}

# 由IP单元生成随机IP
function ipv4(){
    $a = (int)unit();
    $b = '.' . (int)unit();
    $c = '.' . (int)unit() . '.';
    $d = (int)unit();
    if($a === 0) $a = '120';
    if($d === 0) $d = '254';
    $ip = $a . $b . $c . $d;
    return $ip;
    }

# 生成IP单元
function unit(){
    $one = mt_rand(0, 2);
    if($one == 2){
        $two = mt_rand(0, 5);
        $three = mt_rand(0, 5);
        }else{
        $two = mt_rand(0, 9);
        $three = mt_rand(0, 9);
        }
    return $one . $two . $three;
    }

# 刷新缓冲
function customize_flush(){
    if(php_sapi_name() === 'cli'){
	return true;
	}else{
        echo(str_repeat(' ',256));
        // check that buffer is actually set before flushing
        if (ob_get_length()){           
            @ob_flush();
            @flush();
            @ob_end_flush();
        }   
        @ob_start();
	}
}

# 针对curl对SSL的验证验证缺陷，这里使用file_get_contents获取网页的报头和内容
# 验证是否能 SSL ，必须是 https:// 模式
function is_SSL($https){
    $arrContextOptions=array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );  
    $response = file_get_contents($https, false, stream_context_create($arrContextOptions));
	$headers = $http_response_header;
	$num = count($headers);
	$str = '';
	for($i = 0; $i < $num ;$i++){
		$str .= $headers[$i] . "\r\n";
	}
    $response = $str ."\r\n" .$response;
	if(strstr($headers[0] , '200')) return 'https://';
	else return 'http://';
}

# 通过CURL分析HEADER,但对SSL有缺陷
function get_web_page( $url ){
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "spider", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        CURLOPT_SSL_VERIFYPEER => false,    // Disabled SSL Cert checks
		CURLOPT_SSL_VERIFYHOST => 0,
    );

    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
	echo $content;
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $header;
}

# 登录帐号，保存 $cookie
function login($url, $cookie, $post){
    global$host;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:' . ipv4()));
    curl_setopt($ch, CURLOPT_REFERER, $host);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
    }
	
# 用 $cookie 进入账号下载文件
function getdb($url, $cookie){
    global$host;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:' . ipv4()));
    curl_setopt($ch, CURLOPT_REFERER, $host);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
    }

# 用 404 判断网页是否存在
function chkurl($url){
	$handle = curl_init($url);
	curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 1200);//设置超时时间
	curl_setopt($handle, CURLOPT_TIMEOUT, 1200);
	curl_setopt($handle, CURLOPT_HEADER, 0);
	curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

	curl_exec($handle);
	//检查是否404（网页找不到）
	$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
	if($httpCode == 404) {
		return false;
	}else{
		return true;
	}
	curl_close($handle);
}
