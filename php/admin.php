<?php

/* =====================《Apache4All WEB管理》 基本配置=================== */

// --程序使用方式："0"为不用登陆即可使用；"1"为要输入管理员密码才能使用。
$set['mode'] = "1";

// --管理员密码：这里设置的密码是经过MD5加密的字符串，而不是明码。
$set['password'] = "8ddcff3a80f4189ca1c9d4d902c3c909";

/* ============================配置结束================================ */

header("Content-type: text/html; charset=utf-8");

if(!empty($_GET['dir'])){
    $dir = $_GET['dir'];
}
elseif(!empty($_POST['dir'])){
    $dir = $_POST['dir'];
}
else{
    $dir = "./";
}

$style_head = 
<<<EOF
<html>
<head>
<title>→┆站长WEB管理┆{title}←</title>
<meta content='text/html; charset=utf-8' http-equiv="Content-Type">
<meta http-equiv="keyword" content="Apache4All">
<style type='text/css'>
    A:link{color:000000; text-decoration: underline}A:active{color:ff3333;
    text-decoration: underline}A:hover{color:ffffff; text-decoration: underline;
    LEFT: 1px; POSITION: relative; TOP: 1px}A:visited{color:000000; text-decoration:
    underline}body{FONT-FAMILY:宋体; font-size=9pt; color:999999}TD{FONT-SIZE:
    9pt; color:000000; line-height: 150%}INPUT{FONT-SIZE: 9pt; HEIGHT: 20px;
    PADDING-BOTTOM: 1px; PADDING-LEFT: 1px; PADDING-RIGHT: 1px; PADDING-TOP:
    0px}textarea{FONT-FAMILY:宋体}.menu TD A{COLOR:ffffff; TEXT-DECORATION: none;
    WIDTH:100%; padding-top:2px}.menu TD A:hover{COLOR: 000000; TEXT-DECORATION:
    none; BACKGROUND-COLOR: bbbbbb; LEFT: 0px; TOP: 0px}.menu A:active{COLOR:
    ffffff; TEXT-DECORATION: none}.menu A:visited{COLOR: ffffff; TEXT-DECORATION:
    none}
</style>
</head>
<body bgcolor="ffffff" leftmargin="5" rightmargin="5" topmargin="0">
<br >
<div align="center">
    <table width="750" border="0" bgcolor="666666" cellpadding="0" cellspacing="1" class="menu">
    <tr bgcolor="888888">
        <td title='站长 WEB管理'><a href="/">>>　<font color="ffffff" face='Tahoma'>站长 WEB管理</font>　<<</a></td>
        <td width="80" align="center" title='文件目录列表及管理'><a href='?'>文件管理</a></td>
        <td width="80" align="center" title='生成大量的连续代码'><a href='?m=code'>代码生成</a></td>
        <td width="80" align="center" title='生成MD5加密后的字符串'><a href='?m=md5'>MD5加密</a></td>
        <td width="80" align="center" title='Unix时间换算成通用时间'><a href='?m=unixdate'>UNIX时间</a></td>
    </tr>
    </table>
    <table width="750" border="0" bgcolor="666666" cellpadding="3" cellspacing="1">
    <tr bgcolor="666666">
        <td><font color="ffffff">管理员操作：[<a href='?login=1'><u>登陆</u></a>|<a href='?login=3'><u>退出</u></a>]<br>当前打开目录：{$dir}</font></td>
        <td><font size="3" color="ffffff"><b>当前操作：{title}</b></font></td>
    </tr>
    </table>
EOF;


function getmicrotime()
{ // ----执行时间
    list($usec, $sec) = explode(" ", microtime());
    return($usec + $sec);
    }

function error_info($info, $url = "javascript:history.back(1)")
{ // ----错误提示
    //echo "<meta http-equiv=refresh content=5;URL='$url'><center><br><br><font size=3 color=ff0000>$info</font></center></td></tr></table>";
    echo '<meta http-equiv="refresh" content=5;URL=' .$url. '"><center><br><br><font size="3" color="ff0000">' .$info. '</font></center></td></tr></table>';
    exit;
    }

function skin_var($var1, $var2)
{ // ----替换页面变量
    global $style_head;
    $style_head = preg_replace("/\{$var1\}/", $var2, $style_head);
    }

/* ==========================函数结束，开始程序========================= */


