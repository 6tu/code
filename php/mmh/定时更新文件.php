<?php
# 每小时更新一次文件，对比文件的sha512之后更新文件
# 302 重定向
$mhurl = 'http://mh/mh/articles/2018/8/23/2018-8-23-t.zip';
$fn = '2018-8-23-t.zip';
$mhdata = file_get_contents($mhurl);

# 通过buffer函数读取二进制流内容
$finfo = new finfo(FILEINFO_MIME_TYPE);
$file_type = $finfo->buffer($mhdata);
if(!strpos($file_type,'zip')){
    echo '获取数据无效，当前数据类型是 ' . $file_type ;
    exit(1);
}

$hashed = hash('sha512', $mhdata);
// echo '<pre>'.$hashed."\r\n";

# 判断文件是否存在
if (file_exists($fn.'.asc')) {
    #  echo "The file $fn exists";
    $asc = file_get_contents($fn.'.asc');
}else{
    # echo "The file $fn does not exist";
    $asc = '';
}

# 对比值之后，更新文件 
if (strcmp($asc,$hashed) !== 0) {
    file_put_contents($fn.'.asc',$hashed);
    file_put_contents($fn,$mhdata,LOCK_EX);
    
    # 这里更新远程文档
    echo '文档已更新';
}else{
    if (!file_exists($fn)) file_put_contents($fn,$mhdata,LOCK_EX);
    echo '文档无需更新';
}


