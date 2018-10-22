<?php
# http://www.helloweba.com/view-blog-253.html

$mark = 'hostodo';  # arukas 时需要登录帐号
$ssinfo = ssinfo($mark);
$ip = $ssinfo['ip'];
$port = $ssinfo['port'];
$pw = $ssinfo['pw'];
$method = $ssinfo['method'];
$imglink = $ssinfo['imglink'];

$title = 'Shadowsocks / SS 试用账号';
$tdpre = "<tr><td><pre>";
$tdoff = "</pre></td></tr>";

$html = '<html><head><meta http-equiv="content-type" content="text/html;charset=utf-8">';
$html .= '<title>' .$title. '</title><style type="text/css">';
$html .= 'pre{border:dashed 1px green; background-color:#C1CDCD;color:#000000; font-size:15px}';
$html .= '</style></head><body><br><center><h3>' .$title. '</h3>';
$html .= '<table>' . $tdpre . 'Server locus : Los Angeles ' . $tdoff;
$html .= $tdpre . 'Service provider : Hostodo.com ' . $tdoff;
$html .= $tdpre . 'ss://' . $method . ':' . $pw . '@' . $ip . ':' . $port . $tdoff;
$html .= $tdpre . "\r\n\r\n {\r\n";
$html .= '     "server":"' . $ip . '",' . "\r\n";
$html .= '     "server_port":' . $port . ',' . "\r\n";
$html .= '     "local_port":1080,' . "\r\n";
$html .= '     "password":"' . $pw . '",' . "\r\n";
$html .= '     "timeout":600,' . "\r\n";
$html .= '     "method":"' . $method . '",' . "\r\n";
$html .= ' }' . "\r\n $tdoff";
$html .= '<tr><td style=text-align:center;><pre>' . $imglink . $tdoff . '</table>';
$html .= '</center></body></html>';

echo beautify_html($html);





/**-------------- function area --------------*/

function ssinfo($mark){
    if($mark == 'arukas'){
        $post['email'] = 'pubss@gmail.com';
        $post['password'] = 'password';
        
        $cookie = tempnam('./', 'cookie');
        $url = 'https://app.arukas.io/api/login';
        $url2 = 'https://app.arukas.io/api/containers';
        $header[] = 'Accept:application/vnd.api+json';
        $header[] = 'Content-Type:application/vnd.api+json';
        $login = login_post($url, $cookie, $post);
        if(!strstr($login, 'OK')){
            echo '<br>登录失败';
            exit(0);
        }
        $result = get_content($url2, $cookie, $header);
        unlink($cookie);
        $arr = json_decode($result, true);
        print_r($arr);
        # for($i = 0;$i < count($arr['data']);$i++)print_r($arr['data'][$i]['attributes']['port_mappings']['0']);
        $cmd = $arr['data']['0']['attributes']['cmd'];
        $port = $arr['data']['0']['attributes']['port_mappings']['0']['0']['service_port'];
        $host = $arr['data']['0']['attributes']['port_mappings']['0']['0']['host'];
        $host = str_replace('-', '.', $host);
        $host = explode('.', $host);
        $ip = $host[1] . '.' . $host[2] . '.' . $host[3] . '.' . $host[4];
        $cmd = explode(' ', $cmd);
        $pw = $cmd[4];
        $method = $cmd[6];
    }else{
        $ip = '104.223.3.187';
        $port = 11268;
        $pw = 12345678;
        $method = 'AES-256-CFB';
    }
    
    $ssqr = 'ss://' . base64_encode($method . ':' . $pw . '@' . $ip . ':' . $port) . '#' . $mark;
    $PNG_TEMP_DIR = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'qr/temp' . DIRECTORY_SEPARATOR;
    $PNG_WEB_DIR = 'qr/temp/';
    if(!file_exists($PNG_TEMP_DIR)) mkdir($PNG_TEMP_DIR);
    $filename = $PNG_TEMP_DIR . 'test.png';
    $errorCorrectionLevel = 'H'; //array('L','M','Q','H')
    $matrixPointSize = '4'; //array(1~10)
    include './qr/qrlib.php';
    if(isset($ssqr)){
        if(trim($ssqr) == '') die('data can not be empty! <a href="?" >back</a>');
        $filename = $PNG_TEMP_DIR . md5($ssqr . '|' . $errorCorrectionLevel . '|' . $matrixPointSize) . '.png';
        QRcode :: png($ssqr, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
    }
    $imglink = '<img src="' . $PNG_WEB_DIR . basename($filename) . '"/>';
    
    return array('ip' => $ip, 'port' => $port, 'pw' => $pw, 'method' => $method, 'imglink' => $imglink,);
}

function login_post($url, $cookie, $post){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    $result = curl_exec($ch);
    curl_close($ch);
    return$result;
}

function get_content($url, $cookie, $header){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    $result = curl_exec($ch);
    curl_close($ch);
    return$result;
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
?>
