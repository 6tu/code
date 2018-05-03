<?php
/**
 * ==> 用途
 * 该脚本用来修改Linux系统中/etc/hosts.allow文件以允许当前IP临时登录sshd
 * 该脚本需要使用exec()函数执行shell脚本，确保其没有禁用
 * 
 * ==> 安装
 * 登录sshd后对/etc/hosts.allow和/etc/hosts.deny修改如下
 * 在/etc/hosts.allow文件中追加 sshd:当前IP
 * 在/etc/hosts.deny文件中追加 sshd:all
 * 修改完后重启xinetd #service xinetd restart
 * 
 * 用 <?php echo exec('whoami'); ?> 查看当前PHP的所属用户名
 * 用 visudo 命令编辑/etc/sudoers
 * 把用户名 $shell_path追加到该文件中，从而使PHP以sudo权限执行该脚本
 * 
 * ==> 使用
 * 用$_GET['admin']调出密码表单
 * 当 $_POST['pw'] = $pw_allow 时，修改为许可当前IP登录
 * 当 $_POST['pw'] = $pw_deny 时，恢复默认值
 * 
 * ==> 安全策略
 * 密码策略为关键词匹配和长度匹配
 * 密码格式为: 设定值 + KEY + 填充值，$pw_allow 长24 ，$pw_deny 长16
 * 当密码（$_POST['pw']）输错三次，则拒绝该C段IP登录一个月
 * 如果服务器支持 SSL ,则以HTTPS协议提交密码
 *
 */

# 基本设定
$prefix_pw_allow = 'allow_pw';
$prefix_pw_deny = 'Deny#pw';
$pw_allow_length = 24;
$pw_deny_length = 16;
$shell_path = '/home/wwwroot/default/cp.sh';
$hosts_path = 'hosts.allow';
$tmp_path = './tmp';
$os_name = PHP_OS;

# 设定session保存路径和保存时长
if(file_exists($tmp_path) == false) mkdir($tmp_path, 0700);
session_save_path($tmp_path);     # SESSION 存储路径
$session_time = 864000; 	      # SESSION 过期时间(秒)
$lifeTime = 10; 			      # COOKIE 保存时间(分钟)
session_start();

# 提取C段IP和生产 KEY
$ip = $_SERVER['REMOTE_ADDR'];
$array_ip = explode('.', $ip);
$ip_c = $array_ip[0] . '.' . $array_ip[1] . '.' . $array_ip[2];
$ipmd5 = strtolower(md5($ip));
$array = array(substr($ipmd5, 0, 8), strtoupper(substr($ipmd5, 8, 8)), substr($ipmd5, 16, 8), strtoupper(substr($ipmd5, 24, 8)));
$num = rand(0, 3);
$key = $array[$num];

# 对同一浏览器记录 IP 和 KEY
ini_set('session.gc_maxlifetime', $session_time);
if(empty($_SESSION[$ip_c])) $_SESSION[$ip_c] = '';
if(empty($_SESSION['key'])) $_SESSION['key'] = $key;
else $key = $_SESSION['key'];

# 重新设定密码
$pw_allow = $prefix_pw_allow . $key;
$pw_deny = $prefix_pw_deny;

# 获取当前网址
$http_type = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://'; 
$url = $http_type . $_SERVER["HTTP_HOST"] . $_SERVER['PHP_SELF'];

