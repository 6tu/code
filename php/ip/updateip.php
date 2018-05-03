<?php
/**
 *
 * $num = $ta[4];
 * $num = 16777216 ,(32-24=)/8 ,A段
 * $num = 65536 ,(32-16=)/16 ,B段
 * $num = 256 ,(32-8=)/24 ,C段
 *
 * 原件文件格式说明
 * 等级机构|获得该IP段的国家/组织|资源类型|起始IP |IP段长度|分配日期|分配状态
 * apnic   |CN                   |ipv4    |1.2.2.0|256     |20110331|assigned
 *
 * 针对 ripencc 中分配IP个数小于256的行的记录于 small.txt , 
 * 属于四维数组格式, JSON 文件单独保存于 ripencc-small.json 
 *
 * IP个数为 16777216 的, 由于是一个 A 段, 为数不多, 单独在放在数组中 
 *
 * 其它的是三维数组, JSON 保存在对应的文件里
 *
 * 使用了这两位大元的代码
 * https://blog.huijiewei.com/note/php-check-china-ip
 * http://www.cnblogs.com/zemliu/archive/2012/09/12/2681089.html
 *
*/

header("Content-type: text/html; charset=utf-8");     
set_time_limit(0);
ini_set('memory_limit', '512M');

echo 'Old data files are deleted. Start converting data ...<br>';
customize_flush();
get_ip_table();
echo '<br> done<br>';

