<?php
# ---------rc4----------------------
# usage: prepare_key($the_key);
#        rc4($the_message);
$keystate = array();
$keyx = "";
$keyy = "";
function prepare_key($the_key){
    global $keystate, $keyx, $keyy;
    $key = $the_key;
    $keyary = array();
    $index = "";
    $jump = "";
    $temp = "";
    $keylen = "";
    $keylen = strlen($key);
    for($index = 0;$index <= 255;$index++){
        $keyary[$index] = Ord(substr($key, $index % $keylen, 1));
    }
    for($index = 0;$index <= 255;$index++){
        $keystate[$index] = $index;
    }
    $jump = 0;
    for($index = 0;$index <= 255;$index++){
        $jump = ($jump + $keystate[$index] + $keyary[$index]) % 256;
        $temp = $keystate[$index];
        $keystate[$index] = $keystate[$jump];
        $keystate[$jump] = $temp;
    }
    $keyx = 0;
    $keyy = 0;
}
function rc4($the_message){
    global $keystate, $keyx, $keyy;
    $message = $the_message;
    $RC4 = "";
    $index = "";
    $jump = "";
    $temp = "";
    $y = "";
    $t = "";
    $x = "";
    $index = $keyx;
    $jump = $keyy;
    $RC4 = "";
    for($x = 0;$x < strlen($message);$x++){
        $index = ($index + 1) % 256;
        $jump = ($jump + $keystate[$index]) % 256;
        $t = ($keystate[$index] + $keystate[$jump]) % 256;
        $temp = $keystate[$index];
        $keystate[$index] = $keystate[$jump];
        $keystate[$jump] = $temp;
        $y = $keystate[$t];
        $RC4 .= Chr(Ord(substr($message, $x, 1)) ^ $y);
    }
    $keyx = $index;
    $keyy = $jump;
    return($RC4);
}
# ---------xtea---------------------
# usage: xteaencode($the_message,$the_key);
#        xteadecode($the_message,$the_key);
function xteaplus($a, $b){
    $ret = "";
    if($b > 0 && $a > 2147483647 - $b){
        $ret = -2147483648 + ($a - (2147483647 - $b))-1;
        return($ret);
    }
    if($b < 0 && $a < -2147483648 - $b){
        $ret = 2147483647 - (-2147483648 - $b - $a) + 1;
        return($ret);
    }
    $ret = $a + $b;
    return($ret);
}
function xteaencode($the_message, $the_key){
    $message = $the_message;
    $key = $the_key;
    if(strlen($message) != 8 || strlen($key) != 16){
        return("");
    }
    $k = array();
    for($i = 0;$i < 4;$i++){
        $k[$i] = 0;
        for($x = 0;$x < 4;$x++){
            $k[$i] <<= 8;
            //$k[$i] |= Ord(substr($key, $i * 4 + $x, 1));
			$k[$i] |= Ord(substr($key, $i*4+$x, 1));
        }
    }
    $v = array();
    for($i = 0;$i < 2;$i++){
        $v[$i] = 0;
        for($x = 0;$x < 4;$x++){
            $v[$i] <<= 8;
            $v[$i] |= Ord(substr($message, $i * 4 + $x, 1));
        }
    }
    $y = $v[0];
    $z = $v[1];
    $sum = 0;
    $delta = -1640531527;
    $n = 32;
    while($n-- > 0){
        $y = xteaplus($y, xteaplus(($z << 4 ^ ($z >> 5 & 0x07ffffff)), $z) ^ xteaplus($sum, $k[$sum & 3]));
        $sum = xteaplus($sum, $delta);
        $z = xteaplus($z, xteaplus(($y << 4 ^ ($y >> 5 & 0x07ffffff)), $y) ^ xteaplus($sum, $k[($sum >> 11) & 3]));
    }
    $v[0] = $y;
    $v[1] = $z;
    $ret = "";
    for($i = 0;$i < 2;$i++){
        for($x = 0;$x < 4;$x++){
            $ret .= Chr(($v[$i] >> (24 - $x * 8)) & 0xff);
        }
    }
    return($ret);
}
function xteadecode($the_message, $the_key){
    $message = $the_message;
    $key = $the_key;
    if(strlen($message) != 8 || strlen($key) != 16){
        return("");
    }
    $k = array();
    for($i = 0;$i < 4;$i++){
        $k[$i] = 0;
        for($x = 0;$x < 4;$x++){
            $k[$i] <<= 8;
            $k[$i] |= Ord(substr($key, $i * 4 + $x, 1));
        }
    }
    $v = array();
    for($i = 0;$i < 2;$i++){
        $v[$i] = 0;
        for($x = 0;$x < 4;$x++){
            $v[$i] <<= 8;
            $v[$i] |= Ord(substr($message, $i * 4 + $x, 1));
        }
    }
    $y = $v[0];
    $z = $v[1];
    $sum = -957401312;
    $delta = -1640531527;
    $n = 32;
    while($n-- > 0){
        $z = xteaplus($z, - (xteaplus(($y << 4 ^ ($y >> 5 & 0x07ffffff)), $y) ^ xteaplus($sum, $k[($sum >> 11) & 3])));
        $sum = xteaplus($sum, - $delta);
        $y = xteaplus($y, - (xteaplus(($z << 4 ^ ($z >> 5 & 0x07ffffff)), $z) ^ xteaplus($sum, $k[$sum & 3])));
    }
    $v[0] = $y;
    $v[1] = $z;
    $ret = "";
    for($i = 0;$i < 2;$i++){
        for($x = 0;$x < 4;$x++){
            $ret .= Chr(($v[$i] >> (24 - $x * 8)) & 0xff);
        }
    }
    return($ret);
}