if(!empty($_GET['login'])and $_GET['login'] == "2"){
    /* ------------------------ 检测密码，并生成Cookie ---------------------- */
    $password = md5($_POST['password']);
    if($password != $set['password']){
        error_info("密码错误！登陆失败</font>");
        }
    $time = time();
    
    if($_POST['yxtime'] == 3600){
        $cookie_time = $time + 3600;
    }
    elseif($_POST['yxtime'] == 10800){
        $cookie_time = $time + 10800;
    }
    elseif($_POST['yxtime'] == 86400){
        $cookie_time = $time + 86400;
    }
    elseif($_POST['yxtime'] == 2592000){
        $cookie_time = $time + 2592000;
    }
    else{
        $cookie_time = 0;
    }
    
    setcookie("boom_baby", $password, $cookie_time, $_SERVER['PHP_SELF']);
    
    echo "<meta http-equiv=refresh content=5;URL='?'><center><br><br>输入密码正确 | 登陆成功</center>";
    exit;
    }

elseif(!empty($_GET['login'])and $_GET['login'] == "3"){
    /* ------------------------------ 退出登陆状态 -------------------------- */
    setcookie("boom_baby", "00", "-9999", $_SERVER['PHP_SELF']);
    error_info("已经退出登陆，并清空Cookie");
    }

elseif(!empty($_GET['login'])and $_GET['login'] == "1"){
    /* -------------------------------- 登陆界面 ---------------------------- */
    $default =
<<<EOF
<body bgcolor="ffffff">
<center><br><br><br><br><br>
<table width="400" border="0" bgcolor="666666" cellpadding="3" cellspacing="1">
<tr bgcolor="666666">
    <td align="center"><font size="3" color="ffffff"><b>登　陆　管　理</b></font></td>
</tr>
<tr bgcolor="eeeeee">
    <form action='?login=2' method="post">
        <td align="center" height="80">
            管理员密码：<input type='password' name='password' size="19" maxlength="20"><br>
            <font style='font-size:9pt'>Cookies设置：</font>
            <select name='yxtime' size="1">
                <option value='0'>不保存</option>
                <option value='3600'> 1小时</option>
                <option value='10800'>3小时</option>
                <option value='86400'>1天</option>
                <option value='2592000'>1个月</option>
            </select>
            <br>
            <input type='submit' value='登陆管理'>
        </td>
    </form>
</tr>
</table>
</center>
</body>
EOF;
    echo $default;
    exit;
    }

$time_start = getmicrotime();

if(($set['mode'] == "1")and($_COOKIE['boom_baby'] != $set['password'])){
    echo "<center><br><br><br><font size=3 color=ff0000>抱歉，您没有登陆。无法使用本程序！</font><hr size=1><a href='?login=1'>>>输入管理员密码登陆<<</a></center>";
    exit;
    }

if(!@is_dir($dir)){
    $error = error_get_last();
    $em = $error['message'];
    if(!empty($em)){
        $adir = explode(':', $em, 3);
        //print_r($adir);
        echo " <b>目录 " . $dir . " 无权访问!</b> " . " open_basedir 允许的路径：" . $adir[2];
    }else{
        echo " <b>目录 " . $dir . " 或不存在!</b> ";
    }
}else{
    chdir($dir);
}


$open = opendir("./");

if(!empty($_GET['m'])and $_GET['m'] == "show"){
    /* -------------------------------- 查看内容 -------------------------------- */
    if($_GET['id'] != ""){
        if(file_exists($_GET['id'])){
            $fp = fopen($_GET['id'], r);
            $data = fread($fp, "9999999");
            fclose($fp);
            
            $data = str_replace("</textarea>", "</textarea>", $data);
            $data = str_replace("</textarea>", "</textarea>", $data);
            $data = str_replace("<textarea", "<textarea", $data);
            $data = str_replace("<textarea", "<textarea", $data);
            
            }
        }
    
    skin_var('title', "查看编辑文件");
    $edit = 
<<<EOF
{$style_head}<table width="750" border="0" bgcolor="666666" cellpadding="2" cellspacing="1">
<tr bgcolor="eeeeee" align="center">
    <form method="post" action='?m=write&dir={$dir}'>
        <td height="100">
            文件名：<input type="text" name="id" value='{$_GET['id']}' size="30" maxlength="30"><br>
            <textarea name='data' cols="100" rows="20">{$data}</textarea><input type="hidden" name='dir' value='{$dir}'><input type='submit' value='确定修改保存'>
        </td>
    </tr>
</form>
<tr bgcolor="888888" align="center">
    <td>
        <table width="700" border="0" style='border: solid 1; border-color: 666666'>
        <tr>
            <td>
                <center>可编辑txt/html/css/js/php/cgi/asp/jsp等所有文本类文件</center><font color="ff0000">注意：</font>　所编辑文件 < 9mb<br>由于程序使用“<font color="0000ff"><textarea></textarea></font>”标签来显示编辑文件内容，为了避免因冲突产生错误，<br>
                如果所显示编辑的文件中有“<font color="0000ff"><textarea></textarea></font>”标签，<br>
                程序会自动将“<font color="0000ff"><textarea</font>”转换成“<font color="00ff00"><textarea</font>”、“<font color="0000ff"></textarea></font>”转换成“<font color="00ff00"></textarea></font>”显示出来。<br>
                当文件保存时程序会自动再将“<font color="00ff00"><textarea</font>”还原回“<font color="0000ff"><textarea</font>”、“<font color="00ff00"></textarea></font>”还原回“<font color="0000ff"></textarea></font>”。<br>
                -----特此提醒使用者！！！
            </td>
        </tr>
        </table>
    </td>
</tr>
</table>
EOF;

    echo $edit;
    }

