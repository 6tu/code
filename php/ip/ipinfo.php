<?php
# http://ipinfodb.com/

if(empty($_GET['ip'])) $ip = $_SERVER['REMOTE_ADDR'];
else{
	$ip = gethostbyname($_GET['ip']);
    if(!filter_var($ip, FILTER_VALIDATE_IP)) $ip = $_SERVER['REMOTE_ADDR'];
}

$url = 'http://api.geoiplookup.net/?query=' . $ip;
$ipinfo = getipinfo($url);
$ip = $ipinfo['ip'];
$host = $ipinfo['host'];
$isp = $ipinfo['isp'];
$city = $ipinfo['city'];
$countrycode = $ipinfo['countrycode'];
$countryname = $ipinfo['countryname'];
$latitude = $ipinfo['latitude'];
$longitude = $ipinfo['longitude'];

$record=geoip_record_by_name($ip);
$timezone = geoip_time_zone_by_country_and_region($record['country_code'], $record['region']);
$t = explode('/', $timezone);
$continent = $t[0];


$title = '查找的IP为' . $ip;
$tdpre = "<tr><td><pre>";
$tdoff = "</pre></td></tr>";
$td = '</td><td> </td><td><pre>';

$html = '<html><head><meta http-equiv="content-type" content="text/html;charset=utf-8">';
$html .= '<title>' .$title. '</title>';
$html .= '<style type="text/css">';
$html .= 'pre{border:dashed 1px green;padding:8px; background-color:#C1CDCD;color:#000000; font-size:15px}';
$html .= 'td{padding:2px;}';
$html .= '#search {width:300px;float:center;padding:1px;}';
$html .= '.searchbox {width:240px;padding:4px;font-size: 1em;}';
$html .= '.searchbtn {width:60px;padding:4px;background-color:green;border: 0;font-size: 16px;color:#000000;}';
$html .= '#content {float:center;}';
$html .= '</style></head>';

$html .= '<body><br><center><div id="search">';
$html .= '<b> 查找 IP 地址</b>';
$html .= '<form  method="get" action="?ip="  id="searchform" onsubmit="return false;">';
$html .= '<input class="searchbox" type="text" name="ip" value="g.cn" />';
$html .= '<input class="searchbtn" type="submit" onclick="window.location.href=this.form.action + this.form.ip.value;" />';
$html .= '</form></div>';

$html .= '<div id="content"><br><b>' .$title. '</b><br>';
$html .= '<table width=300px>' . $tdpre . 'IP Address:' . $td . $ip . $tdoff;
$html .= $tdpre . 'Hostname:' . $td . $host . $tdoff;
$html .= $tdpre . 'ISP:' . $td . $isp . $tdoff;

$html .= $tdpre . 'Continent:' . $td . $continent . $tdoff;
$html .= $tdpre . 'Country:' . $td . $countryname . '(' .$countrycode. ')' . $tdoff;
$html .= $tdpre . 'City:' . $td . $city . $tdoff;
$html .= $tdpre . 'Timezone:' . $td . $timezone . $tdoff;
$html .= $tdpre . 'Latitude:' . $td . $latitude . $tdoff;
$html .= $tdpre . 'Longitude:' . $td . $longitude . $tdoff . '</table>';
$html .= '</div><center></body></html>';

echo ($html);
//echo beautify_html($html);

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
        $xml = simplexml_load_string($data);
        $array = (array)$xml;
        $array = (array)$array['results'];
        $res = (array)$array['result'];
        return $res;
	}
}

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