if(isset($_GET['admin']) and $_SESSION[$ip_c] < 3){

    if($os_name !== "Linux") die('建议在Linux系统中使用');
    if(function_exists("exec") == false) die('exec() 函数可能被禁止');
    echo(pw_form($num));
}
if(isset($_POST['pw']) and $_SESSION[$ip_c] < 3){

    # 如果输入的密码匹配$pw_allow，则修改/etc/hosts.allow
    if(strstr($_POST['pw'], $pw_allow) and strlen($_POST['pw']) == $pw_allow_length){
        $_SESSION['ok'] = 1;
        $_SESSION[$ip_c] = 0;
        $allow = hosts() . 'sshd:' . $ip_c . ".*\r\n";
	file_put_contents($hosts_path, $allow);

	exec('sudo ' . $shell_path);
        customize_flush();
	echo '<meta http-equiv="refresh" content="1; url=' .$url. '">';
    }
	
    # 如果输入的密码匹配$pw_deny，则恢复 /etc/hosts.allow
    if(strstr($_POST['pw'], $pw_deny) and strlen($_POST['pw']) == $pw_deny_length){
        $_SESSION['ok'] = 1;
        $_SESSION[$ip_c] = 0;
        $deny = hosts();
        file_put_contents($hosts_path, $deny);
        customize_flush();
		echo '<meta http-equiv="refresh" content="1; url=' .$url. '">';
    }
	
    # 如果密码输入错误，则记录错误次数
    if(!isset($_SESSION['ok'])) @$_SESSION[$ip_c] = $_SESSION[$ip_c] + 1;
    @setcookie(session_name(), session_id(), time() + $lifeTime, "/");
}
else exit(index());







/**------------ 以下为函数区，无需修改 ------------*/
# 首页内容
function index(){
    return beautify_html('<title>ip</title><br><br><br><br><br><center>' . $_SERVER['REMOTE_ADDR'] . '</center>');
}

# 提交表单
function pw_form($num){
	global $pw_allow;

    $base_url = $_SERVER["HTTP_HOST"] . $_SERVER['PHP_SELF'];
	$https = 'https://' . $base_url;
	$scheme = is_SSL($https);
    if(empty($_SERVER['HTTPS']) and $scheme == 'https://'){
        $https_url = $scheme . $base_url;
        header("Location:" . $https_url . '?admin');
    }else $https_url = $scheme . $base_url;

    $html = '<!DOCTYPE html><html><head><title>请输入密码</title><meta charset="utf-8"/></head>';
    $html .= '<body><br><br><br><center><form id="change" action="" method="post">';
    $html .= '密码 : <input type="password" id="pw" name="pw" value="'.$pw_allow.'"><br>';
    $html .= '<input type="hidden" id="hidden" name="key" value="' . $num . '"><br>';
    $html .= '<input type="submit" id="button" value="提交"></form>';
    $html .= '<br><a href=" ' . $https_url . '">返 回</a></center></body></html>';
    return beautify_html($html);
}

# /etc/hosts.allow 基本内容
function hosts(){
    $hosts = "";
    $hosts .= "# /etc/hosts.allow: list of hosts that are allowed to access the system.\r\n";
    $hosts .= "#                   See the manual pages hosts_access(5) and hosts_options(5).\r\n";
    $hosts .= "#\r\n";
    $hosts .= "# Example:    ALL: LOCAL @some_netgroup\r\n";
    $hosts .= "#             ALL: .foobar.edu EXCEPT terminalserver.foobar.edu\r\n";
    $hosts .= "#\r\n";
    $hosts .= "# If you're going to protect the portmapper use the name \"rpcbind\" for the\r\n";
    $hosts .= "# daemon name. See rpcbind(8) and rpc.mountd(8) for further information.\r\n";
    $hosts .= "#\r\n";
    $hosts .= "sshd:67.21.65.56\r\n";
    $hosts .= "sshd:104.131.150.174\r\n";
    $hosts .= "sshd:192.210.192.248\r\n";
    $hosts .= "sshd:89.36.215.108\r\n";
    return $hosts;
}

# HTML 美化
function beautify_html($html){
    $tidy_config = array(
        'clean' => true,
        'indent' => true,
        'indent-spaces' => 4,
        'output-xhtml' => true,
        'show-body-only' => false,
        'wrap' => 200
        );
    if(function_exists('tidy_parse_string')){ 
        $tidy = tidy_parse_string($html, $tidy_config, 'utf8');
        $tidy -> cleanRepair();
        return $tidy;
    }
    else return $html;
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
?>

