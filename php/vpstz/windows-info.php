<?php
header("Content-type: text/html; charset=utf-8");
echo "<pre>\r\n\r\n";

# 系统名称、版本和类型
$out = '';
$info = exec('wmic os get Caption,Version,OSArchitecture',$out,$status);
$osinfo_array = explode('  ',$out[1]);
$osinfo = array_values(array_filter($osinfo_array));
echo "系统名称: " . $osinfo[0] ."\r\n系统版本: " . $osinfo[2] ."\r\n系统类型: " . $osinfo[1] ."\r\n\r\n";

# 系统配置
$out = '';
$info = exec('wmic os get producttype',$out,$status);   #返回 3 是server ,返回其它的是 workstation
if($out[1] == 3) $osconfig = "Server";
else $osconfig = "Workstatio";
echo "系统配置: " . $osconfig . "\r\n\r\n";

# 已运行时长
$out = '';
$info = exec('wmic os get lastBootUpTime,LocalDateTime',$out,$status);
$datetime_array = explode('.',$out[1]);
$dt_array = explode(' ',$datetime_array[1]);
$localtime = substr($datetime_array[1],-14);
$boottime = $datetime_array[0];
$uptime = strtotime($localtime) - strtotime($datetime_array[0]);

$day=floor(($uptime)/86400);
$hour=floor(($uptime)%86400/3600);
$minute=floor(($uptime)%86400/60);
$second=floor(($uptime)%86400%60);
echo "已运行: ".$day."天".$hour."小时".$minute."分钟".$second."秒\r\n\r\n";

# 硬盘用量
$out = '';
$info = exec('wmic logicaldisk get FreeSpace,size /format:list',$out,$status);
$hd = '';
foreach($out as $vaule){
	$hd .= $vaule . ' ';;
}
$hd_array = explode('   ', trim($hd));
$key = 'CDEFGHIJKLMNOPQRSTUVWXYZ';
foreach($hd_array as $k => $v){
	$s_array = explode('Size=', $v);
	$fs_array = explode('FreeSpace=', $s_array[0]);
	$size = round(trim($s_array[1])/(1024*1024*1024), 1);
	$freespace = round(trim($fs_array[1])/(1024*1024*1024), 1);
	$drive = $key[$k];
	echo $drive . "盘,\r\n已用空间: " . ($size - $freespace) . "GB/" . $size . "GB\r\n可用空间: " . $freespace . "GB\r\n\r\n";
}

# 物理内存
$out = '';
$info = exec('wmic os get TotalVisibleMemorySize,FreePhysicalMemory',$out,$status);
# 多个空格转为一个空格
$phymem = preg_replace ( "/\s(?=\s)/","\\1",$out[1]);
$phymem_array = explode(' ',$phymem);
//print_r($phymem_array);
$freephymem = ceil($phymem_array[0]/1024);
$totalphymem = ceil($phymem_array[1]/1024);
echo "已用物理内存: ". ($totalphymem - $freephymem) ."MB/". $totalphymem . "MB\r\n空闲物理内存: " . $freephymem . "MB\r\n\r\n";

# 虚拟内存
$out = '';
$info = exec('wmic os get SizeStoredInPagingFiles,FreeSpaceInPagingFiles',$out,$status);
$pagemem = preg_replace ( "/\s(?=\s)/","\\1",$out[1]);
$pagemem_array = explode(' ',$pagemem);
$freepagemem = ceil($pagemem_array[0]/1024);
$totalpagemem = ceil($pagemem_array[1]/1024);
echo "已用虚拟内存: ". ($totalpagemem - $freepagemem) ."MB/". $totalpagemem . "MB\r\n空闲虚拟内存: " . $freepagemem . "MB\r\n\r\n";

# 网卡名称
$out = '';
$info = exec('wmic nic list brief',$out,$status);
$nic_array = explode('  ', $out[2], 2);
$nic = $nic_array[0];
echo "当前网卡名称: " . $nic . "\r\n\r\n";

# 网卡流量，最初计量为字节
$out = '';
$info = exec('netstat -e',$out,$status);
$out_array = array();
foreach ($out as $key => $value) {
    $out_array[$key] = mb_convert_encoding ($value, 'utf-8', 'GBK');
}
$net = preg_replace ( "/\s(?=\s)/","\\1",$out_array[4]);
$net_array = explode(' ',$net);
echo "当前数据流量\r\n已接收: " .round($net_array[1]/(1024*1024), 3) . "MB\r\n已发送: " . round($net_array[2]/(1024*1024), 3) . "MB\r\n\r\n";

//$out = '';
//$info = exec('wmic os get /all  /format:list',$out,$status);
//print_r($out);

# 电脑信息
//$out = '';
//$info = exec('systeminfo',$out,$status);
//print_r($out);

//$info = exec('ipconfig',$out,$status);
//print_r($out);
//$out = '';

# 执行批处理，需要绝对路径
//$info = exec('C:/Users/Administrator/Downloads/www/mem.bat',$out,$status);
//print_r($out);

//$out = '';
//$info = exec('net statistics workstation | find "Statistics since 统计数据开始于"',$out,$status);
//$boottime = preg_replace ( "/\s(?=\s)/","\\1",$out[0]);
//$boottime_array = explode(' ',$boottime,2);
//echo $boottime_array[1];

