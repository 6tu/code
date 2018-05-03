<?php

$source_path = 'f:/blog';
$target_path = 'f:/htdocs/blog';
$log_path    = 'f:/logs';

$source_log  = $log_path . '/source.log';
$update_log  = $log_path . '/target_update.log';

header("Content-type: text/html;charset=gbk");
set_time_limit(0);
date_default_timezone_set('Asia/Shanghai');

// 建立 log 目录
@mkdir($log_path, 0777, true);

// 读取原文件夹中所有的文件的 MD5
if(!file_exists($source_path)) die(" 原文件夹不存在 。;)");
echo "\r\n<br>  正在读取文件的 MD5 值 <br>\r\n";
customize_flush();
$array_files = getDir($source_path);
$maxstr = getItem($array_files);
$maxlen = strlen($maxstr) + 2;
$max = count($array_files);

$filemd5 = "原文件夹中所有的文件  \r\n\r\n";

for($i = 0;$i < $max;$i++){
    $filename = $array_files[$i];
    $md5 = md5_file($filename) . "\r\n";
    $filename = str_pad($filename, $maxlen);         # 用空格补全
	$filemd5 .= $filename . ' ////  ' . $md5;
    }
file_put_contents($source_log, $filemd5);

// 对 MD5 不同的文件作记录
if(!file_exists($target_path)) die(" 目标文件夹不存在 。;)");

if(!file_exists($source_log)) die("文件不存在");
$files = file_get_contents($source_log);
$files = str_replace($source_path, $target_path, $files);
$array_files = explode("\r\n", $files);
$n = count($array_files);

$no_target = "目标文件夹中不存在的文件 \r\n\r\n";
$different = "目标文件夹中已变更的文件 \r\n\r\n";

for($i = 2;$i < $n;$i++){
	if(strstr($array_files[$i], '////')){
		$array_files_md5 = explode('////', $array_files[$i]);
		$filename = trim($array_files_md5['0']);
		$md5 = trim($array_files_md5['1']);
		if(!file_exists($filename)) {
			$no_target .= $filename . "\r\n";
			continue;
		}
		$md5file = md5_file($filename);
		if($md5file !== $md5) $different .= $filename . "\r\n";
	}
}
unset($array_files);
unset($i);
unset($max);

// 对多出的文件作记录
$array_files = getDir($target_path);
$maxstr = getItem($array_files);
$maxlen = strlen($maxstr) + 2;
$max = count($array_files);
$fn_target = "目标文件夹中特有的文件 \r\n\r\n";

for($i = 0;$i < $max;$i++){
    $filename = $array_files[$i];
    $md5 = md5_file($filename) . "\r\n";
	$fn_src = str_replace($target_path, $source_path, $filename);
	$filename = str_pad($filename, $maxlen);         # 用空格补全
	if(!file_exists($fn_src)) $fn_target .= $filename . ' ////  ' . $md5;
}

// 做记录，打印明细
$info = $no_target ."\r\n --------------------- \r\n\r\n". $different . "\r\n\r\n --------------------- \r\n\r\n". $fn_target;
file_put_contents($update_log, $info);

$no_target = str_replace("\r\n", "\r\n<br>", $no_target);
$different = str_replace("\r\n", "\r\n<br>", $different);
$fn_target = str_replace("\r\n", "\r\n<br>", $fn_target);
$logs = "<br>\r\n $no_target <br>\r\n $different <br>\r\n $fn_target";
echo $logs;

@unlink($log_path.'/blank.log');
$blank = ch_empty_dir($target_path) ;
$blank = str_replace("\r\n", "\r\n<br>\r\n", $blank);
//$blank = mb_convert_encoding($blank, "GBK", "UTF-8");
echo "<br>\r\n\r\n目标文件夹中的空文件夹<br><br>\r\n" . $blank . "\r\n<br>\r\n";
# exit(0);




