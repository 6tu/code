<?php
// Line 22-80 , 153-154 and 262 is changed

$dbhost = 'localhost';
$dbport = '3306';
$dbuser = 'root';
$dbpass = 'password';
$dbname = 'phpsocks5';

$secretkey = 'i8CSU0oa';
$debuginfo = True;
$prefix = 'n0MaQbxFnXjhOOwjsgJqe9Zc7AhLRI';
$postfix = 'XJ2o02qqsiSIbBBWWbgGV310mmN6di';

$dbprefix = 'phpsocks5_';
$invstep = 100000;
$invmax = 3000000;

$content_type = 'image/png';
$sesscookiekey = 'PHPSESSID';
$version = "03";

// *************** PHP7 START ***************
if(!function_exists('mysql_pconnect')){
    $mysqli = mysqli_connect("$dbhost:$dbport", $dbuser, $dbpass, $dbname);
    function mysql_pconnect($dbhost, $dbuser, $dbpass){
        global $dbport;
        global $dbname;
        global $mysqli;
        $mysqli = mysqli_connect("$dbhost:$dbport", $dbuser, $dbpass, $dbname);
        return $mysqli;
        }
    function mysql_select_db($dbname){
        global $mysqli;
	    return mysqli_select_db($mysqli,$dbname);
        }
    function mysql_fetch_array($result){
        return mysqli_fetch_array($result);
        }
    function mysql_fetch_assoc($result){
        return mysqli_fetch_assoc($result);
        }
    function mysql_fetch_row($result){
        return mysqli_fetch_row($result);
        }
    function mysql_query($query){
        global $mysqli;
	    return mysqli_query($mysqli,$query);
        }
    function mysql_escape_string($data){
        global $mysqli;
		return mysqli_real_escape_string($mysqli, $data);
        //return addslashes(trim($data));
        }
    function mysql_real_escape_string($data){
        return mysql_real_escape_string($data);
		}
	function mysql_close(){
	    global $mysqli;
        return mysqli_close($mysqli);
        }
}
function config(){
    global $secretkey;
    global $prefix;
    global $postfix;
	$serverurl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
	$localport = '10080';
	$localhost = '127.0.0.1';
	$config = "<br><br>\r\n ----------phpsocks5.properties----------<br><br>\r\n";
	$config .= 'serverurl=' . $serverurl . "<br>\r\n";
	$config .= 'secretkey=' . $secretkey. "<br>\r\n";
	$config .= 'prefix=' . $prefix. "<br>\r\n";
	$config .= 'postfix=' . $postfix. "<br>\r\n";
	$config .= 'localport=' . $localport. "<br>\r\n";
	$config .= 'localhost=' . $localhost. "<br>\r\n";
    return $config;
}
// *************** PHP7 END ***************

function phpsocks5_encrypt($datastr)
{
	global $secretkey;
	$encrypted = '';
	for($i = 0; $i < strlen($datastr); $i++)
		$encrypted .= chr(ord($datastr[$i]) ^ ord($secretkey[$i % strlen($secretkey)]));
	return $encrypted;
}

function phpsocks5_decrypt($datastr)
{
	return phpsocks5_encrypt($datastr);
}

function phpsocks5_tohex($datastr)
{
	global $debuginfo;
	if(!$debuginfo)
		return '';
	$hexstr = bin2hex($datastr);
	$hexstr .= '(';
	for($i = 0; $i < strlen($datastr); $i++)
	{
		if($datastr[$i] < ' ' || $datastr[$i] > '~')
			$hexstr .= '.';
		else
			$hexstr .= $datastr[$i];
	}
	$hexstr .= ')';
	return $hexstr;
}

function phpsocks5_log($message)
{
	global $dbprefix;
	global $debuginfo;
	if(!$debuginfo)
		return;
	error_log(date(DATE_RFC1123) . "\t" . $message . "\n", 3, $dbprefix . "log.log");
}

function phpsocks5_http_500($errmsg)
{
	header('HTTP/1.1 500');
	echo phpsocks5_encrypt(date(DATE_RFC1123) . "\t" . $errmsg);
	mysql_close();
	phpsocks5_log("http_500_" . $errmsg);
	exit;
}