elseif(!empty($_GET['m'])and $_GET['m'] == "write"){
    // -------------------------------- 写文件 --------------------------------
    $data = stripslashes($_POST['data']);
    $data = str_replace("</textarea>", "</textarea>", $data);
    $data = str_replace("<textarea", "<textarea", $data);
    
    skin_var('title', "写入文件");
    echo "{$style_head}
<table width=750 border=0 bgcolor=666666 cellpadding=2 cellspacing=1>
<tr bgcolor=eeeeee align=center>
<td height=100>";
    if($data != ""){
        $fp = fopen($_POST['id'], "w");
        flock($fp, LOCK_EX);
        $data = str_replace("\r", "", $data);
        fputs($fp, $data);
        fclose($fp);
        
        echo "<meta http-equiv=refresh content=5;URL='?dir={$dir}'><p><b>文件：<font size=3 color=ff0000>{$_POST['id']}</font>保存完毕！</b>";
        }
    else{
        echo "<meta http-equiv=refresh content=5;URL='javascript:history.back(1);'><font size=3 color=ff0000>请输入需要修改的文件名称</font>";
    }
    echo "</td></tr></table>";
    }

elseif(!empty($_GET['m'])and $_GET['m'] == "mkdir"){
    // ------------------------------ 创建新目录 -------------------------------
    skin_var('title', "创建新目录");
    echo "{$style_head}
<table width=750 border=0 bgcolor=666666 cellpadding=2 cellspacing=1>
<tr bgcolor=eeeeee align=center><td height=100>";
    
    if($_GET['id'] != ""){
        if(!file_exists($_GET['id'])){
            mkdir($_GET['id'], 0755);
            echo "<meta http-equiv=refresh content=5;URL='?dir={$dir}'>目录“<font size=3 color=ff0000>{$_GET['id']}</font>”创建成功<br><br>程序5秒钟后自动返回查看";
        }
        else{
            echo "<meta http-equiv=refresh content=5;URL='javascript:history.back(1);'>目录“<font size=3 color=ff0000>{$_GET['id']}</font>”已经存在";
        }
        }
    else{
        echo "<meta http-equiv=refresh content=5;URL='javascript:history.back(1);'><font size=3 color=ff0000>请输入需要新创建的目录名称</font>";
    }
    echo "</td></tr></table>";
    }




elseif(!empty($_GET['m'])and $_GET['m'] == "md5"){
    // -------------------------- 输入需MD5加密的字符 ---------------------------
    skin_var('title', "输入需加密的字符");
    echo "{$style_head}
<table width=750 border=0 bgcolor=666666 cellpadding=2 cellspacing=1>
<form method='post' action='?m=showmd5'>
<tr bgcolor=eeeeee align=center>
<td height=100>
需要加密的字符：<input type=text name=word size=30 maxlength=30>
<input type='submit' value='确定'>
</td></tr></form></table>";
    }




elseif(!empty($_GET['m'])and $_GET['m'] == "showmd5"){
    // ------------------------------ 显示MD5加密后 -----------------------------
    $word = md5($_POST['word']);
    skin_var('title', "显示MD5加密后的字符串");
    echo "{$style_head}
<table width=750 border=0 bgcolor=666666 cellpadding=2 cellspacing=1>
<tr bgcolor=eeeeee align=center>
<td height=100>
<font color=ff0000>经过MD5加密后生成的字符串：</font><input type=text name='word' value='$word' size=40 maxlength=50 readonly>
</td></tr></table>";
    }


