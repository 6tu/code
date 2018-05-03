<?php
/**
 *
 * https://blog.huijiewei.com/note/php-check-china-ip
 *
 * $ip = '222.50.36.99';
 * $ipjson = 'china-ip.json';
 * get_china_ip_table($ipjson);
 * echo check_is_china_ip($ip, $ipjson);
 *
 */
 
# ****** 获取APNIC的数据,建立json格式数据
function get_china_ip_table($ipjson){
    $tb = file_get_contents('http://ftp.apnic.net/apnic/stats/apnic/delegated-apnic-latest');
    $tb_array = explode("\n", $tb);
    unset($tb);
    $tb_china = array();
    foreach($tb_array as $t){
        $ta = explode("|", $t);
        if(count($ta) >= 7){
            if($ta[1] == 'CN' && $ta[2] == 'ipv4'){
                $ip_addr = explode(".", $ta[3]);
                if(count($ip_addr) >= 4){
                    $num = (int)$ta[4];
                    $a1 = (int)$ip_addr[0];
                    $a2 = (int)$ip_addr[1];
                    $a3 = (int)$ip_addr[2];
                    $a4 = (int)$ip_addr[3];
                    if($num > 0){
                        $offset = 0;
                        while($num > 0){
                            if($num >= 65536){
                                $tb_china[$a1][$a2 + $offset][] = array('s' => 0, 'e' => 65535, 'n' => 65535);
                            }else{
                                $st = $a3 * 256 + $a4;
                                $et = $st + $num-1;
                                $tb_china[$a1][$a2 + $offset][] = array('s' => $st, 'e' => $et, 'n' => $num);
                            }
                            $num = $num-65536;
                            $offset = $offset + 1;
                        }
                    }
                }
            }
        }
    }
    unset($tb_array);
    $ts_china = json_encode($tb_china);
    unset($tb_china);
    $fp = @fopen($ipjson, 'w');
    fwrite($fp, $ts_china);
    fclose($fp);
    unset($ts_china);
}
# ****** 从json格式数据中检测IP是否来自中国
function check_is_china_ip($ip, $ipjson){
    $ip_addr = explode('.', $ip);
    if(count($ip_addr) < 4) return false;
    $a1 = (int)$ip_addr[0];
    $a2 = (int)$ip_addr[1];
    $a3 = (int)$ip_addr[2];
    $a4 = (int)$ip_addr[3];
    $s_china = file_get_contents($ipjson);
    $tb_china = json_decode($s_china, 1);
    unset($s_china);
    if(!isset($tb_china[$a1][$a2]) || count($tb_china[$a1][$a2]) == 0) return false;
    $a = $a3 * 256 + $a4;
    foreach($tb_china[$a1][$a2] as $d){
        if($a >= $d['s'] && $a <= $d['e']){
            return true;
        }
    }
    return false;
}
?>
