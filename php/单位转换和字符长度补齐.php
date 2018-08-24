<?php
# 单位转换和字符长度补齐

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
