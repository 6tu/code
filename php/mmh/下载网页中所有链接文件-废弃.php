<?php

# 下载网页中所有链接文件

$url0 = 'https://www.baidu.com';
$url_array0 = parse_url($url0);
$host0 = $url_array0['scheme'] . '//' . $url_array0['host'];
if(empty($url_array0['path'])) $url_array0['path'] = '/index.html';
else $url_array0['path'];

$html = file_get_contents($url0);
file_put_contents('.' . $url_array0['path'], $html);
$link = preg_htmllink($html);
$one = getpages($link, $url0);
$two = getpages($one, $url0);
print_r($one);
echo 'done';
exit;

function getpages($link, $url0){
    
    $url_array0 = parse_url($url0);
    $host0 = $url_array0['scheme'] . '//' . $url_array0['host'];
    if(empty($url_array0['path'])) $url_array0['path'] = '/index.html';
    else $url_array0['path'];
    
    $links = array();
    for($i = 0; $i < count($link); $i++){
        
        $url_array = parse_url($link[$i]);
        if(empty($url_array['scheme'])) $scheme = $url_array0['scheme'];
        else $scheme = $url_array['scheme'];
        if(empty($url_array['path'])) $path = '/index.html';
        else $url_array['path'];
        
        if(empty($url_array['host'])){
            $host = $host0;
            $url = format_url($url_array['path'], $url0);
        }else{
            $host = $scheme . '//' . $url_array['host'];
            $url = $link[$i];
        }
        
        $url_array1 = parse_url($url);
        $path = '.' . $url_array1['path'];
        $fn = basename($path);
        $dir = dirname($path);
        if(!is_dir($dir)) mkdir($dir, 0755, true);
        echo $url . "<br>\r\n" . $dir . "\r\n";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $html = curl_exec($ch);
        curl_close($ch);
        
        # 替换链接
        // $html_new = 
        
        file_put_contents($path, $html);
        
        if(strstr($fn, '2018.html')) continue;
        if(strstr($path, 'index.html')) continue;
        $links = preg_htmllink($html) + $links;
    }
    
    $link = array();
    foreach ($links as $k => $v){
        if(strpos($v, '#') !== false){
            unset($v);
            continue;
        }
        $link[] = $v;
    }
    return($link);
}

# 获取网页中超链接的两种方法
function preg_htmllink($html){
    $html = preg_replace('/\s{2,}|\n/i', '', $html); # 过滤掉换行和2个以上的空格
    preg_match_all('/(?:img|a|source|link|script)[^>]*(?:href|src)=[\'"]?([^>\'"\s]*)[\'"]?[^>]*>/i', $html, $out);
    return($out[1]);
}

# 获取网页中超链接
# PHP DOM XPath获取HTML节点方法大全
# https://www.awaimai.com/2113.html
function dom_htmllink($html){
    $dom = new DOMDocument();
    @$dom -> loadHTML($html);
    $xpath = new DOMXPath($dom);
    
    # 获取 css 链接
    $nodeList = $xpath -> query("//link");
    $css = [];
    foreach ($nodeList as $node){
        $css[] = $node -> attributes -> getNamedItem('href') -> nodeValue;
    }
    # 获取 js 链接
    $nodeList = $xpath -> query("//script");
    $js = [];
    foreach ($nodeList as $node){
        $js[] = @$node -> attributes -> getNamedItem('src') -> nodeValue;
    }
    # 获取 mp4 链接
    $nodeList = $xpath -> query("//source");
    $mp4 = [];
    foreach ($nodeList as $node){
        $mp4[] = $node -> attributes -> getNamedItem('src') -> nodeValue;
    }
    # 获取 img 链接
    $nodeList = $xpath -> query("//img");
    $img = [];
    foreach ($nodeList as $node){
        $img[] = $node -> attributes -> getNamedItem('src') -> nodeValue;
    }
    # 获取 htm 链接
    $nodeList = $xpath -> query("/html/body//a");
    // for ($i = 0; $i < $hrefs -> length; $i++) {
    //     $href = $hrefs -> item($i);
    //     $url = $href -> getAttribute('href');
    //     echo $url . "\r\n";
    // }
    $htm = [];
    foreach ($nodeList as $node){
        $htm[] = $node -> attributes -> getNamedItem('href') -> nodeValue;
    }
    
    $link = array_merge($css, $js, $mp4, $img, $htm);
    shuffle($link);
    foreach ($link as $key => $val){
        if (empty($val)) continue;
        $links[] = $val;
    }
    print_r($links);
}

