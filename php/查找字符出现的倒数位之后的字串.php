<?php
$str = "/var/www/mmh/mhdata/p7m-t.zip.zip";
$find = "/";
$offset = 1;
$newstr = findstrpos($str, $find, $offset);
echo $newstr . "<br>\r\n";

# 查找字符出现的倒数$offset=1位之后的字串
function findstrpos($str, $find, $offset){
    $pos = strrpos($str,$find);                #最后出现的位置
    $chr = strrchr($str, $find);               #最后出现的位置及之后的字符串
    $len = strlen($str);                       #获取字符串的长度

    $sub = substr_count($str, $find);          #查找字符出现的次数
    $n = $sub - $offset;
    $start = 0;
    for($i = 1;$i <= $n;$i++){
        $start = strpos($str, $find, $start);  #从首位开始查找，
        $i != $n && $start++;
    }
    //echo $start;
    $newstr = substr($str, $start + 1);        #从指定位置开始截取字符串，可以指定截取的长度。
    return $newstr;
}