function phpsocks5_usleep($usec)
{
	global $dbhost;
	global $dbport;
	global $dbuser;
	global $dbpass;
	global $dbname;
	phpsocks5_log("sleep process 1");
	mysql_close();
	phpsocks5_log("sleep process 2");
	usleep($usec);
	phpsocks5_log("sleep process 3");
	if(!mysql_pconnect("$dbhost:$dbport", $dbuser, $dbpass))
		phpsocks5_http_500('mysql_pconnect error');
	if(!mysql_select_db($dbname))
		phpsocks5_http_500('mysql_select_db error');
	if(!mysql_query('SET AUTOCOMMIT=1'))
		phpsocks5_http_500('mysql_query SET AUTOCOMMIT=1 error');
	phpsocks5_log("sleep process 4");
}

phpsocks5_log("process 1");

if(!isset($_COOKIE[$sesscookiekey])) $_COOKIE[$sesscookiekey]='';
$phpsid = mysql_escape_string($_COOKIE[$sesscookiekey]);

phpsocks5_log("process 2 $phpsid");

set_time_limit(30);

phpsocks5_log("process 3 $phpsid");

if(!mysql_pconnect("$dbhost:$dbport", $dbuser, $dbpass))
	phpsocks5_http_500('mysql_pconnect error');
if(!mysql_select_db($dbname))
	phpsocks5_http_500('mysql_select_db error');
if(!mysql_query('SET AUTOCOMMIT=1'))
	phpsocks5_http_500('mysql_query SET AUTOCOMMIT=1 error');

phpsocks5_log("process 4 $phpsid");

$postdata = file_get_contents("php://input");

phpsocks5_log("process 5 $phpsid before decrypt postdata: " . phpsocks5_tohex($postdata));

$postdata = phpsocks5_decrypt($postdata);

phpsocks5_log("process 6 $phpsid after decrypt postdata: " . phpsocks5_tohex($postdata));

if(!$postdata)
{
	phpsocks5_log("create table process");
	$dbversion = 0;
	if(!mysql_query("SELECT * FROM ${dbprefix}conning"))
		$dbversion = 0;
	else
	{
		$rslt = mysql_query("SELECT * FROM ${dbprefix}dbversion");
		if(!$rslt)
			$dbversion = 1;
		else
		{
			$row = mysql_fetch_row($rslt);
			if(!$row)
			{
				if($debuginfo)
					echo 'create table process fetch dbversion row error.';
				mysql_close();
				exit;
			}
			$dbversion = $row[0];
		} 
	}
	if($dbversion == 0)
	{
		if(!mysql_query("CREATE TABLE ${dbprefix}conning (  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,  sid VARCHAR(200) NOT NULL,  host VARCHAR(512) NOT NULL,  port INTEGER NOT NULL,  PRIMARY KEY (id))"))
		{
			if($debuginfo)
				echo 'Create table 1 error.';
			mysql_close();
			exit;
		}
		if(!mysql_query("CREATE TABLE ${dbprefix}sending (  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,  sid VARCHAR(200) NOT NULL,  cnt VARCHAR(8192) NOT NULL,  PRIMARY KEY (id))"))
		{
			if($debuginfo)
				echo 'Create table 2 error.';
			mysql_close();
			exit;
		}
		if(!mysql_query("CREATE TABLE ${dbprefix}recving (  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,  sid VARCHAR(200) NOT NULL,  cnt VARCHAR(8192) NOT NULL,  PRIMARY KEY (id))"))
		{
			if($debuginfo)
				echo 'Create table 3 error.';
			mysql_close();
			exit;
		}
		$dbversion = 1;
	}
	if($dbversion == 1)
	{
		if(!mysql_query("ALTER TABLE ${dbprefix}sending CHANGE COLUMN cnt cnt MEDIUMTEXT NOT NULL  ;"))
		{
			if($debuginfo)
				echo 'Alter table 2 error.';
			mysql_close();
			exit;
		}
		if(!mysql_query("ALTER TABLE ${dbprefix}recving CHANGE COLUMN cnt cnt MEDIUMTEXT NOT NULL  ;"))
		{
			if($debuginfo)
				echo 'Alter table 3 error.';
			mysql_close();
			exit;
		}
		if(!mysql_query("CREATE TABLE ${dbprefix}dbversion (  dbversion INT NOT NULL )"))
		{
			if($debuginfo)
				echo 'Create table 4 error.';
			mysql_close();
			exit;
		}
		if(!mysql_query("INSERT INTO ${dbprefix}dbversion VALUES (2)"))
		{
			if($debuginfo)
				echo 'Init table 4 error.';
			mysql_close();
			exit;
		}
		$dbversion = 2;
	}
	mysql_close();
	if($debuginfo)
		echo 'Create tables successfully.' . config();
	exit;
}

