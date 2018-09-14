<?php
# error_reporting(E_ALL);
# ini_set('display_errors', '1');

set_time_limit(0);
header("Content-type: text/html;charset=utf-8");

$source_path = '/var/www/tmphtml'; # 最后不加 /
$host = 'https://ysuo.org/';
$hostjs = 'https:\/\/popcn.net\/';

$pagejs = pagejs($hostjs);
$footer = footer($host);
#$footer = file_get_contents('/home/wwwroot/category.html');


echo "<br>\r\n 正在修改文件\r\n<br>";
customize_flush();

$array_files = getDir($source_path);
$max = count($array_files);

for($i = 0;$i < $max;$i++){
    $filename = $array_files[$i];

	if(strstr($filename, '.html')){
		$str = file_get_contents($filename);
		
		# 取消评论
		if(strstr($str, '<form')){
			$arr = explode('<form',$str);
			$arr2 = explode('</form>', $arr[1] , 2);
			$new_str = $arr[0] . $arr2[1];
			$str = str_replace('<h4 class="list-group-item">发表评论 <small id="cancel-comment-reply"><a rel="nofollow" id="cancel-comment-reply-link" href="index.html#respond" style="display:none;">点击这里取消回复。</a></small></h4>','',$new_str);
		}
		
		# 取消边栏
		if(strstr($str, 'sidebar')){
			$arr = explode('<div id="sidebar" class="col-lg-4 col-md-4" role="complementary" itemscope itemtype="http://schema.org/WPSideBar">', $str , 2);
			
			$arr[0] = preg_replace('#		<script[^>]*?>.*?<\/script\s*>#si', '', $arr[0]);

			$arr2 = @explode('</footer>', @$arr[1] , 2);
			
			$arr2[1] = @str_replace("<script type='text/javascript' src='../../wp-includes/js/wp-embed.min.js?ver=4.8'></script>", '', @$arr2[1]);	
			$new_str = @$arr[0] . $footer . @$arr2[1];
			$str = $new_str;
		}
		
		
		# 取消日志发布信息,该信息只有一行
		$arr = explode("\n", $str);
		$n = count($arr);
		$str = '';
		for($x = 0; $x < $n; $x++){
			if(strstr($arr[$x], '<div class="entry-meta">')) $arr[$x] = '</div>';
			$str .= $arr[$x] . "\n";
		}
		
		# 去掉正文中的JS
		if(strstr($filename, '/201')){
			# echo $filename;
			$arr_x = explode('<div class="entry-footer clearfix" role="toolbar">', $str);	
			$arr2 = explode('<nav class="pager"', @$arr_x[1]);
			$str = $arr_x[0] . '</div><nav class="pager"' . @$arr2[1];
            $new_str = preg_replace('#<script[^>]*?>.*?<\/script\s*>#si', '', $str);
            $str = $new_str;
		}
		
		
		# 增加JS
 		if(strstr($filename, 'page/')){
 		    $str = str_replace($pagejs, '', $str);
            $str .= $pagejs;
		}

		# 最后替换html中的超链接成为相对链接
	    $str = str_replace($host, '/', $str);
		
        $js1 = '<script type="text/javascript" src="/blog/wp-content/cache/minify/570f2.js"></script>';
        $js2 = "\r\n<script type='text/javascript' src='/blog/wp-content/themes/dmeng2.0/js/bootstrap.min.js?ver=3.3.1'></script>\r\n";
        $js2 .= "<script type='text/javascript' src='/blog/wp-content/themes/dmeng2.0/js/dmeng-2.0.9.5.js?ver=2.0.9.5'></script>";
        $js2 = $js1 . $js2;
	    $str = str_replace($js1, $js2, $str);
		
		
		
		
		file_put_contents($filename , $str );
	}
}
echo "\r\n<br>done";
























/********************函数区，无需改动*********************/

function pagejs($hostjs){
 	$pagejs  = "\r\n\r\n\r\n";
    $pagejs .= "<script>\r\n";
    $pagejs .= 'var ajaxurl = \'/blog\/wp-admin\/admin-ajax.php\';';
    $pagejs .= 'var isUserLoggedIn = 0;';
    $pagejs .= 'var loginUrl = \'/blog\/wp-login.php?redirect_to=https%3A%2F%2Fysuo.org%2Fpage%2F3%2F\';';
    $pagejs .= 'var dmengPath = \'/blog\/wp-content\/themes\/dmeng2.0/\';';
    $pagejs .= 'var dmengTracker = {"type":"home","pid":1};';
    $pagejs .= 'var dmengInstant = 0;';
    $pagejs .= 'var dmengTips = {"success":"\u64cd\u4f5c\u6210\u529f","error":"\u64cd\u4f5c\u5931\u8d25","tryagain":"\u8bf7\u91cd\u8bd5"};';
    $pagejs .= 'var dmengCodePrettify = 0;';
    $pagejs .= "\r\n</script>\r\n</body>\r\n</html>\r\n";
    return $pagejs;
}

function footer($host){
    $footer = '' . "\r\n";
    $footer .= '    <div id="content" class="col-lg-8 col-md-8 archive" role="main" itemscope itemprop="mainContentOfPage" itemtype="http    ://schema.org/Blog">' . "\r\n";
    $footer .= '        <aside id="search-3" class="panel panel-default widget clearfix widget_search">' . "\r\n";
    $footer .= '            <form class="input-group" role="search" method="get" id="searchform" action="'.$host.'">' . "\r\n";
    $footer .= '                <span class="input-group-addon">搜索</span>' . "\r\n";
    $footer .= '                <input type="text" class="form-control" placeholder="请输入检索关键词 &hellip;" name="s" id="s" required>' .     "\r\n";
    $footer .= '                <span class="input-group-btn">' . "\r\n";
    $footer .= '                    <button type="submit" class="btn btn-default" id="searchsubmit"><span class="glyphicon glyphicon-search"    ></span></button>' . "\r\n";
    $footer .= '                </span>' . "\r\n";
    $footer .= '            </form>' . "\r\n";
    $footer .= '        </aside>' . "\r\n";
    $footer .= '        <div class="panel-footer clearfix">&copy; 2017 <a href="'.$host.'">伊索笔记</a>版权所有' . "\r\n";
    $footer .= '            <span class="pull-right copyright"> 主题源自 <a href="http://www.dmeng.net/wordpress/" target="_blank"> 多梦网络</a    ></span>' . "\r\n";
    $footer .= '        </div>' . "\r\n";
    $footer .= '    </div>' . "\r\n";
    $footer .= '' . "\r\n";
    $footer .= '    </div>' . "\r\n";
    $footer .= '</div><!-- #main -->' . "\r\n";
    return $footer;
}


/**
 * *刷新缓冲
 */	
function customize_flush(){
    echo(str_repeat(' ',256));
    // check that buffer is actually set before flushing
    if (ob_get_length()){           
        @ob_flush();
        @flush();
        @ob_end_flush();
    }   
    @ob_start();
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
	
