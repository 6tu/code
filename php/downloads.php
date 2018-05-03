<?php
set_time_limit(0);
header("Content-type: text/html; charset=utf-8");
$allow_type = array('bmp','jpg','jpeg','png','gif',
                    'apk','rar','zip','7z','ace','arj','bz2','gz','iso','img','jar','lzh','tar','uue','xz','z','cab',
                    'cda','wav','mp3','wma','ra','midi','ogg','ape','flac','acc','ram',
                    'avi','rm','3gp','mp4','rmvb','wmv','mpeg','asf','mov','dvd','vcd','flv','fla','swf',
                    'pdf','doc','xls','ppt','docx','pptx','wps','','','','txt','json',
                    'mdb','csv','dat','dbc','db','dbf','dba','nsf','mdf','ldf','sql',
                    'exe','dll',
                    );
$allow_dir = array('./soft','./gapps','soft','gapps',);

isset($_GET['appid']) ? $filename = $_GET['appid'] : die('<br> 没有指定 appid 参数 <meta http-equiv="refresh" content="3;url=./index.php"> ');
if(!file_exists($filename)) die('文件不存在 <meta http-equiv="refresh" content="3;url=./index.php"> ');
$fileinfo = customize_fileinfo($filename);
#print_r($fileinfo);
$path = $fileinfo['dirname'];
$file = $fileinfo['basename'];
$ext = $fileinfo['extension'];
$size = $fileinfo['size'];
$size2 = $size - 1;
if (!in_array($path, $allow_dir)) die('请求的目录不对 <meta http-equiv="refresh" content="3;url=./index.php"> ');
if (!in_array($ext, $allow_type)) die('请求的文件格式不对 <meta http-equiv="refresh" content="3;url=./index.php"> ');

header('Content-Range: bytes 0-' . $size2 . '/' . $size);
#header('Content-Type: application/octet-stream');
header('Content-Type: multipart/byteranges');
header('Content-type: application/x-' . $ext);
header('Content-Disposition: attachment; filename=' . $file);
header('Content-Length: ' . $size);
flush();
readfile($filename);
exit();

#new.down.php
#download($path,$file);  

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
		$relativepath = @explode('/', $dir, 2);
	}else{
		$relativepath = @explode('/', $pathinfo['dirname'], 2);
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
	
    $file_info = $file_info + array('realpath' => $realpath, 'relativepath' => @$relativepath['1']) + $pathinfo;
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
/**
 * *自定义 mime 类型的数组
 * *用法 minetype_array() 返回数组
 *
 */
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
?>
