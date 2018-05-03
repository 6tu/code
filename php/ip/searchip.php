<?php


header("Content-type:text/html; charset=utf-8");
set_time_limit(0);
ini_set('memory_limit', '512M');

$ip = '111.50.48.85';
$ip = '103.234.240.5';
$ip = '91.227.79.6';
//$ip = '3.227.79.6';
//$ip = '216.146.5.0';
//$ip = '192.0.2.1';
//$ip = '255.255.255.255';

if($ip == '255.255.255.255') echo $cn = 'Broadcast';
else echo check_ip($ip);

$cn = 'CN';
echo check_cn($cn);




# 用国家代码查询IP 
function check_cn($cn){
	$json = array(
			'./json/ripencc-small.json', 
			'./json/apnic.json', 
			'./json/arin.json', 
			'./json/ripencc.json', 
			'./json/afrinic.json', 
			'./json/lacnic.json',
			);

	# Antarctica //南极洲
	$continent = country_array($cn)[1];
	if($continent == 'Asia') $json = $json[1];
	if($continent == 'Oceania') $json = $json[1];
	if($continent == 'North America') $json = $json[2];
	if($continent == 'Africa') $json = $json[4];
	if($continent == 'South America') $json = $json[5];
	if($continent == 'Europe'){
		$json = $json[3];
		$json2 = $json[0];
	}
	
	$cn = strtoupper($cn);
	if (!file_exists($json)) die(' json file does not exist');
    	$tb = json_decode(file_get_contents($json),true);
	$cn_array = array_search_re($cn, $tb);
	$fn = $cn . '-ip-segment.txt';
	@unlink($fn);
	foreach($cn_array as $c){
		$cnip = $c[2] .'.'. $c[3] .'.'. $c[4] . ".0\r\n";
		if (is_writable($fn)) file_put_contents($fn, $cnip, FILE_APPEND);
		else die('write false');
    	}
}

#查询 IP 属地
function check_ip($ip){
	$json = array(
			'./json/ripencc-small.json', 
			'./json/apnic.json', 
			'./json/arin.json', 
			'./json/ripencc.json', 
			'./json/afrinic.json', 
			'./json/lacnic.json',
			);

    $xip = explode('.', $ip);
    $ip3 = $xip[0] . '.' . $xip[1] . '.' . $xip[2];
	
	if(special_array($ip)) $cn = 'private' . special_array($ip);
    elseif(aip_array($xip[0])) $cn = aip_array($xip[0]);
    elseif(rip_small_array($ip3)){
        $small = json_decode(file_get_contents($json[0]), true);
        foreach($small as $c){
            if(isset($c[$xip[0]][$xip[1]][$xip[2]][$xip[3]])) $cn = $c[$xip[0]][$xip[1]][$xip[2]][$xip[3]];
        }
	#$json[2]
    }elseif(normal_array($ip, $json)) $cn = normal_array($ip, $json);
    else $cn = 'error';
	
	$country = country_array($cn)[0];
	$continent = country_array($cn)[1];
	if($cn == 'reserved'){
		$continent = 'North America';
		$country = 'No country';
	}
	if(strpos($cn, 'private')){
		$continent = 'Reserved ip address';
		$country = 'Reserved';
	}
	if($cn == 'error'){
		$continent = 'error';
		$country = 'error';
	}
	return $ip . ' => ' . $continent . ' => ' . $cn . ' [' . $country . ']';
}

function normal_array($ip, $json){
    $ip_addr = explode('.', $ip);
    if(count($ip_addr) < 4) return false;
    $a1 = (int)$ip_addr[0];
    $a2 = (int)$ip_addr[1];
    $a3 = (int)$ip_addr[2];
    $a4 = (int)$ip_addr[3];
     for($i = 0;$i < 6; $i++){
		if(!file_exists($json[$i]))die('this json file does not exist');
		$tb = json_decode(file_get_contents($json[$i]), true);
		# file_put_contents('test.php',print_r($tb,true));
		foreach($tb as $cn){
			if(isset($cn[$a1][$a2][$a3])){
				return $cn[$a1][$a2][$a3];
				break;
				exit;
			}
		}
		unset($cn);
		foreach($tb as $cn){
			if(isset($cn[$a1][$a2][0])){
				return join($cn[$a1][$a2][0]);
				break;
				exit;
			}
		}
     }
}

