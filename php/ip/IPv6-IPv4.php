<?php

/* *================ 对端脚本
# ETH0ORSIMILAR=$(ip route get 8.8.8.8 | awk -- '{printf $5}')
# IP=$(ifdata -pa $ETH0ORSIMILAR)

$company = 'hostdare >> ';
$nic = '[eth0]';
$ipinfo = @$_SERVER['SERVER_NAME'] . ' - ';
if('/' == DIRECTORY_SEPARATOR){
    $ipinfo = @$_SERVER['SERVER_ADDR'] . ' - ';
    }else{
    $ipinfo = @gethostbyname($_SERVER['SERVER_NAME']) . ' - ';
    }
// 这里需要自行设定/etc/issue
if(PHP_OS == 'Linux'){
    $kernel = substr(php_uname('r'), 0, stripos(php_uname('r'), '-'));
    // $os = file_get_contents('/etc/issue');
    $lastline = exec('cat /etc/issue', $res, $rc);
    $os = $res[0];
    $os = str_replace(array("\r\n", "\n", '\n', '\l'), '', $os);
    $os = trim($os) . ' - ' . $kernel;
    }else{
    $os = php_uname('s') . ' ' . php_uname('r');
    }
$apache = trim($_SERVER['SERVER_SOFTWARE']) . ' ';
$apache = substr($apache, 0, stripos($apache, ' '));
$s_info = $company . $nic . $ipinfo . $os . ' - ' . $apache . ' PHP/' . phpversion();

$c_ip = @$_SERVER['REMOTE_ADDR'];

// ajax调用实时刷新
if ($_GET['act'] == "rt"){
    $arr = array(
        'c_ip' => "$c_ip",
        's_info' => "$s_info",
        );
    $jarr = json_encode($arr);
    $_GET['callback'] = htmlspecialchars($_GET['callback']);
    echo $_GET['callback'],'(',$jarr,')';
    # exit;
}else{
    echo '<small>' . rand() . '</small><br><br><center><h3>';
    echo ' ' . $_SERVER['REMOTE_ADDR'] . "<br>\r\n";
    # if(isset($_SERVER['HTTP_FORWARDED'])) echo 'HTTP_FORWARDED: '. $_SERVER['HTTP_FORWARDED'] ."<br>\r\n";
    if(isset($_SERVER['HTTP_X_FORWARDED'])) echo 'HTTP_X_FORWARDED: ' . $_SERVER['HTTP_X_FORWARDED'] . "<br>\r\n";
    if(isset($_SERVER['HTTP_FORWARDED_FOR'])) echo 'HTTP_FORWARDED_FOR: ' . $_SERVER['HTTP_FORWARDED_FOR'] . "<br>\r\n";
    if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) echo 'HTTP_X_FORWARDED_FOR: ' . $_SERVER['HTTP_X_FORWARDED_FOR'] . "<br>\r\n";
    if(isset($_SERVER['HTTP_CLIENT_IP'])) echo 'HTTP_CLIENT_IP: ' . $_SERVER['HTTP_CLIENT_IP'] . "<br>\r\n";
    echo '</h3></center>';
}
*/

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>访客 IP 信息</title>
<script language="JavaScript" type="text/javascript" src="https://raw.githubusercontent.com/6tu/code/master/php/vpstz/jquery-1.11.3.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){getJSONData();});
function getJSONData(){
    setTimeout("getJSONData()", 1000);
    $.getJSON("http://1suo.net/ip.php?act=rt&callback=?", displayData_info)
}
function displayData_info(dataJSON){
    $("#c_ip").html(dataJSON.c_ip);
    $("#s_info").html(dataJSON.s_info);
}
</script>
</head>
<body><br><center><table><tr>
<?php
/*
echo $_SERVER['SERVER_NAME'];
if('/' == DIRECTORY_SEPARATOR){
    echo '(' . $_SERVER['SERVER_ADDR'] . ')';
}else{
    echo '(' . @gethostbyname($_SERVER['SERVER_NAME']) . ')';
}*/
$cip = @$_SERVER['REMOTE_ADDR'];
if(filter_var($cip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
    echo '<td>', $cip, '<p><br></td></tr><tr><td>检测不到 IPV6 地址<p><br></td></tr>';
}else {
    echo '<td>', $cip, '<p></td></tr><tr><td><span id="c_ip"></span><p></td></tr>';
}
?>

<!-- IPv6-test.com widget BEGIN -->
<script type="text/javascript">var _ipv6test_widget_style = {
border: "solid 1px #000",
show_country_flags: true,
show_loading_anim: true,
ipv4_label_color: "#393",
ipv4_background_color: "#eee",
ipv6_label_color: "#339",
ipv6_background_color: "#ddd",
stats_position: "top",
stats_font_size: "10px",
stats_color: "#eee",
stats_color_v4: "#beb",
stats_color_v6: "#bbe",
stats_background_color: "#666"
}</script>

<tr><td><p>
<div id="_ipv6test_widget" style="width:250px;display:none">loading <a href="http://ipv6-test.com/">IPv6 connection test</a> ...</div><script type="text/javascript" src="http://ipv6-test.com/api/widget.php?domain=referer" async="async"></script>
<!-- IPv6-test.com widget END -->

</td></tr></table>
</body></html>

