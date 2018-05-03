<?php
#
# SolusVM 控制面板 
# 端口一般为 5656
#
# 官方档案
# https://documentation.solusvm.com/display/DOCS/PHP+Code+Examples
# https://documentation.solusvm.com/display/DOCS/Functions
#
# 查询值 action / key / hash
# action 支持 GET 和 POST 调用方法
# action 参数: reboot / boot / shutdown / status / info
# info 参数含有的标志: ipaddr / hdd / mem / bw ,设置为true将返回结果
# GET 用法 command.php?action=info&hdd=true&bw=true&key=apikey&hash=apihash
# 
# 样品 https://github.com/liamjack/SolusVM-Status
#


$host = "https://vps.host.ltd/api/client/command.php";
$apikey = "GSDQ-WDWEC-YIQ21";
$apihash = "66abd6e26bdb5dasdfweasddg63c55c3bc306552";

$url = $host . '?action=info&bw=true&key=' . $apikey . '&hash=' . $apihash;

$data = getipinfo($url);

preg_match_all('/<(.*?)>([^<]+)<\/\\1>/i', $data, $match);
$result = array();
foreach ($match[1] as $x => $y) 
{
	$result[$y] = $match[2][$x];
}

if (isset($result["bw"])){
    $bw = $result['bw'];
    $array_bw = explode(',', $bw);
}
$bwtotal = round($array_bw[0]/pow(1024, 3), 2) .' GB';
$bsused =  round($array_bw[1]/pow(1024, 3), 2) .' GB';
echo $bsused .'/'. $bwtotal;




# 用 CURL 获取网页内容
function getipinfo($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
	if($httpCode == 404) return false;
    else{
        return $data;
	}
}
<?php
#
# SolusVM 控制面板 
# 端口一般为 5656
#
# 官方档案
# https://documentation.solusvm.com/display/DOCS/PHP+Code+Examples
# https://documentation.solusvm.com/display/DOCS/Functions
#
# 查询值 action / key / hash
# action 支持 GET 和 POST 调用方法
# action 参数: reboot / boot / shutdown / status / info
# info 参数含有的标志: ipaddr / hdd / mem / bw ,设置为true将返回结果
# GET 用法 command.php?action=info&hdd=true&bw=true&key=apikey&hash=apihash
# 
# 样品 https://github.com/liamjack/SolusVM-Status
#


$host = "https://myvm.hiformance.com/api/client/command.php";
$apikey = "8WZU6-TR5AB-6ITI5";
$apihash = "15ceb6944c9a99abeeebb58b559c4faf300df232";

$url = $host . '?action=info&bw=true&hdd=true&mem=true&key=' . $apikey . '&hash=' . $apihash;

$data = getipinfo($url);
preg_match_all('/<(.*?)>([^<]+)<\/\\1>/i', $data, $match);
$result = array();
print_r($result);
foreach ($match[1] as $x => $y) {
	$result[$y] = $match[2][$x];
}
#print_r($result);
if(isset($result['ipaddress'])) $ip = $result['ipaddress'];
if(isset($result['hostname']))  $host = $result['hostname'];
if(isset($result['bw']))        $bw = vpsinfo($result['bw']);
if(isset($result['hdd']))       $hdd= vpsinfo($result['hdd']);
if(isset($result['mem']))       $mem= vpsinfo($result['mem']);

$ip = '<a href=https://wq.apnic.net/static/search.html?query=' . $ip .'>'. $ip .'</a>';

echo "<br><br><center><table>\r\n";
echo "<tr><td>IP地址: </td><td>". $ip ."</td></tr>\r\n";
echo "<tr><td>硬 盘 : </td><td>". $hdd ."</td></tr>\r\n";
echo "<tr><td>内 存 : </td><td>". $mem ."</td></tr>\r\n";
echo "<tr><td>流 量 : </td><td>". $bw ."</td></tr>\r\n";
echo "</table></center>\r\n";

function vpsinfo($str){
        $array = explode(',', $str);
        $total = @round($array[0]/pow(1024, 3), 2) .' GB';
        $used =  @round($array[1]/pow(1024, 3), 2) .' GB';
        return $used .'/'. $total;
}

# 用 CURL 获取网页内容
function getipinfo($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
	if($httpCode == 404) return false;
    else{
        return $data;
	}
}
