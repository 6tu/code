<?php
set_time_limit(0);
if (file_exists('bench.html')) {
    echo file_get_contents('./bench.html');
    exit(0);
}
if (!file_exists('superbench.sh')) {
    #$info = exec('wget -q -O ./superbench.sh http://ro.6tu.me/superbench.bak');
    $info = exec('wget -q -O ./superbench.sh http://git.io/superbench.sh');
}
$sh = file_get_contents('./superbench.sh');
$sh_array = explode("\n", $sh);

$sh = '';
foreach($sh_array as $value){
    if(strstr($value, 'run as root')){
        $sh .= '#' . $value . "\n";
    }else{
        $sh .= $value . "\n";
    }
}
file_put_contents('./superbench.sh', $sh);

# $info = exec('bash ./superbench.sh speed', $out1, $rec);
$info = exec('bash ./superbench.sh', $out1, $rec);
$find = '----------------------------------------------------------------------';
$num = array_search($find, $out1) - 1;
$out1 = array_slice($out1, $num);
$num2 = count($out1) -8;
$out1 = array_slice($out1, 0,$num2);
$out1[] = "\r\n";
$info = exec('wget -qO- bench.sh|bash', $out2, $rec);
# $info = exec('wget -q -O bench.sh http://bench.sh');
$num = array_search($find, $out2) + 4;
$out2 = array_slice($out2, $num);
$out2[0] = 'Usage : wget -qO- bench.sh | bash';
$out = array_merge($out1, $out2);
$out = str_replace('[0;36m', '',  $out);
$out = str_replace('[0;33m', '',  $out);
$out = str_replace('[0;32m', '',  $out);
$out = str_replace('[0;31m', '',  $out);
$out = str_replace('[0m',    '',  $out);

$head = '<!DOCTYPE html>
            <html xmlns="http://www.w3.org/1999/xhtml">
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                    <title>服务器的基本参数</title>
                </head>
            <body>
        ';
$pre = "<pre style=\"
            border:none;
            line-height:1.45em;
            font-size:13px;
            vertical-align:baseline;
            background:rgb(0,43,54) none;
            width:670px;
            margin:0 auto;
            text-align:center;
            text-align:left;
            font-family:Menlo, Monaco, 'Andale Mono', 'lucida console', 'Courier New', monospace;
            color:rgb(147,161,161);
       \">\r\n\r\n    服务器的基本参数\r\n\r\n";
echo $head . $pre;
print_r($out);
echo "\r\n</pre>";

# 保存记录到文件
$results = $head . $pre . print_r($out, true) . "\r\n</pre>\r\n</body>\r\n</html>";
file_put_contents('bench.html', $results);
?>
