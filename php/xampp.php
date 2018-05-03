<?php

# 获取 xampp 最新版本
# 参数 ?os=win , ?os=linux , ?os=mac
# wget --content-disposition url

$str = file_get_contents('https://www.apachefriends.org/zh_cn/index.html');
if(empty($_GET['os'])){
    $tags = '<div class="large-3 columns">';
    $tags_array = explode($tags, $str);
    $div_array = explode('</div>', $tags_array[4]);
    $link_contents = $tags . $tags_array[1] . $tags . $tags_array[2] . $tags . $tags_array[3] . $tags . $div_array[0] . '</div>';
    $link_contents = str_replace('data-delayed-href="/zh_cn/download_success.html"', '', $link_contents);
    $link_contents = str_replace('/download.html', 'https://www.apachefriends.org/download.html', $link_contents);
    echo$link_contents;
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
if($_GET['os'] === 'win')fwd301($win);
if($_GET['os'] === 'linux')fwd301($linux);
if($_GET['os'] === 'mac')fwd301($mac);
function fwd301($str){
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . get_url($str));
}
function get_url($str){
    $a_tags = strip_tags($str, '<a>');
    preg_match_all('/ href="([^>]*)">[^<]*<\/a>/is', $a_tags, $matches);
    return($matches[1][0]);
}