elseif(!empty($_GET['m'])and $_GET['m'] == "code"){
    // -------------------------------- 代码生成 --------------------------------
    skin_var('title', "输入代码");
    echo "{$style_head}
<table width=750 border=0 bgcolor=eeeeee cellpadding=2 cellspacing=1>
<tr><td align=center height=150>
<table width=500 border=0 bgcolor=bbbbbb cellpadding=3 cellspacing=1>
<tr><td bgcolor=eeeeee>
这个功能的用处在于生成大量相近的连续代码。
<br>举个例子来说吧：
<br>如果要在网站资料中加上“http://qxbbs.org/”地址下从“001.gif”到“100.gif”的图片，
我难道需要手工一个个插入或是编写代码？
<br>曾经手工编写代码的我做了这个功能。让程序自己产生，而不是我们来编写修改。
<br><br>我们所需要做的只是设置好需要的字符串/要变化部分的最小值/最大值/。
<br>等程序生成后我们再拷贝代码就OK啦！
</td></tr></table>
<hr size=1 color=cccccc>
<form method='post' action='?m=showcode'>
前部字符：<input type=text name='string_q' size=50 maxlength=80 value='http://qxbbs.org/'>
<br>
初始数：<input type=text name='minimum' size=3 maxlength=3 value='1'>
最大数：<input type=text name='max' size=3 maxlength=3 value='100'>
<br>
后部字符：<input type=text name='string_h' size=50 maxlength=80 value='.gif'>
<br>
<input type='submit' value='开始生成'>
</form>
</td></tr></table>";
    }




elseif(!empty($_GET['m'])and $_GET['m'] == "showcode"){
    // -------------------------------- 显示所生成代码 --------------------------------
    $all = $_POST['max'] - $_POST['minimum'] + 1;
    skin_var('title', "显示代码");
    echo "{$style_head}
<table width=750 border=0 bgcolor=666666 cellpadding=2 cellspacing=1>
<tr bgcolor=eeeeee align=center>
<td height=100><b>{$_POST['minimum']}</b> 到 <b>{$_POST['max']}</b> 共 <b>{$all}</b> 项<br>
<textarea name='data' cols=80 rows=19>";
    
    $len = strlen($_POST['minimum']);
    for($i = $_POST['minimum']; $i <= $_POST['max']; $i++){
        $num = $i;
        $x = $len - strlen($i);
        for($x; $x > 0; $x--){
            $num = "0" . $num;
        }
        echo(stripslashes("{$_POST['string_q']}{$num}{$_POST['string_h']}\n"));
        }
    echo "</textarea></td></tr></table>";
    }




elseif(!empty($_GET['m'])and $_GET['m'] == "unixdate"){
    // ------------------------------- unix时间换算 --------------------------------
    skin_var('title', "UNIX时间换算");
    echo "{$style_head}
<table width=750 border=0 bgcolor=666666 cellpadding=2 cellspacing=1>
<tr bgcolor=eeeeee align=center>
<td height=100>
<br>将输入的UNIX时间戳记转换为通用公元年月日时分秒
<br>比如：1067762599 计算为 2003年11月02日 16时11分19秒
<hr size=1>
<form method='post' action='?m=showdate'>输入UNIX时间戳记：<input type=text name=data size=20 maxlength=20><input type='submit' value='开始计算'></form>
<hr size=1>注：UNIX时间是从 1970年1月1日8时1分0秒 为起始的以秒为单位的10进制数值。
</td></tr>
</table>";
    }



elseif(!empty($_GET['m'])and $_GET['m'] == "showdate"){
    // ------------------------------ unix时间转换通常时间 -----------------------------
    $date = date("Y年m月d日 H时m分s秒", $_POST['data']);
    
    skin_var('title', "UNIX时间转换通常时间");
    echo "{$style_head}
<table width=750 border=0 bgcolor=666666 cellpadding=2 cellspacing=1>
<tr bgcolor=eeeeee align=center>
<td height=100>时间：<font size=3>$date</font>
</td></tr></table>";
    }



elseif(!empty($_POST['m'])and $_POST['m'] == "属性"){
    // -------------------------------- 输入属性 --------------------------------
    skin_var('title', "输入属性值");
    echo "{$style_head}
<table width=750 border=0 bgcolor=666666 cellpadding=2 cellspacing=1>
<tr bgcolor=eeeeee><td align=center height=100>";
    
    if(!$_POST['id']['0']){
        error_info("没有选择要修改属性的项目");
    }
    
    while(list($key, $val) = each($_POST['id'])){
        if($key == "0"){
            $items = $_POST['id']['$key'];
        }
        else{
            $items = $items . "|" . $_POST['id']['$key'];
        }
        }
    
    echo "
<form action='?m=chmod&dir={$dir}' method=post><br>属性值：
<input type='text' name='val' value='0755' size=4 maxlength=4>
<input type='hidden' name='items' value='{$items}'>
<input type=submit value='确定修改'>
</td>
</tr></form>
</table>";
    }



