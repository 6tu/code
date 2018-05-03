
<?php
/**
 *web:      echo("usage: " .$_SERVER['PHP_SELF']. "?ip=<ip_address>\r\n<br>");
 *php-cli:  /usr/local/bin/php -f dbip.php <ip_address>
 */

date_default_timezone_set('UTC');

//=========== 分析提交的数据 ===========//
if(isset($argc)){
	if(empty($argv[1])) exit("usage: {$argv[0]} <ip_address>\n");
	else{
		$ip_addr = gethostbyname($argv[1]);
		$host = $ip_addr;
	}
}else if(empty($_GET['ip'])){
	$ip_addr = $_SERVER['REMOTE_ADDR'];
	#$ip_addr = '192.210.192.248';
	$host = $ip_addr;
}else{
	$ip_addr = gethostbyname($_GET['ip']);
    if(!filter_var($ip_addr, FILTER_VALIDATE_IP)) $ip_addr = $_SERVER['REMOTE_ADDR'];
	$host = $_GET['ip'];
}
if($host == $ip_addr) $host = '';
$hostname = gethostbyaddr($ip_addr);

$linefeed = "\r\n";

#　获取数据
$url = 'https://db-ip.com/'.$ip_addr;
$dbip = parse_url($url)['host'] . $linefeed;
$data = getipinfo($url);

$array = explode('<table', $data);
$array = explode('</table>', $array[1]);
$html = '<table' . $array[0] .'</table>';
$html = strip_space_enter($html, $tags = '<tr><th><td>', $n = 10);
# $html = str_replace(array('"',), array('\"',), $html);
$html = str_replace(array(':',), array('',), $html);
$html = str_replace(array('<tr><th>', '</th><td>', '</td></tr>',), array('"', '":"', '",',), $html);
$dbip_array = json2array($html);
$t = explode('UTC', $dbip_array['Timezone']);
$t = substr($t[1], 0, -1);
$dbip_array['Local time'] = date('Y-m-d H:i:s', strtotime("$t hours"));

$url = 'http://geoiplookup.net/ip/'.$ip_addr;
$geoiplookup = $linefeed . parse_url($url)['host'] . $linefeed;
$data = getipinfo($url);

$array = explode('<h2', $data);
$html = '<h2' . $array[1] . '<h2' . $array[2];
$html = stripHtmlTags($tags = 'h2', $html, $content = true);
$html = strip_space_enter($html, $tags = '<div>', $n = 10);
# $html = str_replace(array('"',), array('\"',), $html); // 对 " 转义
$html = str_replace(array(':',), array('',), $html);
$html = str_replace(array('<div class="ipinfo">', '</div><div class="ipdata">', '</div>'), array('"', '":"', '",',), $html);
$geoiplookup_array = json2array($html);

unset($url);
unset($data);
unset($array);
unset($html);

$data = array("ip" => $ip_addr);
$url = 'http://www.ipip.net/ip/ajax/';
$ipip = $linefeed . parse_url($url)['host'] . $linefeed;
$result = http($url, $data, $json = false);
$result = preg_replace('#<script[^>]*?>.*?<\/script\s*>#si', '', $result);
$array1 = explode('<table', $result);
$array2 = explode('</table>', $array1[2]);

$html = strip_tags('<table' . $array1[1], '<span>');
$html .= strip_tags('<table' . $array2[0] .'</table>', '<td>');
$html = strip_space_enter($html, $tags = '<span><td>', $n = 50);
$html = strip_space_enter($html, $tags = '<span><td>', $n = 30);
$html = str_replace(array('</span>', '<td>',), array("</span>\r\n", "\r\n<td>"),  $html);

$array = explode("\r\n", $html);
foreach($array as $v){
	if(strstr($v, '<span id="myself">')) $iplocation = $v;
}
$iplocation = str_replace(array('</span>', '<span id="myself">'), array(""),  $iplocation);

$html = str_replace(array('</td>', "\r\n"), array(""),  $html);
$array = explode('<td>', $html);
$ipip_array = array('IPlocation' => $iplocation, 'ASN' => $array[1], 'IPsegment' => $array[2], 'ISP' => $array[3]);


unset($url);
unset($data);
unset($html);
unset($array);
unset($result);
unset($array1);
unset($array2);