function aip_array($key){
    $large = array(
				'3' => 'US', '4' => 'US', '6' => 'US', '7' => 'US', '8' => 'US', '9' => 'US', '11' => 'US', '12' => 'US', 
				'15' => 'US', '16' => 'US', '17' => 'US', '19' => 'US', '21' => 'US', '22' => 'US', '26' => 'US', '28' => 'US', 
				'29' => 'US', '30' => 'US', '33' => 'US', '38' => 'US', '44' => 'US', '48' => 'US', '55' => 'US', '56' => 'US', 
				'73' => 'US', '214' => 'US', '215' => 'US', '25' => 'GB', '53' => 'DE', '57' => 'BE', '126' => 'JP', '133' => 'JP',
				);
    if(array_key_exists($key, $large)) return $large[$key];
    else return false;
}

function rip_small_array($value){
    $small = array(
				'91.227.79', '193.34.192', '193.34.193', '193.34.195', '193.34.196', '193.34.197', '193.34.198', '193.34.199', 
				'193.34.200', '193.34.201', '193.34.203', '193.43.0', '193.58.0', '193.164.232', '193.188.134', '193.192.12', 
				'193.192.15', '193.201.144', '193.201.145', '193.201.146', '193.201.147', '193.201.148', '193.201.149', '193.201.150', 
				'193.201.151', '193.201.152', '193.201.154', '193.201.155', '193.201.156', '193.201.157', '193.201.159', '193.218.205', 
				'193.218.207', '193.243.183', '193.254.23', '194.42.47', '194.42.55', '194.55.1', '194.93.123', '194.117.50', 
				'194.117.52', '194.117.53', '194.117.54', '194.117.55', '194.153.152', '194.153.153', '194.153.154', '194.153.156', 
				'194.153.157', '194.153.158', '194.153.159', '194.153.215', '194.180.159', '194.180.226', '194.246.39', '195.13.46', 
				'195.35.104', '195.60.80', '195.60.81', '195.60.82', '195.60.83', '195.60.84', '195.60.85', '195.60.87', 
				'195.60.88', '195.60.89', '195.60.90', '195.60.91', '195.60.92', '195.60.93', '195.60.94', '195.60.95', '156.67.6',
				);
    if(in_array($value, $small)) return true;
    else return false;
}