elseif(!empty($_GET['m'])and $_GET['m'] == "chmod"){
    // -------------------------------- 修改属性 --------------------------------
    # $val=(integer)$_POST['val'];
    # echo "{$_POST['val']}|".gettype($_POST['val'])."<br>{$val}|".gettype($val)."<br>";
    skin_var('title', "修改属性");
    echo "{$style_head}
<table width=750 border=0 bgcolor=666666 cellpadding=2 cellspacing=1>
<tr bgcolor=eeeeee align=center>
<td height=100>";
    if(!$_POST['items']){
        error_info("!没有选择需要修改属性的目标!");
    }
    
    $id = explode("|", $_POST['items']);
    $val = base_convert($_POST['val'], 8, 10);
    # $val=base_convert($val,10,8);
    for($i = 0; $i < count($id); $i++){
        if(chmod($id['$i'], $val)){
            echo "修改『<font color=ff0000>{$id['$i']}</font>』属性为['<font color=ff0000>{$_POST['val']}</font>']成功<br>";
        }else{;
            echo "“<font color=ff0000>{$id['$i']}</font>”修改属性失败<br>";
        }
        }
    echo "</td></tr></table>";
    }



elseif(!empty($_POST['m'])and $_POST['m'] == "改名"){
    // -------------------------------- 改名确认 --------------------------------
    if(!$_POST['id']['0']){
        error_info("!没有选择需要改名的目标!");
    }
    
    skin_var('title', "改名确认");
    echo "{$style_head}
<table width=750 border=0 bgcolor=666666 cellpadding=2 cellspacing=1>
<tr bgcolor=eeeeee align=center>
<td height=100>
<form action='?m=rename&dir={$dir}' method=post><br>
文件/目录：<input type='text' name='id' value='{$_POST['id']['0']}' size=20 readonly><br>
　 改名为：<input type='text' name='newname' size=20 maxlength=20><br>
<input type=submit value='确定改名'>
</td>
</tr></form></td></tr></table>";
    }



elseif(!empty($_GET['m'])and $_GET['m'] == "rename"){
    // -------------------------------- 修改名称 --------------------------------
    if((!$_POST['id'])or(!$_POST['newname'])){
        error_info("!请选择需要改名的目标，并输入新名称!");
    }
    
    skin_var('title', "修改名称");
    echo "{$style_head}
<table width=750 border=0 bgcolor=666666 cellpadding=2 cellspacing=1>
<tr bgcolor=eeeeee align=center>
<td height=100>";
    
    if(rename($_POST['id'], $_POST['newname'])){
        echo "<font size=3>改名成功</font>";
    }
    else{
        echo "<font size=3 color=ff0000>改名操作失败</font>";
    }
    echo "</td></tr></table>";
    }



elseif(!empty($_POST['m'])and $_POST['m'] == "删除"){
    // -------------------------------- 删除确认 --------------------------------
    skin_var('title', "删除操作确认");
    echo "{$style_head}
<table width=750 border=0 bgcolor=666666 cellpadding=2 cellspacing=1>
<tr bgcolor=eeeeee align=center>
<td height=100>";
    
    if(!$_POST['id']['0']){
        error_info("没有选择要删除的项目<br>");
    }
    $id_all = count($_POST['id']);
    
    echo "<table width=300 border=0 bgcolor=cccccc cellpadding=3 cellspacing=1>";
    
    while(list($key, $val) = each($_POST['id'])){
        if($key == "0"){
            $items = $_POST['id']['$key'];
        }
        else{
            $items = $items . "|" . $_POST['id']['$key'];
        }
        
        if(is_dir($_POST['id']['$key'])){
            $info1 = "目录";
        }else{
            $info1 = "文件";
        }
        if((is_writeable($_POST['id']['$key'])) == 1){
            $info2 = "可删";
        }else{
            $info2 = "<font color=ff0000>不可删</font>";
        }
        echo "<tr bgcolor=eeeeee><td>{$_POST['id']['$key']}</td><td align=center>$info1</td><td align=center>$info2</td></tr>";
        }
    
    echo "</td></tr></table>
<hr size=1>
<font color=ff0000>再次提醒您看清楚路径！误操作将带来不必要的损失！</font><br><font size=3><b>确定删除以上全部 <font color=ff0000>{$id_all}</font> 项？</b></font>
<form action='?m=del&dir={$dir}' method=post>
<input type='hidden' name='items' value='$items'>
<input type=submit value='确定删除'>
</form>
<hr size=1>若删除目录，程序会自动删除目录下一级的文件和空目录（不包括更深层的目录和文件）
</td></tr></table>";
    }