# 打印数据到客户机
$tdpre = '<tr><td style="width:150px;"><pre>';
$tdoff = '</pre></td></tr>';
$td = '</td><td style="width:5px;"></td><td style="width:500px;"><pre>';

$html = html_form($ip_addr);
//--------------------------------//
$html .= '<div id="content"><br><b>从' .$geoiplookup. '查找的结果</b><br>';
$html .= '<table>';
foreach($geoiplookup_array as $key => $value){
	$html .= $tdpre . $key . $td . $value . $tdoff;;
}
$html .= '</table></div>';

//--------------------------------//
$html .= '<div id="content"><br><b>从' .$dbip. '查找的结果</b><br>';
$html .= '<table>';
foreach($dbip_array as $key => $value){
	$html .= $tdpre . $key . $td . $value . $tdoff;;
}
$html .= '</table></div>';
//--------------------------------//
$html .= '<div id="content"><br><b>从' .$ipip. '查找的结果</b><br>';
$html .= '<table>';
foreach($ipip_array as $key => $value){
	$html .= $tdpre . $key . $td . $value . $tdoff;;
}
$html .= '</table></div>';
//--------------------------------//

$html .= '<center><br><br><hr width=60%><br><br></body></html>';
echo ($html);
//echo beautify_html($html);



/**
 * 以下用 API 的方式索取数据
 *
 * //=========== 获取 db-ip 的数据 ===========//
 * require "dbip-client.class.php";
 * $api_key = "f7d40841c01f728373219b59691bc3d50028a2d7";
 * 
 * try {
 * 	$dbip = new DBIP_Client($api_key);
 * 	$keyinfo = $dbip->Get_Key_Info();
 * 	$keyinfo = object2array($keyinfo);
 * 	$addrinfo = $dbip->Get_Address_Info($ip_addr);
 * 	$addrinfo = object2array($addrinfo);
 * 	
 * 	if(isset($argc)){
 * 		echo "keyinfo:".$linefeed;
 * 		foreach ($dbip->Get_Key_Info() as $k => $v){
 * 			echo "{$k}: {$v}".$linefeed;
 * 		}
 * 	}else{
 * 		if($keyinfo['queriesLeft'] == 0){
 * 			echo $linefeed. '今天已经使用了' .$keyinfo['queriesPerDay'];
 * 			header("refresh:3; url=http://db-ip.com");
 * 			exit();
 * 		}
 * 	}
 * 	if(isset($argc)){
 * 		echo "addrinfo:".$linefeed;
 * 		foreach ($dbip->Get_Address_Info($ip_addr) as $k => $v) {
 * 			echo "{$k}: " . (is_array($v) ? implode(", ", $v) : $v) . $linefeed;
 * 		}
 * 		exit;
 * 	}
 * //=========== 获取 geoiplookup 的数据 ===========//
 * 	$geoiplookup = 'http://api.geoiplookup.net/?query=' . $ip;
 * 	$ipinfo = getipinfo($geoiplookup);
 * 	$data = xml_parser($ipinfo);
 * 	$ipinfo = $data['results']['result'];
 * //=========== 获取 ipip 的数据 ===========//
 * 	$ipip = 'http://freeapi.ipip.net/?ip=' . $ip . '&lang=EN';
 * 	$ipinfo = getipinfo($ipip);
 * 	$ipinfo = json_decode($ipinfo);
 * 
 * }catch (Exception $e){
 * 	die("error: {$e->getMessage()}\n");
 * }
 *
 */
/*
$ip = $addrinfo['ipAddress'];
$continentcode = $addrinfo['continentCode'];
$continent = $addrinfo['continentName'];
$countrycode = $addrinfo['countryCode'];
$countryname = $addrinfo['countryName'];
$region = $addrinfo['stateProv'];
$city = $addrinfo['city'];
$isp = $addrinfo['isp'];
$latitude = $addrinfo['latitude'];
$longitude = $addrinfo['longitude'];
$timezone = '';
$record=geoip_record_by_name($ip);
$timezone = geoip_time_zone_by_country_and_region($record['country_code'], $record['region']);
$t = explode('/', $timezone);
$continent = $t[0];
*/


/************** 函数区，无需修改 ************/

# JSON 转数组
function json2array($html){
	$obj = json2object($html);
	$html = object2array($obj);
	return $html;
}