function special_array($ip){
	$special = array(
			'0' => 'Software', 
			'10' => 'localcommunications', 
			'127' => 'local host Loopback',
			'224' => 'Internet Reserved for multicast',
			'225' => 'Internet Reserved for multicast',
			'226' => 'Internet Reserved for multicast',
			'227' => 'Internet Reserved for multicast',
			'228' => 'Internet Reserved for multicast',
			'229' => 'Internet Reserved for multicast',
			'230' => 'Internet Reserved for multicast',
			'231' => 'Internet Reserved for multicast',
			'232' => 'Internet Reserved for multicast',
			'233' => 'Internet Reserved for multicast',
			'234' => 'Internet Reserved for multicast',
			'235' => 'Internet Reserved for multicast',
			'236' => 'Internet Reserved for multicast',
			'237' => 'Internet Reserved for multicast',
			'238' => 'Internet Reserved for multicast',
			'239' => 'Internet Reserved for multicast',
			'240' => 'Internet Reserved for future use',
			'241' => 'Internet Reserved for future use',
			'242' => 'Internet Reserved for future use',
			'243' => 'Internet Reserved for future use',
			'244' => 'Internet Reserved for future use',
			'245' => 'Internet Reserved for future use',
			'246' => 'Internet Reserved for future use',
			'247' => 'Internet Reserved for future use',
			'248' => 'Internet Reserved for future use',
			'249' => 'Internet Reserved for future use',
			'250' => 'Internet Reserved for future use',
			'251' => 'Internet Reserved for future use',
			'252' => 'Internet Reserved for future use',
			'253' => 'Internet Reserved for future use',
			'254' => 'Internet Reserved for future use',
			'255' => 'Internet Reserved for future use',
			'169.254' => 'Subnet',
			'100.64' => 'carrier-grade NAT',
			'100.65' => 'carrier-grade NAT',
			'100.66' => 'carrier-grade NAT',
			'100.67' => 'carrier-grade NAT',
			'100.68' => 'carrier-grade NAT',
			'100.69' => 'carrier-grade NAT',
			'100.70' => 'carrier-grade NAT',
			'100.71' => 'carrier-grade NAT',
			'100.72' => 'carrier-grade NAT',
			'100.73' => 'carrier-grade NAT',
			'100.74' => 'carrier-grade NAT',
			'100.75' => 'carrier-grade NAT',
			'100.76' => 'carrier-grade NAT',
			'100.77' => 'carrier-grade NAT',
			'100.78' => 'carrier-grade NAT',
			'100.79' => 'carrier-grade NAT',
			'100.80' => 'carrier-grade NAT',
			'100.81' => 'carrier-grade NAT',
			'100.82' => 'carrier-grade NAT',
			'100.83' => 'carrier-grade NAT',
			'100.84' => 'carrier-grade NAT',
			'100.85' => 'carrier-grade NAT',
			'100.86' => 'carrier-grade NAT',
			'100.87' => 'carrier-grade NAT',
			'100.88' => 'carrier-grade NAT',
			'100.89' => 'carrier-grade NAT',
			'100.90' => 'carrier-grade NAT',
			'100.91' => 'carrier-grade NAT',
			'100.92' => 'carrier-grade NAT',
			'100.93' => 'carrier-grade NAT',
			'100.94' => 'carrier-grade NAT',
			'100.95' => 'carrier-grade NAT',
			'100.96' => 'carrier-grade NAT',
			'100.97' => 'carrier-grade NAT',
			'100.98' => 'carrier-grade NAT',
			'100.99' => 'carrier-grade NAT',
			'100.100' => 'carrier-grade NAT',
			'100.101' => 'carrier-grade NAT',
			'100.102' => 'carrier-grade NAT',
			'100.103' => 'carrier-grade NAT',
			'100.104' => 'carrier-grade NAT',
			'100.105' => 'carrier-grade NAT',
			'100.106' => 'carrier-grade NAT',
			'100.107' => 'carrier-grade NAT',
			'100.108' => 'carrier-grade NAT',
			'100.109' => 'carrier-grade NAT',
			'100.110' => 'carrier-grade NAT',
			'100.111' => 'carrier-grade NAT',
			'100.112' => 'carrier-grade NAT',
			'100.113' => 'carrier-grade NAT',
			'100.114' => 'carrier-grade NAT',
			'100.115' => 'carrier-grade NAT',
			'100.116' => 'carrier-grade NAT',
			'100.117' => 'carrier-grade NAT',
			'100.118' => 'carrier-grade NAT',
			'100.119' => 'carrier-grade NAT',
			'100.120' => 'carrier-grade NAT',
			'100.121' => 'carrier-grade NAT',
			'100.122' => 'carrier-grade NAT',
			'100.123' => 'carrier-grade NAT',
			'100.124' => 'carrier-grade NAT',
			'100.125' => 'carrier-grade NAT',
			'100.126' => 'carrier-grade NAT',
			'100.127' => 'carrier-grade NAT',
			'179.16' => 'local communications',
			'172.17' => 'local communications',
			'172.18' => 'local communications',
			'172.19' => 'local communications',
			'172.20' => 'local communications',
			'172.21' => 'local communications',
			'172.22' => 'local communications',
			'172.23' => 'local communications',
			'172.24' => 'local communications',
			'172.25' => 'local communications',
			'172.26' => 'local communications',
			'172.27' => 'local communications',
			'172.28' => 'local communications',
			'172.29' => 'local communications',
			'172.30' => 'local communications',
			'172.31' => 'local communications',
			'192.168' => 'local communications',
			'198.18' => 'Network benchmark tests subnets',
			'198.19' => 'Network benchmark tests subnets',
			'192.0.0' => 'IANA IPv4 IETF Protocol Assignments',
			'192.0.2' => 'Documentation TEST-NET',
			'192.88.99' => 'Internet 6to4',
			'198.51.100' => 'Documentation TEST-NET-2',
			'203.0.113' => 'Documentation TEST-NET-3',
			);
	
	$xip = explode('.', $ip);
	$ip1 = $xip[0];
	$ip2 = $xip[0] .'.'. $xip[1];
    $ip3 = $xip[0] .'.'. $xip[1] .'.'. $xip[2];
	if(array_key_exists($ip1, $special)) return $special[$ip1];
	if(array_key_exists($ip2, $special)) return $special[$ip2];
	if(array_key_exists($ip3, $special)) return $special[$ip3];
	else return false;
}

