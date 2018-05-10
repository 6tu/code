<?php

# 获取 xampp 最新版本
# 参数 ?os=win , ?os=linux , ?os=mac
# wget --content-disposition url

$html = html();
$head = $html[0];
$body = $html[1];
if(empty($_GET['os'])){
    header("Content-type: text/html; charset=utf-8");
    echo $head . '<br><br><table align="center" height="" width=""><tr><td>';
    echo $body;
    echo '</td></tr></table>';
    exit(0);
}

$array = explode("\n", $str);
$n = count($array);
$win = '';
$linux = '';
$mac = '';
for($i = 0;$i < $n;$i++){
    if(strstr($array[$i], "installer.exe"))$win = $array[$i] . '</a>';
    if(strstr($array[$i], "installer.run"))$linux = $array[$i] . '</a>';
    if(strstr($array[$i], 'vm.dmg'))$mac = $array[$i] . '</a>';
}
if($_GET['os'] === 'win') fwd301($win);
if($_GET['os'] === 'linux') fwd301($linux);
if($_GET['os'] === 'mac') fwd301($mac);

function fwd301($str){
    $url = getrealurl($str);
    $url = substr($url,0,strrpos($url,'?'));
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $url);
}

function get_url($str){
    $a_tags = strip_tags($str, '<a>');
    preg_match_all('/ href="([^>]*)">[^<]*<\/a>/is', $a_tags, $matches);
    return($matches[1][0]);
}

function getrealurl($url){
    $url = get_url($url);
    $header = get_headers($url, 1);
    if(strpos($header[0], '301') || strpos($header[0], '302')){
        if(is_array($header['Location'])){
            return $header['Location'][count($header['Location'])-1];
        }else{
            return $header['Location'];
        }
    }else{
        return $url;
    }
}

function html(){
    $str = file_get_contents('https://www.apachefriends.org/zh_cn/index.html');
    $head = explode('</head>', $str);
    $preg = "/<script[\s\S]*?<\/script>/i";
    $newstr = preg_replace($preg, "", $head[0], 3);
    $css = explode('<link', $newstr);
    $newstr = $css[0] . '<link' . $css[2];
    $head = $newstr . "\r\n</head>\r\n\r\n<body>";
    
    $td = "</td></tr>\r\n<tr><td>";
    $tags = '<div class="large-3 columns">';
    $tags_array = explode($tags, $str);
    $div_array = explode('</div>', $tags_array[4]);
    
    $tags = '';
    // $td = "";
    $down_link = str_replace('</a>', '</a> 或者从 <a href="https://sourceforge.net/projects/xampp/files/" target="_blank">SourceForge</a> 下载<br>', $tags_array[1]);
    $down_link = $tags . $down_link . $td;
    $win_link = $tags . $tags_array[2] . $td;
    $linux_link = $tags . $tags_array[3] . $td;
    $osx_link = $tags . $div_array[0] . $td;
    
    $link_contents = $down_link . $win_link . $linux_link . $osx_link . '</div>';
    $link_contents = str_replace('data-delayed-href="/zh_cn/download_success.html"', '', $link_contents);
    $link_contents = str_replace('/download.html', 'https://www.apachefriends.org/download.html', $link_contents);
    $link_contents = str_replace('</h2>', ' XAMPP</h2>', $link_contents);
    $wget = $tags .'<img src="https://raw.githubusercontent.com/6tu/code/master/php/wget.png" style="max-width:90%; max-height:50px;"/>';
    $wget .= '<font style="color:green;font-size:20px;"> wget 用法</font><br><br>';
    $wget .= '<font style="color:#2F4F4F;"><b> wget --content-disposition url <br>';
    $wget .= ' url: http://domain/path/xampp.php?os=linux <br>';
    $wget .= ' os:  win , linux , mac </div></b></font>';

    return array($head, $link_contents.$wget);
}





