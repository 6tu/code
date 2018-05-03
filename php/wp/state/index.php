<?php
$static_host = 'ysuo.org';                              # 静态镜像域名，不带http或者https，最后边不加 /
$static_host = $_SERVER['HTTP_HOST'];
$id_url_log = 'url.txt';        # 记录 ID对应 URL的文件

$scheme = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'))?'https://':'http://';
$static_path = $scheme . $static_host . parse_url($_SERVER['PHP_SELF'], PHP_URL_PATH);

# $id_url_path 的数组格式 $arr_url
if(file_exists($id_url_log) == false) die('file is not exists');
$id_url_path = file_get_contents($id_url_log);
$array = explode("\r\n", $id_url_path);
$nz = count($array)-1;
$arr_url = array();
for($z = 0;$z < $nz;$z++){
    $array_url = explode(' ', $array[$z]);
    $arr_url = $arr_url + array($array_url['0'] => $array_url['1']);
}
// print_r($arr_url);
# $arr_url[$_GET['p']] 即是 ID 对应的URL ,这里$static_host/ 后面默认的是 index.php 文件
if(isset($_GET['p'])){
    $static_url = $static_path . '/' . $arr_url[$_GET['p']];
    echo $static_url;
    header('Location:' . $static_url);
}else{
    header('Location:' . $static_paht . '/index.html');
}