elseif(!empty($_GET['m'])and $_GET['m'] == "del"){
    // -------------------------------- 开始删除 --------------------------------
    skin_var('title', "删除操作");
    echo "{$style_head}
<table width=750 border=0 bgcolor=666666 cellpadding=2 cellspacing=1>
<tr bgcolor=eeeeee align=center>
<td height=100>
<table border=0><tr><td>";
    
    if(!$_POST['items']){
        error_info("没有选择要删除的项目");
    }
    $id = explode("|", $_POST['items']);
    $i_all = count($id);
    echo "删除项目总数:$i_all<hr>";
    
    for($i = 0; $i < $i_all; $i++){
        if(is_dir($id['$i'])){
            
            chdir($id['$i']);
            $open = opendir("./");
            for($ii = 0; $filename = readdir($open); $ii++){
                if(is_dir($filename)){
                    if(($filename != ".")and($filename != "..")){
                        rmdir($filename);
                    }
                    }
                else{
                    unlink($filename);
                }
                }
            chdir("../");
            $open = opendir("./");
            
            if(@rmdir($id['$i'])){
                echo "删除目录：<b>{$id['$i']}</b><br>";
            }
            else{
                echo "<font color=ff0000>删除目录<b>{$id['$i']}失败</b></font><br>";
            }
            
            }
        else{
            if(@unlink($id['$i'])){
                echo "删除文件：<b>{$id['$i']}</b><br>";
            }
            else{
                echo "<font color=ff0000>删除文件<b>{$id['$i']}</b>失败！</font><br>";
            }
            }
        }
    
    echo "
</td></tr></table></td></tr></table>";
    }

elseif(!empty($_GET['m'])and $_GET['m'] == "help"){
    // -------------------------------- 程序说明 --------------------------------
    $phpver = phpversion();
    $os = PHP_OS;
    $df = @round(diskfreespace("/") / 1048576);
    if(get_cfg_var("safe_mode")){
        $safe_mode = "开启";
    }else{
        $safe_mode = "关闭";
    }
    $upfile_max = get_cfg_var("upload_max_filesize");
    $scriptouttime = get_cfg_var("max_execution_time");
    if(get_cfg_var("register_globals")){
        $register_globals = "On";
    }else{
        $register_globals = "Off";
    }
    $post_max_size = get_cfg_var("post_max_size");
    $memory_limit = get_cfg_var("memory_limit");
    
    skin_var('title', "信息说明");

    $allinfo =
<<<EOF
{$style_head}
<table width="750" border="0" bgcolor="666666" cellpadding="2" cellspacing="1">
<tr bgcolor="eeeeee">
    <td height="100">
        <font size="3"><center>我的IP地址：{$_SERVER['REMOTE_ADDR']}</center></font><br>
        <table border="0" bgcolor="aaaaaa" cellpadding="1" cellspacing="1" align="center">
        <tr bgcolor="cccccc">
            <td colspan="2" align="center">主信息：</td>
        </tr>
        <tr bgcolor="eeeeee">
            <td colspan="2">{$_SERVER['SERVER_SIGNATURE']}</td>
        </tr>
        <tr bgcolor="eeeeee">
            <td>操作系统</td>
            <td>{$os}</td>
        </tr>
        <tr bgcolor="eeeeee">
            <td>PHP 版本</td>
            <td>{$phpver}</td>
        </tr>
        <tr bgcolor="eeeeee">
            <td>服务器程序</td>
            <td>{$_SERVER['SERVER_SOFTWARE']}</td>
        </tr>
        <tr bgcolor="eeeeee">
            <td>磁盘剩余空间</td>
            <td>{$df} MB</td>
        </tr>
        <tr bgcolor="eeeeee">
            <td>WWW服务默认路径</td>
            <td>{$_SERVER['DOCUMENT_ROOT']}</td>
        </tr>
        <tr bgcolor="eeeeee">
            <td>当前程序所在路径</td>
            <td>{$_SERVER['SCRIPT_FILENAME']}</td>
        </tr>
        <tr bgcolor="cccccc">
            <td colspan="2" align="center">PHP.ini配置信息：</td>
        </tr>
        <tr bgcolor="eeeeee">
            <td>安全模式</td>
            <td>{$safe_mode}</td>
        </tr>
        <tr bgcolor="eeeeee">
            <td>自动全局变量</td>
            <td>{$register_globals}</td>
        </tr>
        <tr bgcolor="eeeeee">
            <td>最大上传文件</td>
            <td>{$upfile_max}</td>
        </tr>
        <tr bgcolor="eeeeee">
            <td>最大POST上限</td>
            <td>{$post_max_size}</td>
        </tr>
        <tr bgcolor="eeeeee">
            <td>最大使用内存</td>
            <td>{$memory_limit}</td>
        </tr>
        <tr bgcolor="eeeeee">
            <td>脚本超时时间</td>
            <td>{$scriptouttime} sec</td>
        </tr>
        <tr bgcolor="cccccc">
            <td colspan="2" align="center">[<a href='?m=phpinfo'><font color="ff0000">Phpinfo 详细信息！</font></a>]</td>
        </tr>
        </table>
        <br><br><br>
        <b>功能介绍：</b>
        <li>遍历服务器上有足够权限的目录，并列出目录下的文件和子目录信息。
        <li>测试文件是否可以读写。1为可，0为否。
        <li>在可读的情况下，能查看文件的内容。包括该文件里的敏感信息。
        <li>在可写的情况下，能够【上传文件】、【修改属性】、【文件改名】、【编辑文件】、【新建文件】、【新建目录】。
        <li>【批量删除文件和空目录】、【批量修改文件和目录属性】。
        <li>['MD5加密字符']、['批量代码生成']、['UNIX时间戳换算']。
        <li>另外还可返回系统环境信息。
        <li>管理员登陆功能。
        <li>以后会增加更多所能想到的实用功能。
        <br><br>
        <b>注意事项：</b>
        <li><font color="ff0000">本程序可无用设置直接使用。但由于功能强大而存在危险性，所以请改名并放在只有你自己知道的地方。最好配置密码使用！</font>
        <li><font color="ff0000">使用本程序也许会获得某些服务器中的敏感信息，但请勿做非法用途！否则后果自负！ </font>
        <li>由于服务器配置各不相同，无法保证您使用时本程序的全部功能都有效。程序某些功能无法正常执行，返回错误信息也不奇怪。
        <li>若要删除文件和目录，请先把要删除目标所在的目录属性改为777，以确保成功。
        <hr size="1">
        <p align="right">程序修改：白云　　2004-09-12
	</td>
</tr>
</table>
EOF;
echo $allinfo;
    }