# 生成 json 格式数据
function get_ip_table(){
	/*
	 * $local = 'http://127.0.0.1/nicip/delegated-test-latest.txt';
	 * $Registries[] = 'ftp://ftp.arin.net/pub/stats/arin/delegated-arin-extended-latest';   //North America
     * $Registries[] = 'ftp://ftp.ripe.net/ripe/stats/delegated-ripencc-latest';             //Europe
     * $Registries[] = 'ftp://ftp.afrinic.net/pub/stats/afrinic/delegated-afrinic-latest';   //Africa
     * $Registries[] = 'ftp://ftp.apnic.net/pub/stats/apnic/delegated-apnic-latest';         //Asia & Pacific
     * $Registries[] = 'ftp://ftp.lacnic.net/pub/stats/lacnic/delegated-lacnic-latest';      //South America
	*/
	
	$url = 'ftp://ftp.lacnic.net/pub/stats/lacnic/delegated-lacnic-latest';
	$fn_arr = filename($url)['basename'];
	$fn = explode("-", $fn_arr);
	$ipjson = $fn[1] . '.json';
    $tb = file_get_contents($url);
	if (file_exists($ipjson)) unlink($ipjson);
	if (file_exists('much-' . $ipjson)) unlink('much-' . $ipjson);
    $tb_array = explode("\n", $tb);
    unset($tb);
	
    $muchip = '';
    foreach($tb_array as $t){
        $ta = explode("|", $t);
        if(count($ta) >= 7 and $ta[2] == 'ipv4'){ // $ta[1]=='CN'&&

			# 生成 JSON 数据
            $endip = cal_ip($ta[3], $ta[4]);
            $eip = explode(".", $endip['eip']);
            $sip = explode(".", $ta[3]);
			# print_r($endip);
			
            $ip1 = $eip[0] - $sip[0];
            $ip2 = $eip[1] - $sip[1];
            $ip3 = $eip[2] - $sip[2];
            $ip4 = $eip[3] - $sip[3];
            if($ip1 > 0) {
				echo $muchip .= $t . " \r\n";
                continue;
			}
			if($ta[4] >= 16777216){
                echo $muchip .= $t . " \r\n";
                continue;
            }
			if(empty($ta[1])){
                # echo "<b> arin 国家代码为空的行 <br>\r\n";
				$ta[1] = 'reserved';
            }
			
			$ip_range = array();
			$tb = array();
            $mip3 = array();

			# 针对 ripencc 中分配IP个数小于256的行
			if($ta[4] < 256){
				if($ip3 > 0) file_put_contents('small.txt', $t . "\r\n", FILE_APPEND);
				for($sm = 0;$sm < $ta[4];$sm++) {
					$ip4 = $sip[3] + $sm;
					$ip_range[$ip4] = $ta[1];
					$tb[$sip[0]][$sip[1]][$sip[2]] = $ip_range;
				}
				$jssip = json_encode($tb) . ',';
				file_put_contents('ripencc-small.json', $jssip, FILE_APPEND);
				# print_r($tb);
				unset($tb);
				unset($jssip);
            }
			
            # 常用 IP 分段方法
			if($ip2 == 0 and $ta[4] > 255){
				for($a = 0;$a <= $ip3;$a++) {
					$nip3 = $sip[2] + $a;
					if($sip[2] == 0 and $eip[2] == 255) $nip3 = 0;
					$ip_range[$nip3] = $ta[1];
				}

				# $ip_range = array_merge(array_unique($ip_range));
				# $ip_range = array_value_replace($ip_range);
				$tb[$sip[0]][$sip[1]] = $ip_range;
				$jsip = json_encode($tb) . ',';
				file_put_contents($ipjson, $jsip, FILE_APPEND);
				# print_r($tb);
				unset($tb);
			}
			
            # 常用 IP 分段方法
			if($ip2 > 0 and $sip[2] == 0){
				for($i = 0;$i <= $ip2;$i++){
					$mip2 = $sip[1] + $i;
					$ip_range[$mip2][$sip[2]] = array($sip[2] => $ta[1]) ;
				}
				foreach($ip_range as $key => $v){
					$tb[$sip[0]] = array($key => $v);
					$jsip = json_encode($tb) . ',';
					file_put_contents($ipjson, $jsip, FILE_APPEND);	
					# print_r($tb);
					unset($tb);
				}
			}
			
			# 个别 IP 分段方法 arin
			# arin||ipv4|23.135.129.0|65280||reserved| 
			# ripencc|IR|ipv4|91.237.254.0|768|20120403|assigned
			
			if($ip2 > 0 and $sip[2] > 0){
				echo $t ."  <b>ip[3] != 0</b><br>\r\n\r\n";
				for($i = 0;$i <= $ip2;$i++){
					$mip2 = $sip[1] + $i;
					if($i == 0){
						for($m = 0;$m < (256 - $sip[2]);$m++){
							$nip3 = $sip[2] + $m;
							if($nip3 == 0) $nip3 = 'xxx';
							$mip3 = $mip3 + array($nip3 => $ta[1]);	
						}
						$tb[$sip[0]][$mip2] = $mip3;
						$jsip = json_encode($tb) . ',';
						file_put_contents($ipjson, $jsip, FILE_APPEND);
						# print_r($tb);
						unset($tb);
					}
					if($i > 0 and $i < $ip2) {
						$nip3 = 0;
						if($nip3 == 0) $nip3 = 'xxx';
						$mip3 = array($nip3 => $ta[1]);
						$tb[$sip[0]][$mip2] = $mip3;
						$jsip = json_encode($tb) . ',';
						file_put_contents($ipjson, $jsip, FILE_APPEND);
						# print_r($tb);
						unset($tb);
					}
					if($i == $ip2){
						for($n = 0;$n <= $eip[2];$n++){
							$nip3 = $n;
							if($nip3 == 0) $nip3 = 'xxx';
							$mip3 = $mip3 + array($nip3 => $ta[1]);	
						}
						$tb[$sip[0]][$mip2] = $mip3;
						$jsip = json_encode($tb) . ',';
						file_put_contents($ipjson, $jsip, FILE_APPEND);
						# print_r($tb);
						unset($tb);
					}
				}
			}

        }
    }
	
	# 修改组合后的json数据，方便解析
	unset($jsip);
	$data = @file_get_contents($ipjson);
	$data = '[' . trim($data) . ']';
	$data = str_replace(',]', ']', $data);
	$data = str_replace('xxx', '0', $data);
	file_put_contents($ipjson, $data);
	if(!empty($muchip)) file_put_contents('much-' . $ipjson, $muchip, FILE_APPEND);
}