function ch_empty_dir($path){
	global $log_path;
	
    if(is_dir($path) && ($handle = opendir($path)) !== false){
        while(($file = readdir($handle)) !== false){ // 遍历文件夹
            if($file != '.' && $file != '..'){
                $curfile = $path . '/' . $file;//当前目录
                if(is_dir($curfile)){ // 目录
                    ch_empty_dir($curfile);//如果是目录则继续遍历
                    if(count(scandir($curfile)) == 2){ // 目录为空,=2是因为.和..存在
						file_put_contents($log_path.'/blank.log', $curfile . "\r\n", FILE_APPEND);
                    }
                }
            }
        }
        closedir($handle);
    }
	$cf = @file_get_contents($log_path.'/blank.log');
    return $cf;
}







/********************函数区，无需改动*********************/

/**
 * *刷新缓冲
 */	
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

/**
 * *复制文件，log 被记录
 * *使用 copyfiles($srcfile)，无返回值
 *
 * @参数 $srcfile 原文件，$update_path 目标目录，$maxlenth 限制最大文件
 */	
function copyfiles($srcfile){

    global $update_path;
    global $log_path;
    global $maxlenth;
	global $maxlen;
	if (empty($update_path)) $update_path = $source_path . '-update';
	if (empty($log_path)) $log_path = dirname(__FILE__) . '\phplog';
	if (empty($maxlenth)) $maxlenth = 104857600;
	if (!file_exists($update_path))  mkdir($update_path, '0777',  true);
    $srcfileinfo = customize_fileinfo($srcfile);
    $dstpath = $update_path . '/' . $srcfileinfo['relativepath'] . '/';
    $dstfile = $dstpath . $srcfileinfo['basename'];
    if (!is_dir($dstpath)){
        $mode = $srcfileinfo['perms'];
        @mkdir($dstpath, $mode, true);
        }
    # 如果目标文件存在，则新文件名为原文件名-文件日期-4位随机数
    if (file_exists($dstfile)){
	    $dstfilemd5 = md5_file($dstfile);
		if($srcfileinfo['md5'] !== $dstfilemd5){ 
            $dstfilename = $srcfileinfo['filename'] . '-' . $srcfileinfo['mtime'] . '-' . rand_char($n = 4) . '.' . $srcfileinfo['extension'];
            $dstfile = $dstpath . $dstfilename;
		    }
        }
    $srcfileinfo['size'] < $maxlenth ? $cp = copy($srcfile, $dstfile) : $cp = 0;
    // 记录
	if(file_exists($log_path . '/false.log')) unlink($log_path . '/false.log');
    $log = str_pad($srcfile, $maxlen);
    if ($cp !== 1 ) @file_put_contents($log_path . '/false.log', $log . "  ////  no \r\n", FILE_APPEND);
	return $cp;
    }

/**
 * *获取随机字串
 * 
 * @参数 $n=4 字串
 */
function rand_char($n=4) { 
    $rand = '';
    for($i = 0;$i < $n;$i++ ){
        $base = 62;
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $rand .= $chars[mt_rand(1, $base) - 1];
	}
	return $rand;
}

/**
 * *取数组长度最长的值
 * 
 * @参数 $array数组
 */
function getItem($array) {
    $index = 0;
    foreach ($array as $k => $v) {
        if (strlen($array[$index]) < strlen($v))
            $index = $k;
    }
    return @$array[$index];
}

/**
 * *遍历目录中的文件
 * *由 searchDir() 和 getDir() 两个函数组成，
 * *使用 getDir($path) ，返回数组
 *
 * @参数 $path目录路径
 */	
function searchDir($path, & $data){
    if(is_dir($path)){
        $dp = dir($path);
        while($file = $dp -> read()){
            if($file != '.' && $file != '..'){
                searchDir($path . '/' . $file, $data);
                }
            }
        $dp -> close();
        }
    if(is_file($path)){
        $data[] = $path;
        }
    }
function getDir($path){
    $data = array();
    searchDir($path, $data);
    return $data;
    }

/**
 * *获取文件信息
 * *由两个函数组成  customize_fileinfo() , minetype_array() 
 * *用法 customize_fileinfo($file)，返回数组
 *
 * @参数 $file 包括路径和文件名
 */

