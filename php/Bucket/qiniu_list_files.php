<?php
# http://developer.qiniu.com/docs/v6/api/reference/rs/list.html#list-description
require_once __DIR__ . '/php-sdk-7.2.6/autoload.php';
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;

$accessKey = 'KzBWtGa-Qsxd2zA_SbYkcxi9Evw0fRNgQY5ax9T6';
$secretKey = 'xxxxxx';
$bucket = 'yisuo';
$baseurl = 'http://oold3s5tj.bkt.clouddn.com/';

$auth = new Auth($accessKey, $secretKey);
$bucketManager = new BucketManager($auth);

$prefix = '';              #要列取文件的公共前缀，即是文件目录名
$marker = '';              #上次列举返回的位置标记，作为本次列举的起点信息。
$limit = 100;              #本次列举的条目数
$delimiter = '/';

# 列举文件
list($ret, $err) = $bucketManager->listFiles($bucket, $prefix, $marker, $limit, $delimiter);

echo "<pre>\nList CommonPrefixes and Iterms ====>\n";
# print_r($ret);
# 目录列表
if (array_key_exists('commonPrefixes', $ret)){
    print_r($ret['commonPrefixes']);
}
# 文件列表
foreach($ret['items'] as $v){
    $size = bytesize($v['fsize']);
    $space = bytecomplement($v['key']);
    echo $baseurl . $v['key'] . $space . '[' . $size . "]\r\n";
}

function bytesize($num){
    $bt = pow(1024, 1);
    $kb = pow(1024, 2);
    $mb = pow(1024, 3);
    $gb = pow(1024, 4);
    if(!is_numeric($num)         ) $size = '值不是数字';
    if($num <  0                 ) $size = '值不能小于 0 ';
    if($num >= 0   and $num < $bt) $size = $num . ' B';
    if($num >= $bt and $num < $kb) $size = floor(($num / $bt) * 100) / 100 . ' KB';
    if($num >= $kb and $num < $mb) $size = floor(($num / $kb) * 100) / 100 . ' MB';
    if($num >= $mb and $num < $gb) $size = floor(($num / $mb) * 100) / 100 . ' GB';
    if($num >= $gb               ) $size = floor(($num / $gb) * 100) / 100 . ' TB';
    return $size;
}

function bytecomplement($str){
    $bv = 50;
    $length = strlen($str);
    if($length < $bv){
        $dv = $bv - $length;
        $space = '';
        for($i = 0; $i < $dv; $i++){
            $space .= ' ';
        }
    }else{
        $space = '  ';
    }
    return $space;
}



#------------------------------
if ($err !== null) {
    echo "\n====> list file err: \n";
    var_dump($err);
} else {
    if (array_key_exists('marker', $ret)) {
        echo "Marker:" . $ret["marker"] . "\n";
    }
}


