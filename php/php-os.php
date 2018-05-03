
<?php
header('Access-Control-Allow-Origin:*');
$ipinfo = @gethostbyname($_SERVER['SERVER_NAME']) . '(' . $_SERVER['SERVER_ADDR'] . ') - ';
if(PHP_OS == 'Linux'){
    $kernel = substr(php_uname('r'), 0, stripos(php_uname('r'), ' - '));
    $os = file_get_contents('/etc/issue');
    $os = str_replace(array("\r\n", "\n", '\n', '\l'), '', $os);
    $os = trim($os) . ' ' . $kernel;
}else{
    $os = php_uname('s') . ' ' . php_uname('r');
}
$os_info = $ipinfo . $os . ' - ' . $_SERVER['SERVER_SOFTWARE'];
//echo  $os_info;
echo '<pre>';
$sys_info = php_uname();
if(strtolower(substr($sys_info, 0, 5)) == "linux") echo "this is a linux system.\n";
echo "Host:" . php_uname('n') . "\n";


if(false !== ($strs = @file("/proc/net/dev"))){
    $key = '';
    $value = '';
    foreach($strs as $k => $v){
        if(strstr($v, 'eth0', true)){
            $value .= $v;
            $key .= $k;
            $nic = 'eth0';
        }elseif(strstr($v, 'venet0', true)){
            $value .= $v;
            $key .= $k;
            $nic = 'venet0';
        }
    }
}

echo $key ."\n" . $nic;

