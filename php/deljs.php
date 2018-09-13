<?php
set_time_limit(0);

# 获取$srcdir目录下的 .htm* 文件列表
$srcdir = 'F://yuanjian';  #绝对路径
$updatedir = $srcdir . '-update/';
$filelist = $srcdir . '/list.txt';
if(!is_dir($srcdir)) mkdir($srcdir);
if(!is_dir($updatedir)) mkdir($updatedir);
if(file_exists($filelist)) rename($filelist, $filelist . '.bak');
$filenames = get_filenamesbydir($srcdir);
$fn = '';
foreach ($filenames as $value){
    $ext = pathinfo($value, PATHINFO_EXTENSION);
	if(strstr($ext, 'htm')) $fn .= $value. PHP_EOL;
}
file_put_contents($filelist, $fn, FILE_APPEND);


echo "\r\n";
$len = strlen($srcdir);
$list_array = explode("\r\n", $fn);
//print_r($list_array);
$i = 1;
foreach($list_array as $v){
    if(empty($v)) continue;
    $reldir = substr($v, $len);
	$srcfn = $srcdir . $reldir;
    $str = file_get_contents($srcfn);

    # 多个空格转为一个空格
    $article = preg_replace ( "/\s(?=\s)/","\\1",$str);

    # 删除js
    $preg = "/<script[\s\S]*?<\/script>/i";
    $html = preg_replace($preg, "", $article, -1);

    # 替换其它字串
    $article = str_replace("href='https://ysuo.org/blog", "href='/blog", $article);
    $article = str_replace('href="https://ysuo.org/blog', 'href="/blog', $article);
    $article = str_replace('<a href="https://ys138.win/">', '<a href="https://popcn.net/">', $article);

    $html = $article;
    $html = beautify_html($html);

    $newdir = pathinfo($updatedir . $reldir, PATHINFO_DIRNAME);
    if(!is_dir($newdir)) mkdir($newdir, 777, true); 

    file_put_contents($updatedir . $reldir, $html);

    $compstr = bytecomplement($reldir);
	echo $i . ' ......  ' . '.' . $reldir . '   ' . $compstr ." done\r\n";
	$i++;
}

# 长度补齐
function bytecomplement($str){
    $bv = 30;
    $length = strlen($str);
    if($length < $bv){
        $dv = $bv - $length;
        $space = '';
        for($i = 0; $i < $dv; $i++){
            $space .= '.';
        }
    }else{
        $space = '  ';
    }
    return $space;
}

# HTML 格式化
function beautify_html($html){
    $tidy_config = array(
        'clean' => true,
        'indent' => true,
        'indent-spaces' => 4,
        'output-xhtml' => true,
        'show-body-only' => false,
        'wrap' => 0
        );
    if(function_exists('tidy_parse_string')){
		$tidy = new tidy();
        $tidy = tidy_parse_string($html, $tidy_config, 'utf8');
        $tidy -> cleanRepair();
        $tidy = tidy_get_output($tidy);
        return $tidy;
    }else return $html;
}

# 获取目录下所有文件，包括子目录
function get_allfiles($path,&$files){
    if(is_dir($path)){
        $dp = dir($path);
        while ($file = $dp ->read()){
            if($file !== "." && $file !== ".."){
                get_allfiles($path."/".$file, $files);
            }
        }
        $dp ->close();
    }
    if(is_file($path)){
        $files[] = $path;
    }
}

function get_filenamesbydir($dir){  
    $files =  array();
    get_allfiles($dir,$files);
    return $files;
}


?>
        for($i = 0; $i < $dv; $i++){
            $space .= '.';
        }
    }else{
        $space = '  ';
    }
    return $space;
}

# HTML 格式化
function beautify_html($html){
    $tidy_config = array(
        'clean' => true,
        'indent' => true,
        'indent-spaces' => 4,
        'output-xhtml' => true,
        'show-body-only' => false,
        'wrap' => 0
        );
    if(function_exists('tidy_parse_string')){
		$tidy = new tidy();
        $tidy = tidy_parse_string($html, $tidy_config, 'utf8');
        $tidy -> cleanRepair();
        $tidy = tidy_get_output($tidy);
        return $tidy;
    }else return $html;
}

# 获取目录下所有文件，包括子目录
function get_allfiles($path,&$files){
    if(is_dir($path)){
        $dp = dir($path);
        while ($file = $dp ->read()){
            if($file !== "." && $file !== ".."){
                get_allfiles($path."/".$file, $files);
            }
        }
        $dp ->close();
    }
    if(is_file($path)){
        $files[] = $path;
    }
}

function get_filenamesbydir($dir){  
    $files =  array();
    get_allfiles($dir,$files);
    return $files;
}


?>