elseif(!empty($_GET['m'])and $_GET['m'] == "phpinfo"){
    phpinfo();
    exit;
    }


elseif(!empty($_GET['m'])and $_GET['m'] == "upfile"){
    // -------------------------------- 文件上传 --------------------------------
    if($_FILES['upfile']['name'] == ""){
        error_info("!请选择要上传的文件!<br>不然我怎么知道你要上传哪一个？昏！");
    }
    if(file_exists($_FILES['upfile']['name'])){
        error_info("该目录中已有同名文件，请改名！");
    }
    
    move_uploaded_file($_FILES['upfile']['tmp_name'], $_FILES['upfile']['name']);
    
    skin_var('title', "文件上传");
    echo "{$style_head}
<table width=750 border=0 bgcolor=666666 cellpadding=2 cellspacing=1>
<tr bgcolor=eeeeee>
<td height=200 align=center>
<b><font size=3>文件“<font color=ff0000>{$_FILES['upfile']['name']}</font>”上传完毕！</font></b>
<br>
文件大小：{$_FILES['upfile']['size']} Byte
<hr size=1 width=400>
备注：某些情况下可能需要上传2次才能成功。
</td></tr></table>
<meta http-equiv=refresh content=7;URL='?dir={$dir}'>";
    }



else{
    // -------------------------------- 目录列表 --------------------------------
    if(empty($_GET['showtype'])){
        $showname = "所有文件与目录";
    }
    elseif(!empty($_GET['showtype'])and $_GET['showtype'] == "directory"){
        $showname = "所有目录";
    }
    else{
        $showname = "<b><font face='Tahoma'>*.{$_GET['showtype']}</font></b> 文件";
    }
    
    skin_var('title', "目录列表");
    echo "{$style_head}
<table width=750 border=0 bgcolor=666666 cellpadding=2 cellspacing=1>
<tr bgcolor=888888 align=center><td></td>
<form method='get'>
<td><table width=100% border=0 cellpadding=0 cellspacing=0><tr><td><font color=ffffff>　{$showname}</font></td><td align=right>
<select name='showtype' size=1 onchange=\"window.location=('?dir={$dir}&showtype='+this.options['this.selectedIndex'].value+'');\">
 <option style='BACKGROUND-COLOR: aaaaaa; color=ffffff'>显示条件</option>
 <option value=''>全部显示</option>
 <option value='directory'>< 目录 ></option>
 <option value='html'>*.html</option>
 <option value='htm'>*.htm</option>
 <option value='txt'>*.txt</option>
 <option value='cgi'>*.cgi</option>
 <option value='php'>*.php</option>
 <option value='asp'>*.asp</option>
 <option value='jsp'>*.jsp</option>
 <option value='dat'>*.dat</option>
 <option value='swf'>*.swf</option>
 <option value='gif'>*.gif</option>
 <option value='jpg'>*.jpg</option>
 <option value='png'>*.png</option>
 <option value='zip'>*.zip</option>
 <option value='rar'>*.rar</option>
</select></td></tr></table>
</td></form>
<td><font color=ffffff>文件大小</font></td>
<td><font color=ffffff>创建时间</font></td>
<td><font color=ffffff>修改时间</font></td>
<td><font color=ffffff>属 性</font></td>
<td><font color=ffffff>可读</font></td>
<td><font color=ffffff>可写</font></td>
<td><font color=ffffff>所有者</font></td>
</tr><form method='post'>\n";
    $dir_i = '';
    $file_i = '';
    for($i = 0; $filename = readdir($open); $i++){
        if(is_dir($filename)){
            if((!empty($_GET['showtype'])and $_GET['showtype'] != "")and(!empty($_GET['showtype'])and $_GET['showtype'] != "directory")){
                continue;
            }
            
            if(($filename == ".")or($filename == "..")){
                echo "<tr bgcolor=dddddd align=center><td></td><td align=left><font color=ff9900>['<a href='?dir={$dir}$filename/'>$filename</a>']</font></td>";
            }
            else{
                echo "<tr bgcolor=dddddd align=center><td><input type='checkbox' name='id['']' value='$filename'></td><td align=left><font color=ff9900>['<a href='?dir={$dir}$filename/'>$filename</a>']</font></td>";
            }
            $fileinfo['2'] = "<td>< 目录 >";
            $dir_i++;
            }
        else{
            if(!empty($_GET['showtype'])and $_GET['showtype'] == "directory"){
                continue;
            }
            elseif(!empty($_GET['showtype'])and $_GET['showtype'] != ""){
                if(strtolower(!empty($_GET['showtype'])and $_GET['showtype']) != strtolower(substr(strrchr($filename, "."), 1))){
                    continue;
                }
                }
            
            echo "<tr bgcolor=eeeeee align=center><td><input type='checkbox' name='id['']' value='$filename'></td><td align=left><table width=100% border=0 cellpadding=0 cellspacing=0><tr><td><a href='{$dir}" . urlencode($filename) . "'>$filename</a></td><td align=right><a href='?m=show&id={$filename}&dir={$dir}'>查看</a></td></tr></table></td>";
            $fileinfo['2'] = "<td align=right>" . filesize("{$filename}");
            $file_i++;
            }
        
        echo "{$fileinfo['2']}</td><td>" . date("y-m-d H:i", filectime($filename)) . "</td><td>" . date("y-m-d H:i", filemtime($filename)) . "</td><td>" . substr(decoct(fileperms($filename)), -3) . "</td><td>" . is_readable($filename) . "</td><td>" . is_writeable($filename) . "</td><td>" . fileowner("{$filename}") . "</td></tr>\n";
        }
    echo "<tr bgcolor=888888><td colspan=3>
<input type=hidden name='dir' value='{$dir}'>
<input type='submit' name='m' value='删除'>
<input type='submit' name='m' value='属性'>
<input type='submit' name='m' value='改名'>
</td>
</form>
<td colspan=6 align=center>总共：{$i}个文件和目录　　目录数：{$dir_i}　　文件数：{$file_i}</td></tr>
</table>";
    }

