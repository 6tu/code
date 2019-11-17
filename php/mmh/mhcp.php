<?php
date_default_timezone_set ("Etc/GMT+6");  #比林威治标准时间慢6小时
// echo date("Y-n-j", time());
$current_date = date("Y-n-j", time());

$path = './mhdata';
$hash_algorithm = 'sha512';

$hash_extension = '.' . $hash_algorithm;

// 读取目录文件。is_dir是否为目录
$temp = scandir($path);
foreach($temp as $v){
    $fn = $path . '/' . $v;
    if(is_file($fn) == false) continue;
    // echo "\r\n<br>".$fn;

    // 提取日期，建立文件夹
    if(strpos($v, $hash_extension)){
        $array = explode('-', $v);
        if(strlen($array[1]) == 1) $array[1] = '0' . $array[1];
        $date = $array[0] . $array[1];
        // print_r($array);
        // echo "\r\n<br>".$date;
        $date_dir = $path . '/archives/' . $date;
        $log_dir = $date_dir . '/log-update';
        $hash_dir = $date_dir . '/' . $hash_algorithm;
        if(!is_dir($hash_dir)){
            mkdir(iconv("UTF-8", "GBK", $hash_dir), 0777, true);
        }
        if(!is_dir($log_dir)){
            mkdir(iconv("UTF-8", "GBK", $log_dir), 0777, true);
        }
        // 组合文件名，移动文件
        $fn_base = $array[0] . '-' . intval($array[1]) . '-' . $array[2];
        if($fn_base === $current_date){
            echo "\r\n<br> 无需复制最新文件\r\n<br>";
            continue;
        }
        $fn_hash = $fn_base . '-t.zip' . $hash_extension;
        $fn_p7m = 'p7m_' . $fn_base . '-t.zip.b64.zip';
        $fn_log = $fn_base . '-t.zip_update.log';
        copy($path . '/' . $fn_p7m, $date_dir . '/' . $fn_p7m);
        copy($path . '/' . $fn_hash, $hash_dir . '/' . $fn_hash);
        copy($path . '/log/' . $fn_log, $log_dir . '/' . $fn_log);
        unlink($path . '/' . $fn_p7m);
        unlink($path . '/' . $fn_hash);
        unlink($path . '/log/' . $fn_log);
    }
}
echo "\r\n<br> 文件整理完毕 done \r\n<br>";


