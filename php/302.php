<?php
function getrealurl($url){
    $header = get_headers($url, 1);
    if(strpos($header[0], '301') || strpos($header[0], '302')){
        if(is_array($header['Location'])){
            return $header['Location'][count($header['Location'])-1];
        }else{
            return $header['Location'];
        }
    }else{
        return $url;
    }
}
$url = 'https://www.apachefriends.org/xampp-files/7.2.4/xampp-linux-x64-7.2.4-0-installer.run';
echo getrealurl($url);
# 源码显示
echo highlight_file("001.php");
echo "\r\n\r\n<br><br>";
echo file_get_contents("test.php"); 
?>