/* ================================程序尾部样式========================= */
$time_end = getmicrotime();
$alltime = $time_end - $time_start;
$footer =
<<<EOF
<table width="750" border="0" bgcolor="666666" cellpadding="3" cellspacing="0">
<tr bgcolor="666666">
    <form action='?m=upfile&dir={$dir}' method='post' enctype='multipart/form-data'>
        <td><input type='file' name='upfile' size="18"><input type='submit' value='上传文件'></td>
    </form>
    <td><a href='?dir=c:/'>Windows[C:] </a><a href='?dir=d:/'> Windows[D:] </a><a href='?dir=/'> Linux[/'] </a>|<a href='?m=help'>说明</a>|</td>
    <form method='get'>
        <td align="right">
            <select name='m' size="1">
                <option value='mkdir'>新建一个目录</option>
                <option value='show'>新建一个文件</option>
            </select>
            <input type="hidden" name='dir' value='{$dir}'><input type="text" name="id" size="15" maxlength="15"><input type="submit" value='确定创建'>
        </td>
    </form>
</tr>
</table>
<table width="750" border="0" cellpadding="3" cellspacing="1">
<tr align="center">
    <td align="left"><font color="666666">程序执行时间：{$alltime} s</font></td>
    <td align="right"><font color="777777" face='Tahoma'>程序修改：白云 2004-09-12</font></td>
</tr>
</table>
</div>
</body>
</html>
EOF;

echo $footer;

?>