header("Content-Type: $content_type");

if($postdata[0] != $version[0] || $postdata[1] != $version[1])
	phpsocks5_http_500('version not match');

phpsocks5_log("process 7");

if($postdata[2] == "1")
{
	phpsocks5_log("connect process 1");
	if(!session_start())
		phpsocks5_http_500('session_start error');
	phpsocks5_log("connect process 2");
	$host = mysql_escape_string(strtok(substr($postdata, 3), ':'));
	$port = mysql_escape_string(strtok(':'));
	$phpsid = mysql_escape_string(session_id());
	phpsocks5_log("connect process 3 $phpsid");
	mysql_query("DELETE FROM ${dbprefix}conning WHERE sid = '" . $phpsid . "'");
	mysql_query("DELETE FROM ${dbprefix}sending WHERE sid = '" . $phpsid . "'");
	mysql_query("DELETE FROM ${dbprefix}recving WHERE sid = '" . $phpsid . "'");
	phpsocks5_log("connect process 4 $phpsid");
	if(!mysql_query("INSERT INTO ${dbprefix}conning (sid, host, port) VALUES ('" . session_id() . "', '$host', '$port')"))
		phpsocks5_http_500('mysql_query INSERT error');
	phpsocks5_log("connect process 5 $phpsid");
}
elseif($postdata[2] == "2")
{
	phpsocks5_log("background process 1 $phpsid");
	$inv = 0;
	$rslt = mysql_query("SELECT id, host, port FROM ${dbprefix}conning WHERE sid = '" . $phpsid . "'");
	if(!$rslt)
		phpsocks5_http_500('mysql_query SELECT error');
	phpsocks5_log("background process 2 $phpsid");
	$row = mysql_fetch_row($rslt);
	phpsocks5_usleep(0);
	if(!$row)
		phpsocks5_http_500('mysql_fetch_row error');
	phpsocks5_log("background process 3 $phpsid");
	$rmtskt = fsockopen($row[1], $row[2]);
	phpsocks5_log("background process 4 $phpsid");
	if(!$rmtskt)
	{
		mysql_query("DELETE FROM ${dbprefix}conning WHERE id = $row[0]");
		phpsocks5_http_500('fsockopen error');
	}
	phpsocks5_log("background process 5 $phpsid");
	if(!stream_set_blocking($rmtskt, 0))
		phpsocks5_http_500('stream_set_blocking error');
	phpsocks5_log("background process 6 $phpsid");
	while(true)
	{
		$noop = true;
		phpsocks5_log("background process 7 $phpsid");
		if(feof($rmtskt))
			phpsocks5_http_500('feof');
		phpsocks5_log("background process 8 $phpsid");
		$cnt = fread($rmtskt, 1048576);
		phpsocks5_log("background process 9 $phpsid");
		if($cnt)
		{
			phpsocks5_log("background process 10 $phpsid fread: " . phpsocks5_tohex($cnt));
			if(!mysql_query("INSERT INTO ${dbprefix}recving (sid, cnt) VALUES ('" . $phpsid . "', '" . base64_encode($cnt) . "')"))
				phpsocks5_http_500('mysql_query INSERT error');
			phpsocks5_usleep(0);
			phpsocks5_log("background process 11 $phpsid");
			$noop = false;
		}
		phpsocks5_log("background process 12 $phpsid");
		phpsocks5_usleep($inv);
		phpsocks5_log("background process 13 $phpsid");
		$rslt = mysql_query("SELECT id, cnt FROM ${dbprefix}sending WHERE sid = '" . $phpsid . "' ORDER BY id ASC LIMIT 1");
		if(!$rslt)
			phpsocks5_http_500('mysql_query SELECT error');
		$row = mysql_fetch_row($rslt);
		phpsocks5_usleep(0);
		phpsocks5_log("background process 14 $phpsid");
		if($row)
		{
			$noop = false;
			phpsocks5_log("background process 15 $phpsid");
			mysql_query("DELETE FROM ${dbprefix}sending WHERE id = $row[0]");
			phpsocks5_usleep(0);
			phpsocks5_log("background process 16 $phpsid");
			if(!$row[1])
				phpsocks5_http_500('break');
			$cnt = base64_decode($row[1]);
			phpsocks5_log("background process 17 $phpsid fwrite: " . phpsocks5_tohex($cnt));
			if(!fwrite($rmtskt, $cnt))
				phpsocks5_http_500('fwrite error');
			phpsocks5_log("background process 18 $phpsid");
		}
		if($noop)
		{
			phpsocks5_log("background process 19 $phpsid");
			$inv += $invstep;
			if($inv > $invmax)
				$inv = $invmax;
		}
		else
		{
			phpsocks5_log("background process 20 $phpsid");
			set_time_limit(30);
			phpsocks5_log("background process 21 $phpsid");
			$inv = 0;
		}
		phpsocks5_usleep($inv);
		phpsocks5_log("background process 22 $phpsid");
	}
}
elseif($postdata[2] == "3")
{
	phpsocks5_log("send process 1 $phpsid");
	if(!mysql_query("INSERT INTO ${dbprefix}sending (sid, cnt) VALUES ('" . $phpsid . "', '" . base64_encode(substr($postdata, 3)) . "')"))
		phpsocks5_http_500('mysql_query INSERT INTO error');
}
elseif($postdata[2] == "4")
{
	phpsocks5_log("receive process 1 $phpsid");
	$inv = 0;
	while(true)
	{
		phpsocks5_log("receive process 2 $phpsid");
		$rslt = mysql_query("SELECT id, cnt FROM ${dbprefix}recving WHERE sid = '" . $phpsid . "' ORDER BY id ASC LIMIT 1");
		if(!$rslt)
			phpsocks5_http_500('mysql_query SELECT error');
		phpsocks5_log("receive process 3 $phpsid");
		$row = mysql_fetch_row($rslt);
		phpsocks5_usleep(0);
		if($row)
		{
			phpsocks5_log("receive process 4 $phpsid");
			mysql_query("DELETE FROM ${dbprefix}recving WHERE id = $row[0]");
			phpsocks5_usleep(0);
			if($row[1])
			{
				$cnt = base64_decode($row[1]);
				phpsocks5_log("receive process 5 $phpsid echo: " . phpsocks5_tohex($cnt));
				echo $prefix . phpsocks5_encrypt($cnt) . $postfix;
			}
			else
				phpsocks5_http_500('break');
			phpsocks5_log("receive process 6 $phpsid");
			break;
		}
		phpsocks5_log("receive process 7 $phpsid");
		$inv += $invstep;
		if($inv > $invmax)
			$inv = $invmax;
		phpsocks5_log("receive process 8 $phpsid");
		phpsocks5_usleep($inv);
		phpsocks5_log("receive process 9 $phpsid");
	}
}
elseif($postdata[2] == "5")
{
	phpsocks5_log("close process 1 $phpsid");
	mysql_query("INSERT INTO ${dbprefix}sending (sid, cnt) VALUES ('" . $phpsid . "', '')");
	mysql_query("INSERT INTO ${dbprefix}recving (sid, cnt) VALUES ('" . $phpsid . "', '')");
	phpsocks5_log("close process 2 $phpsid");
}

phpsocks5_log("process 8 $phpsid");

mysql_close();
?>
