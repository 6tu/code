<?php
# system() 输出结果，返回结果的最后一行，第二参数为状态码
# exec() 不输出结果，返回结果的最后一行，第三参数为状态码，第二参数把完整的结果追加到array数组中
# 状态码，0 或者空

echo "<pre>\r\n";
//$lastline = system('netstat -aolptun', $rc);
//echo "\r\n";
$lastline = exec('netstat -aolptun', $res, $rc);
//echo $lastline . $rc;
//print_r($res);

$c = '';
foreach ($res as $v) {
    if( strpos($v, '127.0.0.1') == false and 
        strpos($v, '8.8.8.8') == false and 
        strpos($v, '0.0.0.0') == false and 
        strpos($v, '185.247.62.30:80') == false) $c .= $v . "\n";
}
echo "".$c."\n\n";
system("netstat -n | awk '/^tcp/ {++S[\$NF]} END {for(a in S) print a, S[a]}'");
