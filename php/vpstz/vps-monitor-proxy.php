<?php
/**
 * 多台VPS上线监测工具
 * 
 * 服务端用 雅黑探针，客户端基本是雅黑探针的 HTML 部分
 * vpstz.php 是服务端，修改 $company 为VPS提供商的英文代号，如 aliyun
 * vps-monitor.php 算是客户端，修改服务器端探针所在的路径。$query_string可以留空
 * eth0表示VPS是KVM/VMware，VENET0则是Openvz
 * 
 * 系小白业余练习，请勿拍砖
 */

 
# 服务器端探针指向 vpstz.php
$query_string = '/tz/vpstz.php?act=rt&callback=';

# 服务器地址和商家名称，不支持中文名称
$sites = array(
    'qcloud' => "http://119.29.23.116",
    'aliyun' => "http://aliyun.com",
    'arubacloud' => "http://89.36.215.108",
    'ServerCheap' => "http://162.212.157.193",
    'Linode' => "http://45.56.85.55",
    'hostdare' => "http://31.220.14.46",    
    'hostmybytes' => "http://66.154.104.88",
    'alpharacks' => "http://155.94.226.47",
    'woothosting' => "http://155.94.181.137",
    'firevps' => "http://192.99.211.42",
    'hostzeta' => "http://181.215.240.149",
    'hiformance' => "http://107.173.160.111",

);

# 客户端 url 指向vps-monitor.php
$mor_query = '/tz/vpsmor.php?act=rt&callback=?';

# 本页面 th 标签背景和文字颜色
$color_array = array(
    '#3066a6;color: #ffffff;',
    '#dedede;color:#626262;'
    );

$title = 'VPS 在线监测';

$json_all = '';
$ajax_add_1 = '';
$ajax_add_2 = '';
$span_add = '';
foreach($sites as $key => $value){
    $json = get_json($value . $query_string);
    if($json == false or !strstr($json, 'nic')){
        $json_all .= '';
        $ajax_add_1 .= '';
        $ajax_add_2 .= '';
        $span_add .= '<table><tr><th colspan="4"> >> ' . $key . ' 已停止运行 / 连接超时 / 获取的数据错误 。。。。。。<< </th></tr></table>';;
        }else{
        $json = str_replace(',"', ',"' . $key . '_', $json);
        $json = str_replace('{"nic', '{"' . $key . '_nic', $json);
        $json_all .= $json;
        $ajax_add_1 .= change_key($key, js_add_1());
        $ajax_add_2 .= change_key($key, js_add_2());
        $span_add .= change_key($key, html_span_add());
        }
    }

    $json = str_replace('})({', ',', $json_all);
    //$json_array=jsonstr2array($json_all);
    //$json=json_encode($json_array);
if(isset($_GET['act']) && $_GET['act'] == "rt"){

    $_GET['callback'] = htmlspecialchars($_GET['callback']);
    //echo $_GET['callback'],'(',$json,')';
    echo $_GET['callback'], $json ;
    exit;
    }

$js = js_static_1() . $ajax_add_1 . js_static_2($mor_query) . $ajax_add_2 . js_static_3();
$css = css_add($color_array);
$html = html_head($title) . $css . $js . "</head>\r\n<body>\r\n" . "<a name=\"w_top\"></a>\r\n<div id=\"page\">\r\n<!--服务器相关参数-->" . $span_add . html_footer();



ob_end_clean();
ob_implicit_flush(true);
echo $html;

# json 数据转为数组
function jsonstr2array($jsonstr){
    $jsonstr = trim($jsonstr, chr(239) . chr(187) . chr(191));
    $jsonstr = str_replace('})({', ',', $jsonstr);
    $jsonstr = rtrim($jsonstr, ')');
    $jsonstr = ltrim($jsonstr, '(');
    $json_array = array_unique(json_decode($jsonstr, true));
    return $json_array;
    }

# CURL 获取服务器端数据
function get_json($url){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_NOBODY, FALSE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate,sdch");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); # 超时设置
    $json = curl_exec($ch);
    //curl_close($ch);
    # $httpCode=curl_getinfo($ch,CURLINFO_HTTP_CODE);
    return $json;
    }

/**************** 以下部分纯处理 html ****************/