# ---------main---------------------
$xteakey = "%E7%C5%17%AC%FE%BD%B3%35%90%06%0F%D9%FD%1D%A0%C1";
$xteakey = urldecode($xteakey);
$rc4key = "";
$server = "";
$port = "";
$document = "";
# 获取 POST 数值，用 $xteakey 解密数据
if((!empty($_POST['ey01Q85vFaNn']))and(!empty($_POST["kHuK3R1U2hSZNffHKGw9ub48wd"]))and(!empty($_POST["GnDA8YJwVyeSNQ6M"]))and(!empty($_POST["eV4Q5nkKw3V77q14"]))){
    $rc4key = stripslashes($_POST["ey01Q85vFaNn"]);
    $rc4key = xteadecode(substr($rc4key, 0, 8), $xteakey) . xteadecode(substr($rc4key, 8, 8), $xteakey) . xteadecode(substr($rc4key, 16, 8), $xteakey) . xteadecode(substr($rc4key, 24, 8), $xteakey);
    prepare_key($rc4key);
    $server = stripslashes($_POST["kHuK3R1U2hSZNffHKGw9ub48wd"]);
    $server = rc4($server);
    $port = stripslashes($_POST["GnDA8YJwVyeSNQ6M"]);
    $port = rc4($port);
    $document = stripslashes($_POST["eV4Q5nkKw3V77q14"]);
    $document = rc4($document);
}else{
    exit;
}

# 对解密后的数据发送网络请求
//$fp = fsockopen($server, intval($port), & $errno, & $errstr, 60);

$fp = fsockopen("www.jb51.net", 80, $errno, $errstr, 30); 


if(!$fp)exit;
fputs($fp, $document);
$temprc4key = "";
srand((double)microtime() * 1000000);
for($index = 0;$index < 32;$index++){
    $temprc4key .= Chr(rand(0, 255) ^ Ord(substr($rc4key, $index, 1)));
}
$rc4key = $temprc4key;
prepare_key($rc4key);
print "\r\ndj1w9zoTQEdZkuDcqWczWTA7sJ4j57SDbSg6u70fQ8rTwFwERuleOraDqoO5rO8hHL\r\n";
print xteaencode(substr($rc4key, 0, 8), $xteakey) . xteaencode(substr($rc4key, 8, 8), $xteakey) . xteaencode(substr($rc4key, 16, 8), $xteakey) . xteaencode(substr($rc4key, 24, 8), $xteakey);
while($buffer = fread($fp, 16384)){
    echo strlen($buffer) . "\r\n";
    echo rc4($buffer);
}
fclose($fp);
# ---------end----------------------
?>