function customize_fileinfo($file){
    
    if(!file_exists($file)) die("文件不存在或者是超链接");
    $file_info = array();
    $realpath = realpath($file);
    $pathinfo = pathinfo($file);
	if(strpos($pathinfo['dirname'], '\\') !== false){
		$relativepath_win = explode('\\', $pathinfo['dirname'], 2);
		$drive = $relativepath_win[0];
		$relativepath_backslashes = $relativepath_win[1];
		$dir = str_replace("\\", '/', $pathinfo['dirname']);
		$relativepath = explode('/', $dir, 2);
	}else{
		$relativepath = explode('/', $pathinfo['dirname'], 2);
    }
	$size = filesize($file);
	$type = filetype($file);
	$mimeType = minetype_array();
	$key = @$pathinfo['extension'];
	if(array_key_exists($key,$mimeType)) {
	        $mime_type = $mimeType[$key];
	    }else{
	        $mime_type = 'application/x-' . $key;
	    }
    $md5 = md5_file($file);
    $sha1 = sha1_file($file);
    $ctime = filectime($file);
    $ctime = date("Ymd-His", $ctime);
    $atime = fileatime($file);
    $atime = date("Ymd-His", $atime);
    $mtime = filemtime($file);
    $mtime = date("Ymd-His", $mtime);
    $group = filegroup($file);
    $owner = fileowner($file);
    $inode = fileinode($file);
    $perms = fileperms($file);
    $is_file = is_file($file);
    $is_file == 1 ? $is_file = 'yes' : $is_file = 'no';
    $is_dir = is_dir($file);
    $is_dir == 1?$is_dir = 'yes':$is_dir = 'no';
    $is_executable = is_executable($file);
    $is_executable == 1?$is_executable = 'yes':$is_executable = 'no';
    $is_readable = is_readable($file);
    $is_readable == 1?$is_readable = 'yes':$is_readable = 'no';
    $is_writable = is_writable($file);
    $is_writable == 1?$is_writable = 'yes':$is_writable = 'no';
    $is_link = is_link($file);
    $is_link == 1?$is_link = 'yes':$is_link = 'no';
	$stat = stat($file);
	
    $file_info = $file_info + array('realpath' => $realpath, 'relativepath' => $relativepath['1']) + $pathinfo;
	if(isset($relativepath_win)) $file_info = $file_info + array('drive' => $drive, 'relativepath_win' => $relativepath_backslashes);
    $file_info = $file_info + array(
	    'mime' => $mime_type, 
	    'type' => $type, 
	    'size' => $size,
	    'md5' => $md5,
	    'sha1' => $sha1,		
	    'ctime' => $ctime, 
		'mtime' => $mtime, 
		'atime' => $atime,
		'group' => $group, 
		'owner' => $owner, 
		'inode' => $inode, 
		'perms' => $perms,
		'is_file' => $is_file, 
		'is_dir' => $is_dir, 
		'is_executable' => $is_executable, 
		'is_readable' => $is_readable, 
		'is_writable' => $is_writable, 
		'is_link' => $is_link,
		'dev' => $stat['dev'],
		'nlink' => $stat['nlink'],
		'uid' => $stat['uid'],
		'gid' => $stat['gid'],
		'rdev' => $stat['rdev'],
		'blksize' => $stat['blksize'],
		'blocks' => $stat['blocks'],
		);
	if(strpos($pathinfo['dirname'], '/') !== false){
		$basename = explode('/', $pathinfo['basename']);
		$filename = explode('/', $pathinfo['filename']);
	    $file_info['basename'] = $basename[count($basename)-1];
		$file_info['filename'] = $filename[count($filename)-1];
		}
    return $file_info;
}