# JSON 转 object
function json2object($html){
    $html = rtrim($html, ",");
	$html = str_replace(array('/',), array('\/',), $html); // 增加需要转义的字符
    $html = '{'. $html .'}';
    $html = json_decode($html);
	return $html;
}

# object 转数组
function object2array(&$object){
	$object = json_decode(json_encode($object),true);
	return $object;
}

# 解析 XML
function xml_parser($str){ 
    $xml_parser = xml_parser_create(); 
    if(!xml_parse($xml_parser,$str,true)){ 
        xml_parser_free($xml_parser); 
        return false; 
    }else { 
        return (json_decode(json_encode(simplexml_load_string($str)),true)); 
    } 
}

# 删除空格和回车
function strip_space_enter($html, $tags, $n){
	$html = strip_tags($html, $tags);
	$html = trim($html);
    for($i = 0; $i < $n; $i++){
        $html = str_replace(array(" \r\n", " \n", ' <', '> '), array("\r\n", "\n", '<', '>'), $html);
    }
    $html = str_replace(array("\r\n", "\n", "\r", '&nbsp;', "\t",), array(''), $html);
	return $html;
}

# 删除指定标签
function stripHtmlTags($tags, $str, $content = true)
{
    $html = [];
    // 是否保留标签内的text字符
    if($content){
        foreach (array($tags) as $tag) {
            $html[] = '/(<' . $tag . '.*?>(.|\n)*?<\/' . $tag . '>)/is';
        }
    }else{
        foreach (array($tags) as $tag) {
            $html[] = "/(<(?:\/" . $tag . "|" . $tag . ")[^>]*>)/is";
        }
    }
    $data = preg_replace($html, '', $str);
    return $data;
}

# form 表单
function html_form($ip){
    $title = '查找的IP为' . $ip;

	$html = '';
    $html .= '<html><head><meta http-equiv="content-type" content="text/html;charset=utf-8">';
    $html .= '<title>' . $title . '</title>';
    $html .= '<style type="text/css">';
    $html .= 'pre{border:dashed 1px green;padding:6px; background-color:#C1CDCD;color:#000000; font-size:15px}';
    $html .= 'td{padding:2px;}';
    $html .= '#search {width:660px;float:center;padding:1px;}';
    $html .= '.searchbox {width:570px;padding:4px;font-size: 1em;}';
    $html .= '.searchbtn {width:90px;padding:4px;background-color:green;border: 0;font-size: 16px;color:#000000;}';
    $html .= '#content {float:center;}';
    $html .= '</style></head>';
    
    $html .= '<body><br><center><div id="search">';
    $html .= '<b> 查找 IP 地址</b>';
    $html .= '<form  method="get" action="?ip="  id="searchform" onsubmit="return false;">';
    $html .= '<input class="searchbox" type="text" name="ip" value="g.cn" />';
    $html .= '<input class="searchbtn" type="submit" onclick="window.location.href=this.form.action + this.form.ip.value;" />';
    $html .= '</form></div>';
	
    return $html;
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

# 用curl模拟post发送json数据
# http://blog.csdn.net/pangchengyong0724/article/details/52103962
function http($url, $data, $json){
	
	$ref = parse_url($url)['scheme'] . '://' . parse_url($url)['host'] . '/';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_REFERER, $ref);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_ACCEPT_ENCODING, "gzip");
    if(!empty($data)){
        if($json && is_array($data)){
            $data = json_encode($data);
        }
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        if($json){
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
							'Content-Type: application/x-www-form-urlencoded; charset=utf-8', 
							'Content-Length:' . strlen($data)
							)
						);
        }
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    $errorno = curl_errno($ch);
    if($errorno){
        return array('errorno' => false, 'errmsg' => $errorno);
    }
    curl_close($ch);
	return $res;
    //return json_decode($res, true);
}

# HTML 格式化
function beautify_html($html){
    $tidy_config = array(
        'clean' => false,
        'indent' => true,
        'indent-spaces' => 4,
        'output-xhtml' => false,
        'show-body-only' => false,
        'wrap' => 0
        );
    if(function_exists('tidy_parse_string')){ 
        $tidy = tidy_parse_string($html, $tidy_config, 'utf8');
        $tidy -> cleanRepair();
        return $tidy;
    }
    else return $html;
}


