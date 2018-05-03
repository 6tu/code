<?php
/**
 * 字串乱序 PHP&JS 
 *
 * php 中把字串乱序后输出给客户机的 JAVASCRIPT , JAVASCRIPT 中恢复
 * 在指定长度提取一个字符，并把这一组字串倒序，
 * 形成这样的几组字串，最后貌似就形成了数字。
 * 
 * 效果一般
 */

$str = "0123456789测试ab“\"'cdefghijklmnop你好多字节字符乱";

$url = 'http://www.baidu.com';
$str = httpget($url);
$str = mb_convert_encoding($str, 'GB18030', 'utf-8');

$r = 64;
$m = mb_strlen($str);
$n = $m / $r;
$jsarr = '';
for($i = 0;$i < $r;$i++){
    $nstr = '';
    for($j = 0;$j < $n;$j++){
        $n2 = $j * $r + $i;
        if($m < $n2) break;
		
        $nstr = mb_substr($str, $n2, 1, 'GB18030') . $nstr;  // 声明 $str 的编码
        # $nstr = @$str[$n2].$nstr;    	                 // 多字节乱码
    }
    $nstr = "\"" . addJsSlashes($nstr) . "\",";
    // addslashes addcslashes
    $jsarr .= $nstr;
    }

	
echo "\r\n\r\n<script> arr = [".$jsarr."];"; 

function addJsSlashes($str){
    $str = addcslashes($str, "\0..\006\010..\012\014..\037\042\047\134\177"); 
    return str_replace(array(chr(7), chr(11)), array('\007', '\013'), $str);
    }
function httpget($url){
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);            // 不响应 header
    curl_setopt($ch, CURLOPT_NOBODY, FALSE);        // 输出 body 区
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    $response = curl_exec($ch);
    if(curl_getinfo($ch, CURLINFO_HTTP_CODE) == '200'){
        return $response;
        }
    else return NULL;
    curl_close($ch);
    }

?>

function decode(arr) {
    var width = arr[0].length;
    var sfarr = new Array(width);
    for (var c = 0; c < width; c++) {
        sfarr[c] = "";
    }
    for (var c = 0; c < arr.length; c++) {
        if (arr[c] == null) continue;
        var str = new String(arr[c]);
        var dif = width - str.length;
        for (var z = 0; z < str.length; z++) {
            if (str.charAt(z) + "" == "") continue;
            else sfarr[z + dif] += str.charAt(z);
        }
    }
    var w = "";
    for (var c = 0; c < sfarr.length; c++) {
        w = sfarr[c] + w;
    }
    document.write(w);
};
decode(arr);
document.close();
</script>