# 计算最终IP
function cal_ip($sip, $num){
    $sip_addr = explode(".", $sip);
    $a1 = str_pad(decbin($sip_addr[0]), 8, 0, STR_PAD_LEFT);
    $a2 = str_pad(decbin($sip_addr[1]), 8, 0, STR_PAD_LEFT);
    $a3 = str_pad(decbin($sip_addr[2]), 8, 0, STR_PAD_LEFT);
    $a4 = str_pad(decbin($sip_addr[3]), 8, 0, STR_PAD_LEFT);
    $sipbit = $a1 . $a2 . $a3 . $a4;

    $log = log($num, 2);
    $len = 32 - $log;
    $bit = '';
    for($i = 0;$i < $len;$i++)$bit .= 1;
    $maskbit = str_pad($bit, 32, 0, STR_PAD_RIGHT);
    $mask_addr = str_split($maskbit, 8);
    $mask = bindec($mask_addr[0]) . '.' . bindec($mask_addr[1]) . '.' . bindec($mask_addr[2]) . '.' . bindec($mask_addr[3]);
    $maskbit_left = str_pad(decbin($num - 1), 32, 0, STR_PAD_LEFT);

	$eipbit = binary_plus($sipbit, $maskbit_left);
    $eip_addr = str_split($eipbit, 8);
    $eip = bindec($eip_addr[0]) . '.' . bindec($eip_addr[1]) . '.' . bindec($eip_addr[2]) . '.' . bindec($eip_addr[3]);
    $ip_array = array('sip' => $sip, 'eip' => $eip, 'mask' => $mask, 'len' => $len, );
    return $ip_array;
}

# 二进制相加 
function binary_plus($binstr1, $binstr2){
    $bin_arr1 = str_split($binstr1);
    $bin_arr2 = str_split($binstr2);
    $arr_len1 = count($bin_arr1);
    $arr_len2 = count($bin_arr2);
    $sum_arr = array();
    if($arr_len1 < $arr_len2){
        $short_arr = & $bin_arr1;
    }else{
        $short_arr = & $bin_arr2;
    }
	
    # 将两个数组的长度补到一样长，短数组在前面补0
    for($i = 0;$i < abs($arr_len1 - $arr_len2);$i++){
        array_unshift($short_arr, 0);
    }
    $carry = 0;
	
    # 进位标记
    for($i = count($bin_arr1)-1;$i >= 0;$i--){
        $result = $bin_arr1[$i] + $bin_arr2[$i] + $carry;
        switch($result){
        case 0:array_unshift($sum_arr, 0);
        $carry = 0;
        break;
        case 1:array_unshift($sum_arr, 1);
        $carry = 0;
        break;
        case 2:array_unshift($sum_arr, 0);
        $carry = 1;
        break;
        case 3:array_unshift($sum_arr, 1);
        $carry = 1;
        break;
        default:die();
        }
    }
    if($carry == 1){
        array_unshift($sum_arr, 1);
    }
    return implode("", $sum_arr);
}

# 替换数组内的元素
function array_value_replace($array){
    if(is_array($array)){
        foreach($array as $k => $v){
            $array[$k] = array_value_replace($array[$k]);
        }
    }else{
        $array = str_replace(array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0'), array(''), $array);
    }
    return $array;
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

# 刷新缓冲
function customize_flush(){
    if(php_sapi_name() === 'cli'){
	return true;
	}else{
        echo(str_repeat(' ',256));
        // check that buffer is actually set before flushing
        if (ob_get_length()){           
            @ob_flush();
            @flush();
            @ob_end_flush();
        }   
        @ob_start();
	}
}

# 从URL获取文件名
function filename($url){
	$urlinfo = parse_url($url);
	if(isset($urlinfo['path'])){
		$path = pathinfo($urlinfo['path']);
		if(empty($path['basename'])) $path['basename'] = $urlinfo['host'];
		if(empty($path['extension'])) $path['extension'] = '';
		if(empty($path['filename'])) $path['filename'] = '';
		$fiename = array('basename' => $path['basename'], 'extension' => $path['extension'], 'filename' => $path['filename'],);
	}else $fiename =array('basename' => $urlinfo['host'], 'extension' => '', 'filename' => '',);
	return $fiename;
}

# 获取网页
function get_contents($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
	return $result;
}
?>