# 替换关键字
function change_key($sites_name, $str){
    $key1 = array(
        'nic',
        'os_info',
        'totalSpace',
        'useSpace',
        'freeSpace',
        'TotalMemory',
        'UsedMemory',
        'FreeMemory',
        'TotalSwap',
        'swapUsed',
        'swapFree',
        'uptime',
        'memRealUsed',
        'memRealFree',
        'NetOut',
        'NetInput',
        'OldOut',
        'OldInput',
        );
    $key2 = array(
        $sites_name . '_nic',
        $sites_name . '_os_info',
        $sites_name . '_totalSpace',
        $sites_name . '_useSpace',
        $sites_name . '_freeSpace',
        $sites_name . '_TotalMemory',
        $sites_name . '_UsedMemory',
        $sites_name . '_FreeMemory',
        $sites_name . '_TotalSwap',
        $sites_name . '_swapUsed',
        $sites_name . '_swapFree',
        $sites_name . '_uptime',
        $sites_name . '_memRealUsed',
        $sites_name . '_memRealFree',
        $sites_name . '_NetOut',
        $sites_name . '_NetInput',
        $sites_name . '_OldOut',
        $sites_name . '_OldInput',
        );
    $str = str_replace($key1, $key2, $str);
    return $str;
    }

# html 部分
function html_head($title){
    $html_head =
    <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

EOF;
    return $html_head . '<title>' . $title . "</title>\r\n";
    }

function html_span_add(){
    $html =
    <<<EOF

<table>
<tr>
    <th colspan="4">
         [<span id="nic"></span>] >> <span id="os_info"></span> || 已运行 >> </span><span id="uptime"></span>
    </th>
</tr>
<tr>
    <td colspan="2">
        硬盘已用: <font color="#333333">
        <span id="useSpace"></span> / <span id="totalSpace"></span> G， >> 空闲:
        <span id="freeSpace"></span> G， </font>
    </td>
    <td colspan="2">
        物理内存：已用 <font color="#CC0000">
        <span id="UsedMemory"></span> / <span id="TotalMemory"></span> >> Swap内存:
        <span id="swapUsed"></span> / <span id="TotalSwap"></span></font>
    </td>
</tr>
<tr>
    <td width="35%">
        入网: <font color="#CC0000"><span id="NetInput2"></span><span id="nic"></span></font>
    </td>
    <td width="15%">
        实时: <font color="#CC0000"><span id="NetInputSpeed2">0B/s</span></font>
    </td>
    <td width="35%">
        出网: <font color="#CC0000"><span id="NetOut2"></span></font>
    </td>
    <td width="15%">
        实时: <font color="#CC0000"><span id="NetOutSpeed2">0B/s</span></font>
    </td>
</tr>
</table>
EOF;
    return $html;
    }

function html_footer(){
    $html_footer = "    </div><br><br><br></body>\r\n<center><font size=5>big foot</font></center><p><br>\r\n</html>\r\n\r\n";
    return $html_footer;
    }

