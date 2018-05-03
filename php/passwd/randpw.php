

<?php
//  ./randpw.php?low=on&upp=on&num=on&spe=on&len=1024
header("Content-type:text/html;charset=utf-8");
if(empty($_GET)){
echo html();
}else{
$array = $_GET;
echo rand_char($array);
}
function rand_char($array) { 
    $rand = '';
    $number = '0123456789';
    $lower = 'abcdefghijklmnopqrstuvwxyz';
    $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $special = '!@#$%&*(){}[]^';
	if(empty($array['num'])) $number = '';
	if(empty($array['low'])) $lower = '';
	if(empty($array['upp'])) $upper = '';	
    if(empty($array['spe'])) $special = '';
	$chars = $number . $lower . $upper . $special;
    $base = strlen($chars);
	$num = is_numeric($array['len']) ? true : false;
	if($num == false) $array['len'] = 8;
	if($array['len'] > 4096) $n = '4096';
    empty($array['len']) ? $n = '8' : $n = $array['len'];
	
    for($i = 0;$i < $n;$i++ ){
	    $rand .= $chars[mt_rand(1, $base) - 1];
	}
	return chunk_split($rand,50);
}
function html() { 
$html = <<<HTML
<!DOCTYPE HTML>
<html>
<head><meta charset="utf-8"><title>随机密码生成器</title>
<script type="text/javascript">
	var xhr;
	var outMsg = "";
	
	//-----------------获得XMLHttpRequest对象---------------------------
	function createXMLHttpRequest() {
		try {
			xhr = new ActiveXObject("Msxml2.XMLHTTP");
	    }catch(e) {
			try {
				xhr = new ActiveXObject("Microsoft.XMLHTTP");
			}catch(e) {
				try {
					xhr = new XMLHttpRequest();
				}catch(e) {
				}
			}
		}
	}
	
	//-------------------------------------------------------------
	function createQUeryString() {
		var upp = document.getElementById("upp").value;
		var low = document.getElementById("low").value;
		var num = document.getElementById("num").value;	
		var spe = document.getElementById("spe").value;
		var len = document.getElementById("len").value;	
		var queryString = "low="+low+"&"+"upp="+upp+"&"+"num="+num+"&"+"spe="+spe+"&"+"len="+len;
		return queryString;
	}
	
	function doRequest() {
		createXMLHttpRequest();//获得XMLHttpRequest对象
		var queryString = "http://127.0.0.1/randpw.php?";
		queryString = queryString+createQUeryString();
		xhr.onreadystatechange = handleStateChange;//定义事件处理器，此处理器将在服务器端返回数据时自动被触发执行
		xhr.open("GET",queryString,true);//与服务器连接并发送
		xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded;");
		xhr.send(null);
	}

	
	function handleStateChange() {
		if(xhr.readyState==4) {//4表示数据已经调用完成
			if(xhr.status==200) {//HTTP的状态码
				parseResults();
			}
		}
	}
	
	function parseResults() {
		var responseDiv = document.getElementById("serverResponse");//获得Div对象的元素
		if(responseDiv.hasChildNodes()) {
			responseDiv.removeChild(responseDiv.childNodes[0]);
		}
		var responseText  = document.createTextNode(xhr.responseText);
		responseDiv.appendChild(responseText);
	}
</script>
</head>
<body>
<br><br><center><span><b>生成随机密码</b></span></center><br>
<form method=get action=randpw.php>
<table width="95%" style="max-width:700px;" border="0" cellpadding="0" cellspacing="0" bgcolor="#C5D5C5" align="center">
<tr><td>
<table width="100%" border="0" cellpadding="8" cellspacing="1">
<tr>
<td width="25%" bgcolor="#F5F5F5" align="center">所用字符</td>
<td width="75%" bgcolor="#FFFFFF">
<span style="display:inline-block"><input id="upp" name="upp" type="checkbox" checked="checked" /><label for="upp" >A-Z</label></span>
<span style="display:inline-block"><input id="low" name="low" type="checkbox" checked="checked" /><label for="low" >a-z</label></span>
<span style="display:inline-block"><input id="num" name="num" type="checkbox" checked="checked" /><label for="num" >0-9</label></span>
<span style="display:inline-block"><input id="spe" name="spe" type="checkbox"/><label for="spe"><input type="text" value="!@#$%^&*" size="2" disabled="disabled" /></label></span>
</td>
</tr>
<tr>
<td bgcolor="#F5F5F5" align="center">密码长度</td>
<td bgcolor="#FFFFFF" class="shuru_div">
<span style="display:inline-block"><input id="len" name="len" type="text" value="默认 8，最大 4096" /><label for="len" >位</label></span>
</td>
</tr>
<tr>
<td bgcolor="#F5F5F5" align="center">生成结果</td>
<td bgcolor="#FFFFFF" ><div id="serverResponse"></div></td>
</tr>
</table>
</td></tr>
</table>
<div align="center">
<br /><input type="submit" value="生成密码" />
		<input type="reset" value="重置">
		<input type="button" value="提交" id="button1" onclick="javascript:doRequest()">
</form>
</div>
<br />
</body>
</html>

HTML;
 
echo $html;
}