# 相对路径URL转换为绝对路径URL,只转一个URL
# https://blog.csdn.net/aoyoo111/article/details/8088070
function format_url($srcurl, $baseurl){
    $srcinfo = parse_url($srcurl);
    if(isset($srcinfo['scheme'])){
        return $srcurl;
    }
    $baseinfo = parse_url($baseurl);
    $url = $baseinfo['scheme'] . '://' . $baseinfo['host'];
    if(substr($srcinfo['path'], 0, 1) == '/'){
        $path = $srcinfo['path'];
    }else{
        $path = dirname($baseinfo['path']) . '/' . $srcinfo['path'];
    }
    $rst = array();
    $path_array = explode('/', $path);
    if(!$path_array[0]){
        $rst[] = '';
    }
    foreach ($path_array AS $key => $dir){
        if ($dir == '..'){
            if (end($rst) == '..'){
                $rst[] = '..';
            }elseif(!array_pop($rst)){
                $rst[] = '..';
            }
        }elseif($dir && $dir != '.'){
            $rst[] = $dir;
        }
    }
    if(!end($path_array)){
        $rst[] = '';
    }
    $url .= implode('/', $rst);
    return str_replace('', '/', $url);
}

# 相对地址转换为绝对地址
function url_real($base, $url = ''){
    if(preg_match("~^(magnet|thunder|flashget|[fht]+p):~", $url, $tmp)){
        return $url;
    }
    $base = preg_replace("~/[^/]+.w+$~", "", $base);
    $base = preg_replace("~^s*http://|/s*$~", "", $base);
    $roads = preg_split("~/~", $base);
    $host = $roads[0];
    if(preg_match("~^/~", $url, $tmp)){
        return "$host$url";
    }
    $up = preg_match_all("~(../)~", $url, $tmp);
    $up = $up > 0?$up:0;
    $roads = array_slice($roads, 1);
    $keep = count($roads) - ($up > count($roads)?count($roads):$up);
    $roads = array_slice($roads, 0, $keep);
    $roads = implode("/", $roads);
    $paths = preg_replace("~^./~", "", $url);
    $paths = preg_split("~/~", $paths);
    $paths = array_slice($paths, $up, count($paths) - $up);
    $paths = implode("/", $paths);
    return "$host" . ($roads?"/$roads":"") . "/$paths";
}

# 相对路径转绝对路径方法
function relative_to_absolute($content, $feed_url){
    preg_match('/(http|https|ftp):///', $feed_url, $protocol);
    $server_url = preg_replace("/(http|https|ftp|news):///", "", $feed_url);
    $server_url = preg_replace("//.*/", "", $server_url);
    if ($server_url == ''){
        return $content;
    }
    if (isset($protocol[0])){
        $new_content = preg_replace('/href="//', 'href="' . $protocol[0] . $server_url . '/', $content);
        $new_content = preg_replace('/src="//', 'src="' . $protocol[0] . $server_url . '/', $new_content);
    }else{
        $new_content = $content;
    }
    return $new_content;
}
# basename() # 返回路径中的文件名
# dirname()  # 返回去掉文件名后的目录名
# pathinfo() # 返回一个关联数组，其中包括dirname、basename和extension
# parse_url  # 指定 PHP_URL_SCHEME、 PHP_URL_HOST、 PHP_URL_PORT、 PHP_URL_USER、 PHP_URL_PASS、 PHP_URL_PATH、 PHP_URL_QUERY 或 PHP_URL_FRAGMENT
#            # scheme  host port user pass path
# parse_str