# css 部分
function css_add($color_array){
    if(empty($color_array)){
        $color_array = array(
		    '#3066a6;color: #ffffff;',
            '#dedede;color:#626262;'
            );
        }
    $rn = array_rand($color_array);
    $color = $color_array[$rn];
    $css1 =
    <<<EOF
<style type="text/css">
<!--
*{font-family: "Microsoft Yahei",Tahoma, Arial;}
body{text-align: center; margin: 0 auto; padding: 0; background-color:#fafafa;font-size:12px;font-family:Tahoma, Arial}
h1{font-size: 26px; padding: 0; margin: 0; color: #333333; font-family: "Lucida Sans Unicode","Lucida Grande",sans-serif;}
h1 small{font-size: 11px; font-family: Tahoma; font-weight: bold;}
a{color: #666; text-decoration:none;}
a.black{color: #000000; text-decoration:none;}
table{width:100%;clear:both;padding: 0; margin: 0 0 10px;border-collapse:collapse; border-spacing: 0;
box-shadow: 1px 1px 1px #CCC;
-moz-box-shadow: 1px 1px 1px #CCC;
-webkit-box-shadow: 1px 1px 1px #CCC;
-ms-filter: "progid:DXImageTransform.Microsoft.Shadow(Strength=2, Direction=135, Color='#CCCCCC')";}
EOF;
    $css2 = 'th{padding: 3px 6px;font-weight: bold;background: ' . $color . 'border: 1px solid #cccccc;text-align: left;}';
    $css3 =
    <<<EOF
tr{padding: 0; background:#FFFFFF;}
td{padding: 3px 6px; border:1px solid #CCCCCC;}
.w_logo{height:25px;text-align:center;color:#333;FONT-SIZE: 15px; width:13%;}
.w_top{height:25px;text-align:center; width:8.7%;}
.w_top:hover{background:#dadada;}
#page{width: 960px; padding: 0 auto; margin: 0 auto; text-align: left;}
#header{position:relative; padding:5px;}
.w_small{font-family: Courier New;}
.w_number{color: #f800fe;}
.sudu{padding: 0; background:#5dafd1;}
.suduk{margin:0px; padding:0;}
.resYes{}
.resNo{color: #FF0000;}
.word{word-break:break-all;}
-->
</style>

EOF;
    return $css1 . $css2 . $css3;
    }

# javascript 部分
function js_add_1(){
    $js_add_1 =
    <<<EOF

    var OldOutSpeed2=2;
    var OldOutSpeed3=3;
    var OldOutSpeed4=4;
    var OldOutSpeed5=5;
    var OldInputSpeed2=2;
    var OldInputSpeed3=3;
    var OldInputSpeed4=4;
    var OldInputSpeed5=5;

EOF;
    return $js_add_1;
    }

function js_add_2(){
    $js_add_2 =
    <<<EOF

    $("#nic").html(dataJSON.nic);
    $("#os_info").html(dataJSON.os_info);
    $("#totalSpace").html(dataJSON.totalSpace);
    $("#useSpace").html(dataJSON.useSpace);
    $("#freeSpace").html(dataJSON.freeSpace);
    $("#TotalMemory").html(dataJSON.TotalMemory);
    $("#UsedMemory").html(dataJSON.UsedMemory);
    $("#FreeMemory").html(dataJSON.FreeMemory);
    $("#TotalSwap").html(dataJSON.TotalSwap);
    $("#swapUsed").html(dataJSON.swapUsed);
    $("#swapFree").html(dataJSON.swapFree);
    $("#uptime").html(dataJSON.uptime);
    $("#memRealUsed").html(dataJSON.memRealUsed);
    $("#memRealFree").html(dataJSON.memRealFree);
    $("#NetOut2").html(dataJSON.NetOut2);
    $("#NetOut3").html(dataJSON.NetOut3);
    $("#NetOut4").html(dataJSON.NetOut4);
    $("#NetOut5").html(dataJSON.NetOut5);
    $("#NetInput2").html(dataJSON.NetInput2);
    $("#NetInput3").html(dataJSON.NetInput3);
    $("#NetInput4").html(dataJSON.NetInput4);
    $("#NetInput5").html(dataJSON.NetInput5);
    $("#NetOutSpeed2").html(ForDight((dataJSON.NetOutSpeed2-OldOutSpeed2),3));OldOutSpeed2=dataJSON.NetOutSpeed2;
    $("#NetOutSpeed3").html(ForDight((dataJSON.NetOutSpeed3-OldOutSpeed3),3));OldOutSpeed3=dataJSON.NetOutSpeed3;
    $("#NetOutSpeed4").html(ForDight((dataJSON.NetOutSpeed4-OldOutSpeed4),3));OldOutSpeed4=dataJSON.NetOutSpeed4;
    $("#NetOutSpeed5").html(ForDight((dataJSON.NetOutSpeed5-OldOutSpeed5),3));OldOutSpeed5=dataJSON.NetOutSpeed5;
    $("#NetInputSpeed2").html(ForDight((dataJSON.NetInputSpeed2-OldInputSpeed2),3));OldInputSpeed2=dataJSON.NetInputSpeed2;
    $("#NetInputSpeed3").html(ForDight((dataJSON.NetInputSpeed3-OldInputSpeed3),3));OldInputSpeed3=dataJSON.NetInputSpeed3;
    $("#NetInputSpeed4").html(ForDight((dataJSON.NetInputSpeed4-OldInputSpeed4),3));OldInputSpeed4=dataJSON.NetInputSpeed4;
    $("#NetInputSpeed5").html(ForDight((dataJSON.NetInputSpeed5-OldInputSpeed5),3));OldInputSpeed5=dataJSON.NetInputSpeed5;

EOF;
    return $js_add_2;
    }

function js_static_1(){
    $js_1 =
    <<<EOF

<script language="JavaScript" type="text/javascript" src="http://lib.sinaapp.com/js/jquery/1.7/jquery.min.js"></script>
<script type="text/javascript">
<!--
$(document).ready(function(){getJSONData();});
EOF;
    return $js_1;
    }

function js_static_2($mor_query){
    $js_2_1 =
    <<<EOF

function getJSONData(){
    setTimeout("getJSONData()", 1000);

EOF;
    $js_2_2 = '    $.getJSON("' . $mor_query . '", displayData)';
    $js_2_3 =
    <<<EOF

}

function ForDight(Dight, How) {
    if (Dight < 0) {
        var Last = 0 + "B/s";
    } else if (Dight < 1024) {
        var Last = Math.round(Dight * Math.pow(10, How)) / Math.pow(10, How) + "B/s";
    } else if (Dight < 1048576) {
        Dight = Dight / 1024;
        var Last = Math.round(Dight * Math.pow(10, How)) / Math.pow(10, How) + "K/s";
    } else {
        Dight = Dight / 1048576;
        var Last = Math.round(Dight * Math.pow(10, How)) / Math.pow(10, How) + "M/s";
    }
    return Last;
}

function displayData(dataJSON){

EOF;

    return $js_2_1 . $js_2_2 .$js_2_3;
    }

function js_static_3(){
    $js_3 =
    <<<EOF

}
-->
</script>

EOF;
    return $js_3;
    }



