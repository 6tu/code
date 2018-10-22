<?php
# http://jackxiang.com/post/7237/
# system() 输出结果，返回结果的最后一行，第二参数为状态码
# exec() 不输出结果，返回结果的最后一行，第三参数为状态码，第二参数把完整的结果追加到array数组中
# 状态码，0 或者空


echo "<pre>\n";

$uptime = trim(shell_exec('uptime'));
// output is 04:47:32 up 187 days,  5:03,  1 user,  load average: 0.55, 0.55, 0.54
$uptime = explode(',', $uptime);
$uptime = explode(' ', $uptime[0]);
if(empty($uptime[3])) $uptime[3] = '';
$uptime = $uptime[2] . ' ' . $uptime[3]; // 187 days
echo "<b>运行时间 ( $uptime)：</b>\n";
echo trim(shell_exec('last reboot | head -1')) . "\n";
echo trim(shell_exec('who -b')) . '  当前时间(含时区) ' . trim(shell_exec('date -R')) . ' 运行时长 ' . $uptime . "<br>\n";

$str = shell_exec("more /proc/meminfo");
$mode = "/(.+):\s*([0-9]+)/";
preg_match_all($mode, $str, $arr);
$used = round($arr[2][0] / 1024 - $arr[2][1] / 1024);
$total = ($arr[2][0] / 1024);
$mempr = floor($used / $total * 100) / 100 * 100 . "%";
echo "<b>内存用量( $mempr)：</b>\n";
echo (shell_exec('free -mh')) . "\r\n";

$fp = popen('df -lh | grep -E "^(/)"', "r");
$rs = fread($fp, 1024);
pclose($fp);
$rs = preg_replace("/\s{2,}/", ' ', $rs); //把多个空格换成 “_”
$hd = explode(" ", $rs);
$hd_avail = trim($hd[3], 'G'); //磁盘可用空间大小 单位G
$hd_usage = trim($hd[4], '%'); //挂载点 百分比
unset($rs);
// print_r($hd);
echo "<b>硬盘用量 ( $hd[4])：</b>\n";
echo trim(shell_exec('df -lh')) . "<br>\r\n";

$str = shell_exec('more /proc/stat');
$pattern = "/(cpu[0-9]?)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)/";
preg_match_all($pattern, $str, $out);
$cpu = count($out[1]);
unset($pattern);
unset($str);
unset($out);
echo "<b>CPU 用量 ( $cpu 个CPU)：</b>\n";
echo shell_exec('top -b -n 2 | grep -E "^(Cpu|Tasks)"') . "\r\n";

$str = shell_exec('more /proc/net/dev');
$pattern = "/(venet[0-9]+):\s*([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)\s+([0-9]+)/";
preg_match_all($pattern, $str, $out);
$net_name = $out[1][0];
$net_in = bytesize($out[3][0]);
$net_out = bytesize($out[11][0]);
echo "<b>网卡流量( $net_name)：</b>\n";
echo "接收 $net_in  发送 $net_out \n";
echo "后台统计 2,653,048 MB (2018/10/22 09:10[Europe/Bucharest,GMT +03]) <br>\n";
// echo "后台统计 2,634,853 MB (2018/10/21 09:11[Europe/Bucharest,GMT +03]) <br>\n";

echo "<b>TCP 链接概况：</b>\n";
system("netstat -n | awk '/^tcp/ {++S[\$NF]} END {for(a in S) print a, S[a]}'");
echo "<br>\n";

echo "<b>网络连接详细情况：</b>\n";
$lastline = exec('netstat -aolptun', $res, $rc);
// echo $lastline . $rc;
// print_r($res);
# 过滤特殊的IP
$c = '';
foreach ($res as $v){
    if( strpos($v, '127.0.0.1') == false and
        strpos($v, '8.8.8.8') == false and
        strpos($v, '0.0.0.0') == false and
        strpos($v, '185.247.62.30:80') == false) $c .= $v . "\n";
}

# 提出特定端口
$rr = explode("\n", $c);
$cc = '';
$ccc = '';
foreach ($rr as $vv){
     if(strpos($vv, ':11269') == false) $cc .= $vv . "\n";
     else $ccc .= $vv . "\n";
    }
$c = $cc . $ccc;

echo "" . $c . "<br></pre>\n\n";

# 单位转换和字符长度补齐
function bytesize($num){
    $bt = pow(1024, 0);
    $kb = pow(1024, 1);
    $mb = pow(1024, 2);
    $gb = pow(1024, 3);
    if(!is_numeric($num)) $size = '值不是数字';
    if($num < 0) $size = '值不能小于 0 ';
    if($num >= 0 and $num < $bt) $size = $num . ' B';
    if($num >= $bt and $num < $kb) $size = floor(($num / $bt) * 100) / 100 . ' KB';
    if($num >= $kb and $num < $mb) $size = floor(($num / $kb) * 100) / 100 . ' MB';
    if($num >= $mb and $num < $gb) $size = floor(($num / $mb) * 100) / 100 . ' GB';
    if($num >= $gb) $size = floor(($num / $gb) * 100) / 100 . ' TB';
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