function minetype_array(){
    $mimeType = array(
        // applications(应用类型)
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'exe' => 'application/octet-stream',
        'doc' => 'application/vnd.ms-word',
        'xls' => 'application/vnd.ms-excel',
        'pdf' => 'application/pdf',
        'xml' => 'application/xml',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pps' => 'application/vnd.ms-powerpoint',
        'odt' => 'application/vnd.oasis.opendocument.text',
        'swf' => 'application/x-shockwave-flash',
        
        // archives(档案类型)
        'gz' => 'application/x-gzip',
        'tgz' => 'application/x-gzip',
        'zip' => 'application/zip',
        'rar' => 'application/x-rar',
        'tar' => 'application/x-tar',
        'bz' => 'application/x-bzip2',
        'bz2' => 'application/x-bzip2',
        'tbz' => 'application/x-bzip2',
        '7z' => 'application/x-7z-compressed',
        
        // texts(文本类型)
        'txt' => 'text/plain',
        'php' => 'text/x-php',
        'html' => 'text/html',
        'htm' => 'text/html',
        'js' => 'text/javascript',
        'css' => 'text/css',
        'rtf' => 'text/rtf',
        'rtfd' => 'text/rtfd',
        'py' => 'text/x-python',
        'java' => 'text/x-java-source',
        'pl' => 'text/x-perl',
        'sql' => 'text/x-sql',
        'rb' => 'text/x-ruby',
        'sh' => 'text/x-shellscript',
        
        // images(图片类型)
        'bmp' => 'image/x-ms-bmp',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'png' => 'image/png',
        'tif' => 'image/tiff',
        'tiff' => 'image/tiff',
        'tga' => 'image/x-targa',
        'psd' => 'image/vnd.adobe.photoshop',
        
        // audio(音频类型)
        'mp3' => 'audio/mpeg',
        'mid' => 'audio/midi',
        'ogg' => 'audio/ogg',
        'mp4a' => 'audio/mp4',
        'wav' => 'audio/wav',
        'wma' => 'audio/x-ms-wma',
        
        // video(视频类型)
        'avi' => 'video/x-msvideo',
        'dv' => 'video/x-dv',
        'mp4' => 'video/mp4',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        'mov' => 'video/quicktime',
        'wm' => 'video/x-ms-wmv',
        'flv' => 'video/x-flv',
        'mkv' => 'video/x-matroska'
        );	
    return $mimeType;
    }
	
/**
 * *删除所有空目录
 * 
 * @参数 $path目录路径
 */
function rm_empty_dir($path, $log_path){
    if(is_dir($path) && ($handle = opendir($path)) !== false){
        while(($file = readdir($handle)) !== false){ // 遍历文件夹
            if($file != '.' && $file != '..'){
                $curfile = $path . '/' . $file;//当前目录
                if(is_dir($curfile)){ // 目录
                    rm_empty_dir($curfile);//如果是目录则继续遍历
                    if(count(scandir($curfile)) == 2){ // 目录为空,=2是因为.和..存在
						rmdir($curfile);//删除空目录
                    }
                }
            }
        }
        closedir($handle);
    }
}
	
/**
 * *建立目录
 * 
 * @参数 $dir 字串，$mode 指定目录属性
 */
function mkdir_empty($dir, $mode){
    if(empty($mode)) $mode = 0777;
    if(file_exists($dir)) rename($dir, $dir . '-' . rand_char($n = 4) . '-old');
	@mkdir($dir, $mode, true);
    return @mkdir($dir, $mode, true);
    }
	
function parseArgs($argv){
    array_shift($argv);
    $out = array();
    foreach ($argv as $arg) {
        if (substr($arg, 0, 2) == '--') {
            $eqPos = strpos($arg, '=');
            if ($eqPos === false) {
                $key       = substr($arg, 2);
                $out[$key] = isset($out[$key]) ? $out[$key] : true;
            } else {
                $key       = substr($arg, 2, $eqPos - 2);
                $out[$key] = substr($arg, $eqPos + 1);
            }
        } elseif (substr($arg, 0, 1) == '-') {
            if (substr($arg, 2, 1) == '=') {
                $key       = substr($arg, 1, 1);
                $out[$key] = substr($arg, 3);
            } else {
                $chars = str_split(substr($arg, 1));
                foreach ($chars as $char) {
                    $key       = $char;
                    $out[$key] = isset($out[$key]) ? $out[$key] : true;
                }
            }
        } else {
            $out[] = $arg;
        }
    }
    return $out;
}
	
?>
