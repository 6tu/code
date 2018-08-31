<?php

if(empty($_GET['randkey'])){
    form_html();
    exit(0);
}

form_html();
$pubkey = trim($_GET['pubkey']);
$allowedips = trim($_GET['allowedips']);
$cmd = "sudo /usr/bin/wg set wg0 peer $pubkey allowed-ips $allowedips";
//echo $cmd . "<br>\r\n";
//exec($cmd, $res, $rc); 
system($cmd, $rc);
if($rc == 0){
    echo "\r\n<br><br><center><b>受理成功，以下是节点信息。 返回<a href='" . php_self() . "'> 当前页</a></b><br><br> \r\n";
    //exec("sudo /usr/bin/wg", $res, $rc);
    //print_r($res);
    echo '<table>';
    echo '	<tr><td>public key:     </td><td> nizEyMf6rv2xyCgdVxfG6sKEMVTUdTe+jVmXYcSkEyw= </td></tr>';
    echo '	<tr><td>listening port: </td><td> 56660                                        </td></tr>';
    echo '	<tr><td>Endpoint:       </td><td> ' .$_SERVER['SERVER_ADDR'] .                '</td></tr>';
    echo '</table>';
    echo '</center>';
}else{
    echo "\r\n<br><br><center><b>请求受理失败，返回<a href='" . php_self() . "'> 当前页</a></b></center><br><br> \r\n";
}


# ================ 函数区，基本无需修改 ================#

function form_html(){
    //header("Content-type: text/html; charset=utf-8");
    $html  = "<!DOCTYPE html> \r\n";
    $html .= "<html xmlns=\"http://www.w3.org/1999/xhtml\"> \r\n";
    $html .= "    <head> \r\n";
    $html .= "        <title>WireGuard PEER</title> \r\n";
    $html .= "    </head> \r\n";
    $html .= "    <body><center><br /> \r\n";
    $html .= "        <b>WireGuard PEER</b> \r\n";
    $html .= "        <form action=\"" . php_self() . "\" method=\"get\"><br /> \r\n";
    $html .= "			Public key : <input type=\"text\" name=\"pubkey\" size=\"60\" value=\"\" /><br /> <br>\r\n";
    $html .= "			Allowed IPs: <input type=\"text\" name=\"allowedips\" size=\"60\" value=\"10.0.0.3/24\" /><br /> \r\n";
    $html .= "			             <input type=\"hidden\" name=\"randkey\" value=\"" . randkey() . "\" /><br /> \r\n";
    $html .= "			<input type=\"submit\" size=\"60\" value=\"Send\" /><br /> \r\n";
    $html .= "        </form></center> \r\n";
    $html .= "    </body> \r\n";
    $html .= "</html> \r\n";

    echo $html;
}

# 获取当前PHP文件名
function php_self(){
    $php_self = substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/') + 1);
    return $php_self;
}

function randkey(){
    $key = date("Y/m/d") . '@' . $_SERVER['REMOTE_ADDR'];
    $randkey = base64_encode(hash('sha256', $key, true));
	return $randkey;
}