function country_array($cn){
    $cn = strtoupper($cn);
	if(strlen($cn) != 2) return false;
    $countries = array(
					"AF" => array("country" => "Afghanistan", "continent" => "Asia"),
					"AX" => array("country" => "?land Islands", "continent" => "Europe"),
					"AL" => array("country" => "Albania", "continent" => "Europe"),
					"DZ" => array("country" => "Algeria", "continent" => "Africa"),
					"AS" => array("country" => "American Samoa", "continent" => "Oceania"),
					"AD" => array("country" => "Andorra", "continent" => "Europe"),
					"AO" => array("country" => "Angola", "continent" => "Africa"),
					"AI" => array("country" => "Anguilla", "continent" => "North America"),
					"AQ" => array("country" => "Antarctica", "continent" => "Antarctica"),
					"AG" => array("country" => "Antigua and Barbuda", "continent" => "North America"),
					"AR" => array("country" => "Argentina", "continent" => "South America"),
					"AM" => array("country" => "Armenia", "continent" => "Asia"),
					"AW" => array("country" => "Aruba", "continent" => "North America"),
					"AU" => array("country" => "Australia", "continent" => "Oceania"),
					"AT" => array("country" => "Austria", "continent" => "Europe"),
					"AZ" => array("country" => "Azerbaijan", "continent" => "Asia"),
					"BS" => array("country" => "Bahamas", "continent" => "North America"),
					"BH" => array("country" => "Bahrain", "continent" => "Asia"),
					"BD" => array("country" => "Bangladesh", "continent" => "Asia"),
					"BB" => array("country" => "Barbados", "continent" => "North America"),
					"BY" => array("country" => "Belarus", "continent" => "Europe"),
					"BE" => array("country" => "Belgium", "continent" => "Europe"),
					"BZ" => array("country" => "Belize", "continent" => "North America"),
					"BJ" => array("country" => "Benin", "continent" => "Africa"),
					"BM" => array("country" => "Bermuda", "continent" => "North America"),
					"BT" => array("country" => "Bhutan", "continent" => "Asia"),
					"BO" => array("country" => "Bolivia", "continent" => "South America"),
					"BA" => array("country" => "Bosnia and Herzegovina", "continent" => "Europe"),
					"BW" => array("country" => "Botswana", "continent" => "Africa"),
					"BV" => array("country" => "Bouvet Island", "continent" => "Antarctica"),
					"BR" => array("country" => "Brazil", "continent" => "South America"),
					"IO" => array("country" => "British Indian Ocean Territory", "continent" => "Asia"),
					"BN" => array("country" => "Brunei Darussalam", "continent" => "Asia"),
					"BG" => array("country" => "Bulgaria", "continent" => "Europe"),
					"BF" => array("country" => "Burkina Faso", "continent" => "Africa"),
					"BI" => array("country" => "Burundi", "continent" => "Africa"),
					"KH" => array("country" => "Cambodia", "continent" => "Asia"),
					"CM" => array("country" => "Cameroon", "continent" => "Africa"),
					"CA" => array("country" => "Canada", "continent" => "North America"),
					"CV" => array("country" => "Cape Verde", "continent" => "Africa"),
					"KY" => array("country" => "Cayman Islands", "continent" => "North America"),
					"CF" => array("country" => "Central African Republic", "continent" => "Africa"),
					"TD" => array("country" => "Chad", "continent" => "Africa"),
					"CL" => array("country" => "Chile", "continent" => "South America"),
					"CN" => array("country" => "China", "continent" => "Asia"),
					"CX" => array("country" => "Christmas Island", "continent" => "Asia"),
					"CC" => array("country" => "Cocos (Keeling) Islands", "continent" => "Asia"),
					"CO" => array("country" => "Colombia", "continent" => "South America"),
					"KM" => array("country" => "Comoros", "continent" => "Africa"),
					"CG" => array("country" => "Congo", "continent" => "Africa"),
					"CD" => array("country" => "The Democratic Republic of The Congo", "continent" => "Africa"),
					"CK" => array("country" => "Cook Islands", "continent" => "Oceania"),
					"CR" => array("country" => "Costa Rica", "continent" => "North America"),
					"CI" => array("country" => "Cote D'ivoire", "continent" => "Africa"),
					"HR" => array("country" => "Croatia", "continent" => "Europe"),
					"CU" => array("country" => "Cuba", "continent" => "North America"),
					"CY" => array("country" => "Cyprus", "continent" => "Asia"),
					"CZ" => array("country" => "Czech Republic", "continent" => "Europe"),
					"DK" => array("country" => "Denmark", "continent" => "Europe"),
					"DJ" => array("country" => "Djibouti", "continent" => "Africa"),
					"DM" => array("country" => "Dominica", "continent" => "North America"),
					"DO" => array("country" => "Dominican Republic", "continent" => "North America"),
					"EC" => array("country" => "Ecuador", "continent" => "South America"),
					"EG" => array("country" => "Egypt", "continent" => "Africa"),
					"SV" => array("country" => "El Salvador", "continent" => "North America"),
					"GQ" => array("country" => "Equatorial Guinea", "continent" => "Africa"),
					"ER" => array("country" => "Eritrea", "continent" => "Africa"),
					"EE" => array("country" => "Estonia", "continent" => "Europe"),
					"ET" => array("country" => "Ethiopia", "continent" => "Africa"),
					"FK" => array("country" => "Falkland Islands (Malvinas)", "continent" => "South America"),
					"FO" => array("country" => "Faroe Islands", "continent" => "Europe"),
					"FJ" => array("country" => "Fiji", "continent" => "Oceania"),
					"FI" => array("country" => "Finland", "continent" => "Europe"),
					"FR" => array("country" => "France", "continent" => "Europe"),
					"GF" => array("country" => "French Guiana", "continent" => "South America"),
					"PF" => array("country" => "French Polynesia", "continent" => "Oceania"),
					"TF" => array("country" => "French Southern Territories", "continent" => "Antarctica"),
					"GA" => array("country" => "Gabon", "continent" => "Africa"),
					"GM" => array("country" => "Gambia", "continent" => "Africa"),
					"GE" => array("country" => "Georgia", "continent" => "Asia"),
					"DE" => array("country" => "Germany", "continent" => "Europe"),
					"GH" => array("country" => "Ghana", "continent" => "Africa"),
					"GI" => array("country" => "Gibraltar", "continent" => "Europe"),
					"GR" => array("country" => "Greece", "continent" => "Europe"),
					"GL" => array("country" => "Greenland", "continent" => "North America"),
					"GD" => array("country" => "Grenada", "continent" => "North America"),
					"GP" => array("country" => "Guadeloupe", "continent" => "North America"),
					"GU" => array("country" => "Guam", "continent" => "Oceania"),
					"GT" => array("country" => "Guatemala", "continent" => "North America"),
					"GG" => array("country" => "Guernsey", "continent" => "Europe"),
					"GN" => array("country" => "Guinea", "continent" => "Africa"),
					"GW" => array("country" => "Guinea-bissau", "continent" => "Africa"),
					"GY" => array("country" => "Guyana", "continent" => "South America"),
					"HT" => array("country" => "Haiti", "continent" => "North America"),
					"HM" => array("country" => "Heard Island and Mcdonald Islands", "continent" => "Antarctica"),
					"VA" => array("country" => "Holy See (Vatican City State)", "continent" => "Europe"),
					"HN" => array("country" => "Honduras", "continent" => "North America"),
					"HK" => array("country" => "Hong Kong", "continent" => "Asia"),
					"HU" => array("country" => "Hungary", "continent" => "Europe"),
					"IS" => array("country" => "Iceland", "continent" => "Europe"),
					"IN" => array("country" => "India", "continent" => "Asia"),
					"ID" => array("country" => "Indonesia", "continent" => "Asia"),
					"IR" => array("country" => "Iran", "continent" => "Asia"),
					"IQ" => array("country" => "Iraq", "continent" => "Asia"),
					"IE" => array("country" => "Ireland", "continent" => "Europe"),
					"IM" => array("country" => "Isle of Man", "continent" => "Europe"),
					"IL" => array("country" => "Israel", "continent" => "Asia"),
					"IT" => array("country" => "Italy", "continent" => "Europe"),
					"JM" => array("country" => "Jamaica", "continent" => "North America"),
					"JP" => array("country" => "Japan", "continent" => "Asia"),
					"JE" => array("country" => "Jersey", "continent" => "Europe"),
					"JO" => array("country" => "Jordan", "continent" => "Asia"),
					"KZ" => array("country" => "Kazakhstan", "continent" => "Asia"),
					"KE" => array("country" => "Kenya", "continent" => "Africa"),
					"KI" => array("country" => "Kiribati", "continent" => "Oceania"),
					"KP" => array("country" => "Democratic People's Republic of Korea", "continent" => "Asia"),
					"KR" => array("country" => "Republic of Korea", "continent" => "Asia"),
					"KW" => array("country" => "Kuwait", "continent" => "Asia"),
					"KG" => array("country" => "Kyrgyzstan", "continent" => "Asia"),
					"LA" => array("country" => "Lao People's Democratic Republic", "continent" => "Asia"),
					"LV" => array("country" => "Latvia", "continent" => "Europe"),
					"LB" => array("country" => "Lebanon", "continent" => "Asia"),
					"LS" => array("country" => "Lesotho", "continent" => "Africa"),
					"LR" => array("country" => "Liberia", "continent" => "Africa"),
					"LY" => array("country" => "Libya", "continent" => "Africa"),
					"LI" => array("country" => "Liechtenstein", "continent" => "Europe"),
					"LT" => array("country" => "Lithuania", "continent" => "Europe"),
					"LU" => array("country" => "Luxembourg", "continent" => "Europe"),
					"MO" => array("country" => "Macao", "continent" => "Asia"),
					"MK" => array("country" => "Macedonia", "continent" => "Europe"),
					"MG" => array("country" => "Madagascar", "continent" => "Africa"),
					"MW" => array("country" => "Malawi", "continent" => "Africa"),
					"MY" => array("country" => "Malaysia", "continent" => "Asia"),
					"MV" => array("country" => "Maldives", "continent" => "Asia"),
					"ML" => array("country" => "Mali", "continent" => "Africa"),
					"MT" => array("country" => "Malta", "continent" => "Europe"),
					"MH" => array("country" => "Marshall Islands", "continent" => "Oceania"),
					"MQ" => array("country" => "Martinique", "continent" => "North America"),
					"MR" => array("country" => "Mauritania", "continent" => "Africa"),
					"MU" => array("country" => "Mauritius", "continent" => "Africa"),
					"YT" => array("country" => "Mayotte", "continent" => "Africa"),
					"MX" => array("country" => "Mexico", "continent" => "North America"),
					"FM" => array("country" => "Micronesia", "continent" => "Oceania"),
					"MD" => array("country" => "Moldova", "continent" => "Europe"),
					"MC" => array("country" => "Monaco", "continent" => "Europe"),
					"MN" => array("country" => "Mongolia", "continent" => "Asia"),
					"ME" => array("country" => "Montenegro", "continent" => "Europe"),
					"MS" => array("country" => "Montserrat", "continent" => "North America"),
					"MA" => array("country" => "Morocco", "continent" => "Africa"),
					"MZ" => array("country" => "Mozambique", "continent" => "Africa"),
					"MM" => array("country" => "Myanmar", "continent" => "Asia"),
					"NA" => array("country" => "Namibia", "continent" => "Africa"),
					"NR" => array("country" => "Nauru", "continent" => "Oceania"),
					"NP" => array("country" => "Nepal", "continent" => "Asia"),
					"NL" => array("country" => "Netherlands", "continent" => "Europe"),
					"AN" => array("country" => "Netherlands Antilles", "continent" => "North America"),
					"NC" => array("country" => "New Caledonia", "continent" => "Oceania"),
					"NZ" => array("country" => "New Zealand", "continent" => "Oceania"),
					"NI" => array("country" => "Nicaragua", "continent" => "North America"),
					"NE" => array("country" => "Niger", "continent" => "Africa"),
					"NG" => array("country" => "Nigeria", "continent" => "Africa"),
					"NU" => array("country" => "Niue", "continent" => "Oceania"),
					"NF" => array("country" => "Norfolk Island", "continent" => "Oceania"),
					"MP" => array("country" => "Northern Mariana Islands", "continent" => "Oceania"),
					"NO" => array("country" => "Norway", "continent" => "Europe"),
					"OM" => array("country" => "Oman", "continent" => "Asia"),
					"PK" => array("country" => "Pakistan", "continent" => "Asia"),
					"PW" => array("country" => "Palau", "continent" => "Oceania"),
					"PS" => array("country" => "Palestinia", "continent" => "Asia"),
					"PA" => array("country" => "Panama", "continent" => "North America"),
					"PG" => array("country" => "Papua New Guinea", "continent" => "Oceania"),
					"PY" => array("country" => "Paraguay", "continent" => "South America"),
					"PE" => array("country" => "Peru", "continent" => "South America"),
					"PH" => array("country" => "Philippines", "continent" => "Asia"),
					"PN" => array("country" => "Pitcairn", "continent" => "Oceania"),
					"PL" => array("country" => "Poland", "continent" => "Europe"),
					"PT" => array("country" => "Portugal", "continent" => "Europe"),
					"PR" => array("country" => "Puerto Rico", "continent" => "North America"),
					"QA" => array("country" => "Qatar", "continent" => "Asia"),
					"RE" => array("country" => "Reunion", "continent" => "Africa"),
					"RO" => array("country" => "Romania", "continent" => "Europe"),
					"RU" => array("country" => "Russian Federation", "continent" => "Europe"),
					"RW" => array("country" => "Rwanda", "continent" => "Africa"),
					"SH" => array("country" => "Saint Helena", "continent" => "Africa"),
					"KN" => array("country" => "Saint Kitts and Nevis", "continent" => "North America"),
					"LC" => array("country" => "Saint Lucia", "continent" => "North America"),
					"PM" => array("country" => "Saint Pierre and Miquelon", "continent" => "North America"),
					"VC" => array("country" => "Saint Vincent and The Grenadines", "continent" => "North America"),
					"WS" => array("country" => "Samoa", "continent" => "Oceania"),
					"SM" => array("country" => "San Marino", "continent" => "Europe"),
					"ST" => array("country" => "Sao Tome and Principe", "continent" => "Africa"),
					"SA" => array("country" => "Saudi Arabia", "continent" => "Asia"),
					"SN" => array("country" => "Senegal", "continent" => "Africa"),
					"RS" => array("country" => "Serbia", "continent" => "Europe"),
					"SC" => array("country" => "Seychelles", "continent" => "Africa"),
					"SL" => array("country" => "Sierra Leone", "continent" => "Africa"),
					"SG" => array("country" => "Singapore", "continent" => "Asia"),
					"SK" => array("country" => "Slovakia", "continent" => "Europe"),
					"SI" => array("country" => "Slovenia", "continent" => "Europe"),
					"SB" => array("country" => "Solomon Islands", "continent" => "Oceania"),
					"SO" => array("country" => "Somalia", "continent" => "Africa"),
					"ZA" => array("country" => "South Africa", "continent" => "Africa"),
					"GS" => array("country" => "South Georgia and The South Sandwich Islands", "continent" => "Antarctica"),
					"ES" => array("country" => "Spain", "continent" => "Europe"),
					"LK" => array("country" => "Sri Lanka", "continent" => "Asia"),
					"SD" => array("country" => "Sudan", "continent" => "Africa"),
					"SR" => array("country" => "Suriname", "continent" => "South America"),
					"SJ" => array("country" => "Svalbard and Jan Mayen", "continent" => "Europe"),
					"SZ" => array("country" => "Swaziland", "continent" => "Africa"),
					"SE" => array("country" => "Sweden", "continent" => "Europe"),
					"CH" => array("country" => "Switzerland", "continent" => "Europe"),
					"SY" => array("country" => "Syrian Arab Republic", "continent" => "Asia"),
					"TW" => array("country" => "Taiwan, Province of China", "continent" => "Asia"),
					"TJ" => array("country" => "Tajikistan", "continent" => "Asia"),
					"TZ" => array("country" => "Tanzania, United Republic of", "continent" => "Africa"),
					"TH" => array("country" => "Thailand", "continent" => "Asia"),
					"TL" => array("country" => "Timor-leste", "continent" => "Asia"),
					"TG" => array("country" => "Togo", "continent" => "Africa"),
					"TK" => array("country" => "Tokelau", "continent" => "Oceania"),
					"TO" => array("country" => "Tonga", "continent" => "Oceania"),
					"TT" => array("country" => "Trinidad and Tobago", "continent" => "North America"),
					"TN" => array("country" => "Tunisia", "continent" => "Africa"),
					"TR" => array("country" => "Turkey", "continent" => "Asia"),
					"TM" => array("country" => "Turkmenistan", "continent" => "Asia"),
					"TC" => array("country" => "Turks and Caicos Islands", "continent" => "North America"),
					"TV" => array("country" => "Tuvalu", "continent" => "Oceania"),
					"UG" => array("country" => "Uganda", "continent" => "Africa"),
					"UA" => array("country" => "Ukraine", "continent" => "Europe"),
					"AE" => array("country" => "United Arab Emirates", "continent" => "Asia"),
					"GB" => array("country" => "United Kingdom", "continent" => "Europe"),
					"US" => array("country" => "United States", "continent" => "North America"),
					"UM" => array("country" => "United States Minor Outlying Islands", "continent" => "Oceania"),
					"UY" => array("country" => "Uruguay", "continent" => "South America"),
					"UZ" => array("country" => "Uzbekistan", "continent" => "Asia"),
					"VU" => array("country" => "Vanuatu", "continent" => "Oceania"),
					"VE" => array("country" => "Venezuela", "continent" => "South America"),
					"VN" => array("country" => "Viet Nam", "continent" => "Asia"),
					"VG" => array("country" => "Virgin Islands, British", "continent" => "North America"),
					"VI" => array("country" => "Virgin Islands, U.S.", "continent" => "North America"),
					"WF" => array("country" => "Wallis and Futuna", "continent" => "Oceania"),
					"EH" => array("country" => "Western Sahara", "continent" => "Africa"),
					"YE" => array("country" => "Yemen", "continent" => "Asia"),
					"ZM" => array("country" => "Zambia", "continent" => "Africa"),
					"ZW" => array("country" => "Zimbabwe", "continent" => "Africa"),
					);
    if(isset($countries[$cn])) return array($countries[$cn]['country'], $countries[$cn]['continent']);
	else return false;
}

# 多维数组查询
function array_search_re($needle, $haystack, $a = 0, $nodes_temp = array()){
    global $nodes_found;
    $a++;
    foreach($haystack as $key1 => $value1){
        $nodes_temp[$a] = $key1;
        if(is_array($value1)){
            array_search_re($needle, $value1, $a, $nodes_temp);
        }
        elseif($value1 === $needle){
            $nodes_found[] = $nodes_temp;
        }
    }
    return $nodes_found;
}
