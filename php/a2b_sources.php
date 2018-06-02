<?PHP

error_reporting(0);
ini_set("memory_limit", "128M");
@ob_end_clean();
ob_implicit_flush(true);

$nn = "\r\n";
define('VERSION', "0.35");

foreach($_POST as $key => $value)
  {
    $_GET[$key] = $value;
  }

if(@$_GET['act'] == "ftpget")
  {

/*------------------
| Download Handler |
------------------*/

/*
 @author Nguyen Quoc Bao <quocbao.coder@gmail.com>
 visit http://en.vietapi.com/wiki/index.php/PHP:_HttpDownload for class information
 Please send me an email if you find some bug or it doesn't work with download manager.
 I've tested it with
 - Reget : http://reget.com
 - FDM : http://freefiledownloadmanager.org
 @version 1.2
 @desc A simple object for processing download operation , support section downloading
 @distribution It's free as long as you keep this header .
 @sample

 1: File Download
 $object = new httpdownload;
 $object->set_byfile($filename); //Download from a file
 $object->use_resume = true; //Enable Resume Mode
 $object->download(); //Download File

 2: Data Download
  $object = new httpdownload;
 $object->set_bydata($data); //Download from php data
 $object->use_resume = true; //Enable Resume Mode
 $object->set_filename($filename); //Set download name
 $object->set_mime($mime); //File MIME (Default: application/otect-stream)
 $object->download(); //Download File

 3: Manual Download
 $object = new httpdownload;
 $object->set_filename($filename);
$object->download_ex($size);
//output your data here , remember to use $this->seek_start and $this->seek_end value :)

*/

class httpdownload {

/*----------------
| Class Variable |
----------------*/
/**
 $handler : Object Handler
 $use_resume : use section download
 $use_autoexit : auto stop after finishing download
 $use_auth : use authentication download
 $data : Download Data
 $data_len : Download Data Len
 $data_type : Download Data Type
 $data_mod : Last modified time
 $filename : Download File Name
 $mime : File mime
 $bufsize : BUFFER SIZE
 $seek_start : Start Seek
 $seek_end : End Seek
**/
var $handler = array('auth' => false ,'header' => false,'fopen'=>false,'fclose'=>false,'fread'=>false,'fseek' => false);
var $use_resume = true;
var $use_autoexit = true;
var $use_auth = false;
var $data = null;
var $data_len = 0;
var $data_mod = 0;
var $filename = null;
var $mime = null;
var $bufsize = 2048;
var $seek_start = 0;
var $seek_end = -1;

/*-------------------
| Download Function |
-------------------*/
/**
 pre_download() : Pre Download Function
 download() : Download all file
 set_byfile() : Set data download by file
 set_bydata() : Set data download by data
 set_byurl() : Set data download by url
 set_filename() : Set file name
 set_mime() : Set file mime
 download_header() : Send header
 download_ex() : Manual Download
**/
function pre_download() {
global $HTTP_SERVER_VARS;
if ($this->use_auth) { //use authentication
if (!$this->_auth()) { //no authentication
$this->_header('WWW-Authenticate: Basic realm="Please enter your username and password"');
    $this->_header('HTTP/1.0 401 Unauthorized');
    $this->_header('status: 401 Unauthorized');
    if ($this->use_autoexit) exit();
return false;
}
}
if ($this->mime == null) $this->mime = "application/octet-stream"; //default mime
if (isset($_SERVER['HTTP_RANGE']) || isset($HTTP_SERVER_VARS['HTTP_RANGE'])) {
if (isset($HTTP_SERVER_VARS['HTTP_RANGE'])) $seek_range = substr($HTTP_SERVER_VARS['HTTP_RANGE'] , strlen('bytes='));
else $seek_range = substr($_SERVER['HTTP_RANGE'] , strlen('bytes='));
$range = explode('-',$seek_range);
if ($range[0] > 0) {
$this->seek_start = intval($range[0]);
}
if ($range[1] > 0) $this->seek_end = intval($range[1]);
else $this->seek_end = -1;
} else {
$this->seek_start = 0;
$this->seek_end = -1;
}
if ($this->seek_start < 0 || !$this->use_resume) $this->seek_start = 0;
// echo $this->seek_start."-".$this->seek_end;
return true;
}
function download_ex($size) {
if (!$this->pre_download()) return false;
ignore_user_abort(true);
@set_time_limit(0);
//Use seek end here
if ($this->seek_start > ($size - 1)) $this->seek_start = 0;
if ($this->seek_end <= 0) $this->seek_end = $size - 1;
$this->download_header($size,$this->seek_start,$this->seek_end);
$this->data_mod = time();
return true;
}
function download() {
if (!$this->pre_download()) return false;
$seek = $this->seek_start;

ignore_user_abort(true);
@set_time_limit(0);

$size = $this->data_len;

if ($this->data_type == 0) {
$size = filesize($this->data);
if ($seek > ($size - 1)) $seek = 0;
if ($this->filename == null) $this->filename = basename($this->data);
$res =& $this->_fopen($this->data,'rb');
if ($seek) $this->_fseek($res , $seek);
if ($this->seek_end < $seek) $this->seek_end = $size - 1;
$this->download_header($size,$seek,$this->seek_end); //always use the last seek
$size = $this->seek_end - $seek + 1;
while (!connection_aborted() && $size > 0) {
if ($size < $this->bufsize) echo $this->_fread($res , $size);
else echo $this->_fread($res , $this->bufsize);
$size -= $this->bufsize;
}
$this->_fclose($res);
} else if ($this->data_type == 1) {
if ($seek > ($size - 1)) $seek = 0;
if ($this->seek_end < $seek) $this->seek_end = $this->data_len - 1;
$this->data = substr($this->data , $seek , $this->seek_end - $seek + 1);
if ($this->filename == null) $this->filename = time();
$size = strlen($this->data);
$this->download_header($this->data_len,$seek,$this->seek_end);
while (!connection_aborted() && $size > 0) {
echo substr($this->data , 0 , $this->bufsize);
$this->data = substr($this->data , $this->bufsize);
$size -= $this->bufsize;
}
} else if ($this->data_type == 2) {
//just send a redirect header
header('location : ' . $this->data);
}
if ($this->use_autoexit) exit();
return true;
}
function download_header($size,$seek_start=null,$seek_end=null) {
$this->_header('Content-type: ' . $this->mime);
$this->_header('Content-Disposition: attachment; filename="' . $this->filename . '"');
$this->_header('Last-Modified: ' . date('D, d M Y H:i:s \G\M\T' , $this->data_mod));
if ($seek_start && $this->use_resume) {
$this->_header("Content-Length: " . ($seek_end - $seek_start + 1));
$this->_header('Accept-Ranges: bytes');
$this->_header("HTTP/1.0 206 Partial Content");
$this->_header("status: 206 Partial Content");
$this->_header("Content-Range: bytes $seek_start-$seek_end/$size");
} else {
$this->_header("Content-Length: $size");
}
}
function set_byfile($dir) {
if (is_readable($dir) && is_file($dir)) {
$this->data_len = 0;
$this->data = $dir;
$this->data_type = 0;
$this->data_mod = filemtime($dir);
return true;
} else return false;
}
function set_bydata($data) {
if ($data == '') return false;
$this->data = $data;
$this->data_len = strlen($data);
$this->data_type = 1;
$this->data_mod = time();
return true;
}

function set_byurl($data) {
$this->data = $data;
$this->data_len = 0;
$this->data_type = 2;
return true;
}

function set_filename($filename) {
$this->filename = $filename;
}
function set_mime($mime) {
$this->mime = $mime;
}
function set_lastmodtime($time) {
$time = intval($time);
if ($time <= 0) $time = time();
$this->data_mod = $time;
}

/*----------------
| Other Function |
----------------*/
/**
 header() : Send HTTP Header
**/
function _header($var) {
if ($this->handler['header']) return @call_user_func($this->handler['header'],$var);
else return header($var);
}
function &_fopen($file,$mode) {
if ($this->handler['fopen']) return @call_user_func($this->handler['fopen'],$file,$mode);
else return fopen($file,$mode);
}
function _fclose($res) {
if ($this->handler['fclose']) return @call_user_func($this->handler['fclose'],$res);
else return fclose($res);
}
function _fseek($res,$len) {
if ($this->handler['fseek']) return @call_user_func($this->handler['fseek'],$res,$len);
else return fseek($res,$len);
}
function &_fread($file,$size) {
if ($this->handler['fread']) return @call_user_func($this->handler['fread'],$file,$size);
else return fread($file,$size);
}
function _auth() {
if (!isset($_SERVER['PHP_AUTH_USER'])) return false;
if ($this->handler['auth']) return @call_user_func($this->handler['auth'],$_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);
else return true; //you must use a handler
}

}

if(!defined('CRLF')) define('CRLF',"\r\n");
if(!defined("FTP_AUTOASCII")) define("FTP_AUTOASCII", -1);
if(!defined("FTP_BINARY")) define("FTP_BINARY", 1);
if(!defined("FTP_ASCII")) define("FTP_ASCII", 0);
if(!defined('FTP_FORCE')) define('FTP_FORCE', TRUE);
define('FTP_OS_Unix','u');
define('FTP_OS_Windows','w');
define('FTP_OS_Mac','m');

class ftp_base {
/* Public variables */
var $LocalEcho=FALSE;
var $Verbose=FALSE;
var $OS_local;

/* Private variables */
var $_lastaction=NULL;
var $_errors;
var $_type;
var $_umask;
var $_timeout;
var $_passive;
var $_host;
var $_fullhost;
var $_port;
var $_datahost;
var $_dataport;
var $_ftp_control_sock;
var $_ftp_data_sock;
var $_ftp_temp_sock;
var $_login;
var $_password;
var $_connected;
var $_ready;
var $_code;
var $_message;
var $_can_restore;
var $_port_available;

var $_error_array=array();
var $AuthorizedTransferMode=array(
FTP_AUTOASCII,
FTP_ASCII,
FTP_BINARY
);
var $OS_FullName=array(
FTP_OS_Unix => 'UNIX',
FTP_OS_Windows => 'WINDOWS',
FTP_OS_Mac => 'MACOS'
);
var $NewLineCode=array(
FTP_OS_Unix => "\n",
FTP_OS_Mac => "\r",
FTP_OS_Windows => "\r\n"
);
var $AutoAsciiExt=array("ASP","BAT","C","CPP","CSV","H","HTM","HTML","SHTML","INI","LOG","PHP","PHP3","PL","PERL","SH","SQL","TXT");

/* Constructor */
function ftp_base($port_mode=FALSE) {
$this->_port_available=($port_mode==TRUE);
$this->SendMSG("Staring FTP client class with".($this->_port_available?"":"out")." PORT mode support");
$this->_connected=FALSE;
$this->_ready=FALSE;
$this->_can_restore=FALSE;
$this->_code=0;
$this->_message="";
$this->SetUmask(0022);
$this->SetType(FTP_AUTOASCII);
$this->SetTimeout(30);
$this->Passive(!$this->_port_available);
$this->_login="anonymous";
$this->_password="anon@ftp.com";
    $this->OS_local=FTP_OS_Unix;
if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $this->OS_local=FTP_OS_Windows;
elseif(strtoupper(substr(PHP_OS, 0, 3)) === 'MAC') $this->OS_local=FTP_OS_Mac;
}

// <!-- --------------------------------------------------------------------------------------- -->
// <!--       Public functions                                                                  -->
// <!-- --------------------------------------------------------------------------------------- -->
function parselisting($list) {
//Parses i line like:"drwxrwx---  2 owner group 4096 Apr 23 14:57 text"
if(preg_match("/^([-ld])([rwxst-]+)\s+(\d+)\s+([-_\w]+)\s+([-_\w]+)\s+(\d+)\s+(\w{3})\s+(\d+)\s+([\:\d]+)\s+(.+)$/i", $list, $ret)) {
$v=array(
"type"=> ($ret[1]=="-"?"f":$ret[1]),
"perms"=> 0,
"inode"=> $ret[3],
"owner"=> $ret[4],
"group"=> $ret[5],
"size"=> $ret[6],
"date"=> $ret[7]." ".$ret[8]." ".$ret[9],
"name"=> $ret[10]
);
$v["perms"]+=00400*(int)($ret[2]{0}=="r");
$v["perms"]+=00200*(int)($ret[2]{1}=="w");
$v["perms"]+=00100*(int)in_array($ret[2]{2}, array("x","s"));
$v["perms"]+=00040*(int)($ret[2]{3}=="r");
$v["perms"]+=00020*(int)($ret[2]{4}=="w");
$v["perms"]+=00010*(int)in_array($ret[2]{5}, array("x","s"));
$v["perms"]+=00004*(int)($ret[2]{6}=="r");
$v["perms"]+=00002*(int)($ret[2]{7}=="w");
$v["perms"]+=00001*(int)in_array($ret[2]{8}, array("x","t"));
$v["perms"]+=04000*(int)in_array($ret[2]{2}, array("S","s"));
$v["perms"]+=02000*(int)in_array($ret[2]{5}, array("S","s"));
$v["perms"]+=01000*(int)in_array($ret[2]{8}, array("T","t"));
}
return $v;
}

function SendMSG($message = "", $crlf=true) {
if ($this->Verbose) {
echo $message.($crlf?CRLF:"");
flush();
}
return TRUE;
}

function SetType($mode=FTP_AUTOASCII) {
if(!in_array($mode, $this->AuthorizedTransferMode)) {
$this->SendMSG("Wrong type");
return FALSE;
}
$this->_type=$mode;
$this->_data_prepare($mode);
$this->SendMSG("Transfer type: ".($this->_type==FTP_BINARY?"binary":($this->_type==FTP_ASCII?"ASCII":"auto ASCII") ) );
return TRUE;
}

function Passive($pasv=NULL) {
if(is_null($pasv)) $this->_passive=!$this->_passive;
else $this->_passive=$pasv;
if(!$this->_port_available and !$this->_passive) {
$this->SendMSG("Only passive connections available!");
$this->_passive=TRUE;
return FALSE;
}
$this->SendMSG("Passive mode ".($this->_passive?"on":"off"));
return TRUE;
}

function SetServer($host, $port=21, $reconnect=true) {
if(!is_long($port)) {
        $this->verbose=true;
        $this->SendMSG("Incorrect port syntax");
return FALSE;
} else {
$ip=@gethostbyname($host);
        $dns=@gethostbyaddr($host);
        if(!$ip) $ip=$host;
        if(!$dns) $dns=$host;
if(ip2long($ip) === -1) {
$this->SendMSG("Wrong host name/address \"".$host."\"");
return FALSE;
}
        $this->_host=$ip;
        $this->_fullhost=$dns;
        $this->_port=$port;
        $this->_dataport=$port-1;
}
$this->SendMSG("Host \"".$this->_fullhost."(".$this->_host."):".$this->_port."\"");
if($reconnect){
if($this->_connected) {
$this->SendMSG("Reconnecting");
if(!$this->quit(FTP_FORCE)) return FALSE;
if(!$this->connect()) return FALSE;
}
}
return TRUE;
}

function SetUmask($umask=0022) {
$this->_umask=$umask;
umask($this->_umask);
$this->SendMSG("UMASK 0".decoct($this->_umask));
return TRUE;
}

function SetTimeout($timeout=30) {
$this->_timeout=$timeout;
$this->SendMSG("Timeout ".$this->_timeout);
if($this->_connected)
if(!$this->_settimeout($this->_ftp_control_sock)) return FALSE;
return TRUE;
}

function connect() {
    $this->SendMsg('Local OS : '.$this->OS_FullName[$this->OS_local]);
if(!($this->_ftp_control_sock = $this->_connect($this->_host, $this->_port))) {
$this->SendMSG("Error : Cannot connect to remote host \"".$this->_fullhost." :".$this->_port."\"");
return FALSE;
}
$this->SendMSG("Connected to remote host \"".$this->_fullhost.":".$this->_port."\". Waiting for greeting.");
do {
if(!$this->_readmsg()) return FALSE;
if(!$this->_checkCode()) return FALSE;
$this->_lastaction=time();
} while($this->_code<200);
$this->_ready=true;
return TRUE;
}

function quit($force=false) {
if($this->_ready) {
if(!$this->_exec("QUIT") and !$force) return FALSE;
if(!$this->_checkCode() and !$force) return FALSE;
$this->_ready=false;
$this->SendMSG("Session finished");
}
$this->_quit();
return TRUE;
}

function login($user=NULL, $pass=NULL) {
if(!is_null($user)) $this->_login=$user;
else $this->_login="anonymous";
if(!is_null($pass)) $this->_password=$pass;
else $this->_password="anon@anon.com";
if(!$this->_exec("USER ".$this->_login, "login")) return FALSE;
if(!$this->_checkCode()) return FALSE;
if($this->_code!=230) {
if(!$this->_exec((($this->_code==331)?"PASS ":"ACCT ").$this->_password, "login")) return FALSE;
if(!$this->_checkCode()) return FALSE;
}
$this->SendMSG("Authentication succeeded");
$this->_can_restore=$this->restore(100);
$this->SendMSG("This server can".($this->_can_restore?"":"'t")." resume broken uploads/downloads");
return TRUE;
}

function pwd() {
if(!$this->_exec("PWD", "pwd")) return FALSE;
if(!$this->_checkCode()) return FALSE;
return ereg_replace("^[0-9]{3} \"(.+)\" .+".CRLF, "\\1", $this->_message);
}

function cdup() {
if(!$this->_exec("CDUP", "cdup")) return FALSE;
if(!$this->_checkCode()) return FALSE;
return true;
}

function chdir($pathname) {
if(!$this->_exec("CWD ".$pathname, "chdir")) return FALSE;
if(!$this->_checkCode()) return FALSE;
return TRUE;
}

function rmdir($pathname) {
if(!$this->_exec("RMD ".$pathname, "rmdir")) return FALSE;
if(!$this->_checkCode()) return FALSE;
return TRUE;
}

function mkdir($pathname) {
if(!$this->_exec("MKD ".$pathname, "mkdir")) return FALSE;
if(!$this->_checkCode()) return FALSE;
return TRUE;
}

function rename($from, $to) {
if(!$this->_exec("RNFR ".$from, "rename")) return FALSE;
if(!$this->_checkCode()) return FALSE;
if($this->_code==350) {
if(!$this->_exec("RNTO ".$to, "rename")) return FALSE;
if(!$this->_checkCode()) return FALSE;
} else return FALSE;
return TRUE;
}

function filesize($pathname) {
if(!$this->_exec("SIZE ".$pathname, "filesize")) return FALSE;
if(!$this->_checkCode()) return FALSE;
return ereg_replace("^[0-9]{3} ([0-9]+)".CRLF, "\\1", $this->_message);
}

function mdtm($pathname) {
if(!$this->_exec("MDTM ".$pathname, "mdtm")) return FALSE;
if(!$this->_checkCode()) return FALSE;
$mdtm = ereg_replace("^[0-9]{3} ([0-9]+)".CRLF, "\\1", $this->_message);
$date = sscanf($mdtm, "%4d%2d%2d%2d%2d%2d");
$timestamp = mktime($date[3], $date[4], $date[5], $date[1], $date[2], $date[0]);
return $timestamp;
}

function systype() {
if(!$this->_exec("SYST", "systype")) return FALSE;
if(!$this->_checkCode()) return FALSE;
$DATA = explode(" ", $this->_message);
return $DATA[1];
}

function delete($pathname) {
if(!$this->_exec("DELE ".$pathname, "delete")) return FALSE;
if(!$this->_checkCode()) return FALSE;
return TRUE;
}

function site($command, $fnction="site") {
if(!$this->_exec("SITE ".$command, $fnction)) return FALSE;
if(!$this->_checkCode()) return FALSE;
return TRUE;
}

function chmod($pathname, $mode) {
if(!$this->site("CHMOD ".decoct($mode)." ".$pathname, "chmod")) return FALSE;
return TRUE;
}

function restore($from) {
if(!$this->_exec("REST ".$from, "restore")) return FALSE;
if(!$this->_checkCode()) return FALSE;
return TRUE;
}

function features() {
if(!$this->_exec("FEAT", "features")) return FALSE;
if(!$this->_checkCode()) return FALSE;
return preg_split("/[".CRLF."]+/", ereg_replace("[0-9]{3}[ -][^".CRLF."]*".CRLF, "", $this->_message), -1, PREG_SPLIT_NO_EMPTY);
}

function rawlist($arg="", $pathname="") {
return $this->_list(($arg?" ".$arg:"").($pathname?" ".$pathname:""), "LIST", "rawlist");
}

function nlist($arg="", $pathname="") {
return $this->_list(($arg?" ".$arg:"").($pathname?" ".$pathname:""), "NLST", "nlist");
}

function is_exists($pathname)
{
if (!($remote_list = $this->nlist("-a", dirname($pathname)))) {
$this->SendMSG("Error : Cannot get remote file list");
return -1;
}
reset($remote_list);
while (list(,$value) = each($remote_list)) {
if ($value == basename($pathname)) {
$this->SendMSG("Remote file ".$pathname." exists");
return TRUE;
}
}
$this->SendMSG("Remote file ".$pathname." does not exist");
return FALSE;
}

function get($remotefile, $localfile=NULL) {
if(is_null($localfile)) $localfile=$remotefile;
if (@file_exists($localfile)) $this->SendMSG("Warning : local file will be overwritten");
$fp = @fopen($localfile, "w");
if (!$fp) {
$this->PushError("get","can't open local file", "Cannot create \"".$localfile."\"");
return FALSE;
}
$pi=pathinfo($remotefile);
if($this->_type==FTP_ASCII or ($this->_type==FTP_AUTOASCII and in_array(strtoupper($pi["extension"]), $this->AutoAsciiExt))) $mode=FTP_ASCII;
else $mode=FTP_BINARY;
if(!$this->_data_prepare($mode)) {
fclose($fp);
return FALSE;
}
if($this->_can_restore) $this->restore(0);
if(!$this->_exec("RETR ".$remotefile, "get")) {
$this->_data_close();
fclose($fp);
return FALSE;
}
if(!$this->_checkCode()) {
$this->_data_close();
fclose($fp);
return FALSE;
}
$out=$this->_data_read($mode, $fp);
fclose($fp);
$this->_data_close();
if(!$this->_readmsg()) return FALSE;
if(!$this->_checkCode()) return FALSE;
return $out;
}

function get2($remotefile, $from) {
$mode=FTP_BINARY;
if(!$this->_data_prepare($mode)) {
return FALSE;
}
if($this->_can_restore) $this->restore($from);
if(!$this->_exec("RETR ".$remotefile, "get")) {
$this->_data_close();
return FALSE;
}
if(!$this->_checkCode()) {
$this->_data_close();
return FALSE;
}
$out=$this->_data_read2();
$this->_data_close();
if(!$this->_readmsg()) return FALSE;
if(!$this->_checkCode()) return FALSE;
return $out;
}

function put($localfile, $remotefile=NULL) {
if(is_null($remotefile)) $remotefile=$localfile;
if (!@file_exists($localfile)) {
$this->PushError("put","can't open local file", "No such file or directory \"".$localfile."\"");
return FALSE;
}
$fp = @fopen($localfile, "r");
if (!$fp) {
$this->PushError("put","can't open local file", "Cannot read file \"".$localfile."\"");
return FALSE;
}
$pi=pathinfo($localfile);
if($this->_type==FTP_ASCII or ($this->_type==FTP_AUTOASCII and in_array(strtoupper($pi["extension"]), $this->AutoAsciiExt))) $mode=FTP_ASCII;
else $mode=FTP_BINARY;
if(!$this->_data_prepare($mode)) {
fclose($fp);
return FALSE;
}
if($this->_can_restore) $this->restore(0);
if(!$this->_exec("STOR ".$remotefile, "put")) {
$this->_data_close();
fclose($fp);
return FALSE;
}
if(!$this->_checkCode()) {
$this->_data_close();
fclose($fp);
return FALSE;
}
$ret=$this->_data_write($mode, $fp);
fclose($fp);
$this->_data_close();
if(!$this->_readmsg()) return FALSE;
if(!$this->_checkCode()) return FALSE;
return $ret;
}

// <!-- --------------------------------------------------------------------------------------- -->
// <!--       Private functions                                                                 -->
// <!-- --------------------------------------------------------------------------------------- -->
function _checkCode() {
return ($this->_code<400 and $this->_code>0);
}

function _list($arg="", $cmd="LIST", $fnction="_list") {
if(!$this->_data_prepare()) return FALSE;
if(!$this->_exec($cmd.$arg, $fnction)) {
$this->_data_close();
return FALSE;
}
if(!$this->_checkCode()) {
$this->_data_close();
return FALSE;
}
$out=$this->_data_read();
$this->_data_close();
if(!$this->_readmsg()) return FALSE;
if(!$this->_checkCode()) return FALSE;
if($out === FALSE ) return FALSE;
$out=preg_split("/[".CRLF."]+/", $out, -1, PREG_SPLIT_NO_EMPTY);
$this->SendMSG(implode($this->NewLineCode[$this->OS_local], $out));
return $out;
}

// <!-- --------------------------------------------------------------------------------------- -->
// <!-- Partie : gestion des erreurs                                                            -->
// <!-- --------------------------------------------------------------------------------------- -->
// Genere une erreur pour traitement externe a la classe
function PushError($fctname,$msg,$desc=false){
$error=array();
$error['time']=time();
$error['fctname']=$fctname;
$error['msg']=$msg;
$error['desc']=$desc;
if($desc) $tmp=' ('.$desc.')'; else $tmp='';
$this->SendMSG($fctname.': '.$msg.$tmp);
return(array_push($this->_error_array,$error));
}

// Recupere une erreur externe
function PopError(){
if(count($this->_error_array)) return(array_pop($this->_error_array));
else return(false);
}
}

$mod_sockets=TRUE;
if (!extension_loaded('sockets')) {
    $prefix = (PHP_SHLIB_SUFFIX == 'dll') ? 'php_' : '';
    if(!@dl($prefix . 'sockets.' . PHP_SHLIB_SUFFIX)) $mod_sockets=FALSE;
}

$mod_sockets=TRUE;
if (!extension_loaded('sockets')) {
    $prefix = (PHP_SHLIB_SUFFIX == 'dll') ? 'php_' : '';
    if(!@dl($prefix . 'sockets.' . PHP_SHLIB_SUFFIX)) $mod_sockets=FALSE;
}
if($mod_sockets)
{
class ftp extends ftp_base {
function ftp($verb=FALSE, $le=FALSE) {
$this->LocalEcho = $le;
$this->Verbose = $verb;
$this->ftp_base(TRUE);
}

// <!-- --------------------------------------------------------------------------------------- -->
// <!--       Private functions                                                                 -->
// <!-- --------------------------------------------------------------------------------------- -->

function _settimeout($sock) {
/*if(!@stream_set_timeout($sock, $this->_timeout)) {
$this->PushError('_settimeout','socket set send timeout');
$this->_quit();
return FALSE;
}
*//*if(!@socket_set_option($sock, 1, SO_RCVTIMEO, array("sec"=>$this->_timeout, "usec"=>0))) {
$this->PushError('_connect','socket set receive timeout',socket_strerror(socket_last_error($sock)));
@socket_close($sock);
return FALSE;
}
if(!@socket_set_option($sock, 1, SO_SNDTIMEO, array("sec"=>$this->_timeout, "usec"=>0))) {
$this->PushError('_connect','socket set send timeout',socket_strerror(socket_last_error($sock)));
@socket_close($sock);
return FALSE;
}
*/return true;
}

function _connect($host, $port) {
$this->SendMSG("Creating socket");
$sock = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($sock < 0) {
$this->PushError('_connect','socket create failed',socket_strerror(socket_last_error($sock)));
return FALSE;
}
if(!$this->_settimeout($sock)) return FALSE;
$this->SendMSG("Connecting to \"".$host.":".$port."\"");
if (!($res = @socket_connect($sock, $host, $port))) {
$this->PushError('_connect','socket connect failed',socket_strerror(socket_last_error($sock)));
@socket_close($sock);
return FALSE;
}
$this->_connected=true;
return $sock;
}

function _readmsg($fnction="_readmsg"){
if(!$this->_connected) {
$this->PushError($fnction,'Connect first');
return FALSE;
}
$result=true;
$this->_message="";
$this->_code=0;
$go=true;
do {
$tmp=@socket_read($this->_ftp_control_sock, 4096, PHP_BINARY_READ);
if($tmp===false) {
$go=$result=false;
$this->PushError($fnction,'Read failed', socket_strerror(socket_last_error($this->_ftp_control_sock)));
} elseif($tmp=="") $go=false;
else {
$this->_message.=$tmp;
//for($i=0; $i<strlen($this->_message); $i++)
//if(ord($this->_message[$i])<32) echo "#".ord($this->_message[$i]); else echo $this->_message[$i];
//echo CRLF;
if(preg_match("/^([0-9]{3})(-(.*".CRLF.")+\\1)? [^".CRLF."]+".CRLF."$/", $this->_message, $regs)) $go=false;
}
} while($go);
if($this->LocalEcho) echo "GET < ".rtrim($this->_message, CRLF).CRLF;
$this->_code=(int)$regs[1];
return $result;
}

function _exec($cmd, $fnction="_exec") {
if(!$this->_ready) {
$this->PushError($fnction,'Connect first');
return FALSE;
}
if($this->LocalEcho) echo "PUT > ",$cmd,CRLF;
$status=@socket_write($this->_ftp_control_sock, $cmd.CRLF);
if($status===false) {
$this->PushError($fnction,'socket write failed', socket_strerror(socket_last_error($this->stream)));
return FALSE;
}
$this->_lastaction=time();
if(!$this->_readmsg($fnction)) return FALSE;
return TRUE;
}

function _data_prepare($mode=FTP_ASCII) {
if($mode==FTP_BINARY) {
if(!$this->_exec("TYPE I", "_data_prepare")) return FALSE;
} else {
if(!$this->_exec("TYPE A", "_data_prepare")) return FALSE;
}
$this->SendMSG("Creating data socket");
$this->_ftp_data_sock = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($this->_ftp_data_sock < 0) {
$this->PushError('_data_prepare','socket create failed',socket_strerror(socket_last_error($this->_ftp_data_sock)));
return FALSE;
}
if(!$this->_settimeout($this->_ftp_data_sock)) {
$this->_data_close();
return FALSE;
}
if($this->_passive) {
if(!$this->_exec("PASV", "pasv")) {
$this->_data_close();
return FALSE;
}
if(!$this->_checkCode()) {
$this->_data_close();
return FALSE;
}
$ip_port = explode(",", ereg_replace("^.+ \\(?([0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]+,[0-9]+)\\)?.*".CRLF."$", "\\1", $this->_message));
$this->_datahost=$ip_port[0].".".$ip_port[1].".".$ip_port[2].".".$ip_port[3];
            $this->_dataport=(((int)$ip_port[4])<<8) + ((int)$ip_port[5]);
$this->SendMSG("Connecting to ".$this->_datahost.":".$this->_dataport);
if(!@socket_connect($this->_ftp_data_sock, $this->_datahost, $this->_dataport)) {
$this->PushError("_data_prepare","socket_connect", socket_strerror(socket_last_error($this->_ftp_data_sock)));
$this->_data_close();
return FALSE;
}
else $this->_ftp_temp_sock=$this->_ftp_data_sock;
} else {
if(!@socket_getsockname($this->_ftp_control_sock, $addr, $port)) {
$this->PushError("_data_prepare","can't get control socket information", socket_strerror(socket_last_error($this->_ftp_control_sock)));
$this->_data_close();
return FALSE;
}
if(!@socket_bind($this->_ftp_data_sock,$addr)){
$this->PushError("_data_prepare","can't bind data socket", socket_strerror(socket_last_error($this->_ftp_data_sock)));
$this->_data_close();
return FALSE;
}
if(!@socket_listen($this->_ftp_data_sock)) {
$this->PushError("_data_prepare","can't listen data socket", socket_strerror(socket_last_error($this->_ftp_data_sock)));
$this->_data_close();
return FALSE;
}
if(!@socket_getsockname($this->_ftp_data_sock, $this->_datahost, $this->_dataport)) {
$this->PushError("_data_prepare","can't get data socket information", socket_strerror(socket_last_error($this->_ftp_data_sock)));
$this->_data_close();
return FALSE;
}
if(!$this->_exec('PORT '.str_replace('.',',',$this->_datahost.'.'.($this->_dataport>>8).'.'.($this->_dataport&0x00FF)), "_port")) {
$this->_data_close();
return FALSE;
}
if(!$this->_checkCode()) {
$this->_data_close();
return FALSE;
}
}
return TRUE;
}

function _data_read($mode=FTP_ASCII, $fp=NULL) {
$NewLine=$this->NewLineCode[$this->OS_local];
if(is_resource($fp)) $out=0;
else $out="";
if(!$this->_passive) {
$this->SendMSG("Connecting to ".$this->_datahost.":".$this->_dataport);
$this->_ftp_temp_sock=socket_accept($this->_ftp_data_sock);
if($this->_ftp_temp_sock===FALSE) {
$this->PushError("_data_read","socket_accept", socket_strerror(socket_last_error($this->_ftp_temp_sock)));
$this->_data_close();
return FALSE;
}
}
if($mode!=FTP_BINARY) {
while(($tmp=@socket_read($this->_ftp_temp_sock, 8192, PHP_NORMAL_READ))!==false) {
$line.=$tmp;
if(!preg_match("/".CRLF."$/", $line)) continue;
$line=rtrim($line,CRLF).$NewLine;
if(is_resource($fp)) $out+=fwrite($fp, $line, strlen($line));
else $out.=$line;
$line="";
}
} else {
while($block=@socket_read($this->_ftp_temp_sock, 8192, PHP_BINARY_READ)) {
if(is_resource($fp)) $out+=fwrite($fp, $block, strlen($block));
else $out.=$line;
}
}
return $out;
}

function _data_read2() {
$NewLine=$this->NewLineCode[$this->OS_local];
$out=0;
if(!$this->_passive) {
$this->SendMSG("Connecting to ".$this->_datahost.":".$this->_dataport);
$this->_ftp_temp_sock=socket_accept($this->_ftp_data_sock);
if($this->_ftp_temp_sock===FALSE) {
$this->PushError("_data_read","socket_accept", socket_strerror(socket_last_error($this->_ftp_temp_sock)));
$this->_data_close();
return FALSE;
}
}
while($block=@socket_read($this->_ftp_temp_sock, 8192, PHP_BINARY_READ)) {
$out+=strlen($block);
echo $block;

}

return $out;
}

function _data_write($mode=FTP_ASCII, $fp=NULL) {
$NewLine=$this->NewLineCode[$this->OS_local];
if(is_resource($fp)) $out=0;
else $out="";
if(!$this->_passive) {
$this->SendMSG("Connecting to ".$this->_datahost.":".$this->_dataport);
$this->_ftp_temp_sock=socket_accept($this->_ftp_data_sock);
if($this->_ftp_temp_sock===FALSE) {
$this->PushError("_data_write","socket_accept", socket_strerror(socket_last_error($this->_ftp_temp_sock)));
$this->_data_close();
return FALSE;
}
}
if(is_resource($fp)) {
while(!feof($fp)) {
$line=fgets($fp, 4096);
if($mode!=FTP_BINARY) $line=rtrim($line, CRLF).CRLF;
do {
if(($res=@socket_write($this->_ftp_temp_sock, $line))===FALSE) {
$this->PushError("_data_write","socket_write", socket_strerror(socket_last_error($this->_ftp_temp_sock)));
return FALSE;
}
$line=substr($line, $res);
}while($line!="");
}
} else {
if($mode!=FTP_BINARY) $fp=rtrim($fp, $NewLine).CRLF;
do {
if(($res=@socket_write($this->_ftp_temp_sock, $fp))===FALSE) {
$this->PushError("_data_write","socket_write", socket_strerror(socket_last_error($this->_ftp_temp_sock)));
return FALSE;
}
$fp=substr($fp, $res);
}while($fp!="");
}
return TRUE;
}

function _data_close() {
@socket_close($this->_ftp_temp_sock);
@socket_close($this->_ftp_data_sock);
$this->SendMSG("Disconnected data from remote host");
return TRUE;
}

function _quit() {
if($this->_connected) {
@socket_close($this->_ftp_control_sock);
$this->_connected=false;
$this->SendMSG("Socket closed");
}
}
}
}
else
{
class ftp  extends ftp_base {
function ftp($verb=FALSE, $le=FALSE) {
$this->LocalEcho = $le;
$this->Verbose = $verb;
$this->ftp_base();
}

// <!-- --------------------------------------------------------------------------------------- -->
// <!--       Private functions                                                                 -->
// <!-- --------------------------------------------------------------------------------------- -->

function _settimeout($sock) {echo"===";
/*if(!@stream_set_timeout($sock, $this->_timeout)) {
$this->PushError('_settimeout','socket set send timeout');
$this->_quit();
return FALSE;
}
*/return TRUE;
}

function _connect($host, $port) {
$this->SendMSG("Creating socket");
$sock = @fsockopen($host, $port, $errno, $errstr, $this->_timeout);
if (!$sock) {
$this->PushError('_connect','socket connect failed', $errstr." (".$errno.")");
return FALSE;
}
$this->_connected=true;
return $sock;
}

function _readmsg($fnction="_readmsg"){
if(!$this->_connected) {
$this->PushError($fnction, 'Connect first');
return FALSE;
}
$result=true;
$this->_message="";
$this->_code=0;
$go=true;
do {
$tmp=@fgets($this->_ftp_control_sock, 512);
if($tmp===false) {
$go=$result=false;
$this->PushError($fnction,'Read failed');
} else {
$this->_message.=$tmp;
//for($i=0; $i<strlen($this->_message); $i++)
//if(ord($this->_message[$i])<32) echo "#".ord($this->_message[$i]); else echo $this->_message[$i];
//echo CRLF;
if(preg_match("/^([0-9]{3})(-(.*".CRLF.")+\\1)? [^".CRLF."]+".CRLF."$/", $this->_message, $regs)) $go=false;
}
} while($go);
if($this->LocalEcho) echo "GET < ".rtrim($this->_message, CRLF).CRLF;
$this->_code=(int)$regs[1];
return $result;
}

function _exec($cmd, $fnction="_exec") {
if(!$this->_ready) {
$this->PushError($fnction,'Connect first');
return FALSE;
}
if($this->LocalEcho) echo "PUT > ",$cmd,CRLF;
$status=@fputs($this->_ftp_control_sock, $cmd.CRLF);
if($status===false) {
$this->PushError($fnction,'socket write failed');
return FALSE;
}
$this->_lastaction=time();
if(!$this->_readmsg($fnction)) return FALSE;
return TRUE;
}

function _data_prepare($mode=FTP_ASCII) {
if($mode==FTP_BINARY) {
if(!$this->_exec("TYPE I", "_data_prepare")) return FALSE;
} else {
if(!$this->_exec("TYPE A", "_data_prepare")) return FALSE;
}
if($this->_passive) {
if(!$this->_exec("PASV", "pasv")) {
$this->_data_close();
return FALSE;
}
if(!$this->_checkCode()) {
$this->_data_close();
return FALSE;
}
$ip_port = explode(",", ereg_replace("^.+ \\(?([0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]+,[0-9]+)\\)?.*".CRLF."$", "\\1", $this->_message));
$this->_datahost=$ip_port[0].".".$ip_port[1].".".$ip_port[2].".".$ip_port[3];
            $this->_dataport=(((int)$ip_port[4])<<8) + ((int)$ip_port[5]);
$this->SendMSG("Connecting to ".$this->_datahost.":".$this->_dataport);
$this->_ftp_data_sock=@fsockopen($this->_datahost, $this->_dataport, $errno, $errstr, $this->_timeout);
if(!$this->_ftp_data_sock) {
$this->PushError("_data_prepare","fsockopen fails", $errstr." (".$errno.")");
$this->_data_close();
return FALSE;
}
else $this->_ftp_data_sock;
} else {
$this->SendMSG("Only passive connections available!");
return FALSE;
}
return TRUE;
}

function _data_read($mode=FTP_ASCII, $fp=NULL) {
$NewLine=$this->NewLineCode[$this->OS_local];
if(is_resource($fp)) $out=0;
else $out="";
if(!$this->_passive) {
$this->SendMSG("Only passive connections available!");
return FALSE;
}
if($mode!=FTP_BINARY) {
while (!feof($this->_ftp_data_sock)) {
$tmp=fread($this->_ftp_data_sock, 4096);
$line.=$tmp;
if(!preg_match("/".CRLF."$/", $line)) continue;
$line=rtrim($line,CRLF).$NewLine;
if(is_resource($fp)) $out+=fwrite($fp, $line, strlen($line));
else $out.=$line;
$line="";
}
} else {
while (!feof($this->_ftp_data_sock)) {
$block=fread($this->_ftp_data_sock, 4096);
if(is_resource($fp)) $out+=fwrite($fp, $block, strlen($block));
else $out.=$line;
}
}
return $out;
}

function _data_read2() {
$NewLine=$this->NewLineCode[$this->OS_local];
$out=0;
if(!$this->_passive) {
$this->SendMSG("Only passive connections available!");
return FALSE;
}
while (!feof($this->_ftp_data_sock)) {
$block=fread($this->_ftp_data_sock, 4096);
$out+=strlen($block);
echo $block;
}
return $out;
}

function _data_write($mode=FTP_ASCII, $fp=NULL) {
$NewLine=$this->NewLineCode[$this->OS_local];
if(is_resource($fp)) $out=0;
else $out="";
if(!$this->_passive) {
$this->SendMSG("Only passive connections available!");
return FALSE;
}
if(is_resource($fp)) {
while(!feof($fp)) {
$line=fgets($fp, 4096);
if($mode!=FTP_BINARY) $line=rtrim($line, CRLF).CRLF;
do {
if(($res=@fwrite($this->_ftp_data_sock, $line))===FALSE) {
$this->PushError("_data_write","Can't write to socket");
return FALSE;
}
$line=substr($line, $res);
}while($line!="");
}
} else {
if($mode!=FTP_BINARY) $fp=rtrim($fp, $NewLine).CRLF;
do {
if(($res=@fwrite($this->_ftp_data_sock, $fp))===FALSE) {
$this->PushError("_data_write","Can't write to socket");
return FALSE;
}
$fp=substr($fp, $res);
}while($fp!="");
}
return TRUE;
}

function _data_close() {
@fclose($this->_ftp_data_sock);
$this->SendMSG("Disconnected data from remote host");
return TRUE;
}

function _quit($force=FALSE) {
if($this->_connected or $force) {
@fclose($this->_ftp_control_sock);
$this->_connected=false;
$this->SendMSG("Socket closed");
}
}
}
}



$ftp = new ftp(FALSE, FALSE);
//$ftp->Verbose = true;
//$ftp->LocalEcho = true;
if(!$ftp->SetServer($_GET[host], $_GET[port] ? (int)$_GET[port] : 21)) {
//if(!$ftp->SetServer("mp3.int.ru", 2121)) {
$ftp->quit();
die("Setting server failed");
}
if (!$ftp->connect()) {
die("Cannot connect");
}
if (!$ftp->login($_GET['user'], $_GET['pass'])) {
$ftp->quit();
die("Login failed");
}
$ftp->Passive(FALSE);
$tmp = explode("/", $_GET['path']);
$ftp_file = array_pop($tmp);
$ftp_dir = implode("/", $tmp);
$ftp->chdir($ftp_dir);
if(!$ftp->SetType(FTP_BINARY)) echo "SetType FAILS!n";
$size=$ftp->filesize($ftp_file);
 $object = new httpdownload;
 $object->set_filename($ftp_file);
      $object->use_resume=true;
$object->download_ex($size);
$ftp->get2($ftp_file,$object->seek_start);
$ftp->quit();
    die;
  }

if(@$_COOKIE["clearsettings"])
  {
    setcookie("domail", "", time() - 3600);
    setcookie("email", "", time() - 3600);
    setcookie("saveto", "", time() - 3600);
    setcookie("path", "", time() - 3600);
    setcookie("useproxy", "", time() - 3600);
    setcookie("proxy", "", time() - 3600);
    setcookie("split", "", time() - 3600);
    setcookie("partSize", "", time() - 3600);
    setcookie("savesettings", "", time() - 3600);
    setcookie("clearsettings", "", time() - 3600);
  }

if(!@$_GET['FileName'] || !$_GET['host'] || !$_GET['path'])
  {
    $LINK = @$_GET['link'];
    if(!$LINK)
      {
        ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//RU">
<html><head>

<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
<style type="text/css">
 <!--
  body      {font-family: Tahoma; font-size: 11px;}
  tr        {font-family: Tahoma; font-size: 11px; line-height: 14.5px;}
  input     {font-family: Tahoma; font-size: 11px;}
  select    {font-family: Tahoma; font-size: 11px;}
  textarea  {font-family: Tahoma; font-size: 11.5px;}
  a:link, a:active, a:visited {color: #0000FF; text-decoration: none}
&#12288;a:hover {color: red; text-decoration: none}

.tab-on {
padding: 2px;
border-top-width: 1px;
border-right-width: 1px;
border-bottom-width: 1px;
border-left-width: 1px;
border-top-style: solid;
border-right-style: none;
border-bottom-style: solid;
border-left-style: solid;
border-top-color: #cccccc;
border-right-color: #cccccc;
border-bottom-color: #cccccc;
border-left-color: #cccccc;
color: #000000;
background-color: #ffffff;
width: 100px;
}

.tab-onr {
padding: 2px;
border-top-width: 1px;
border-right-width: 1px;
border-bottom-width: 1px;
border-left-width: 1px;
border-top-style: solid;
border-right-style: solid;
border-bottom-style: solid;
border-left-style: solid;
border-top-color: #cccccc;
border-right-color: #cccccc;
border-bottom-color: #cccccc;
border-left-color: #cccccc;
color: #000000;
background-color: #ffffff;
width: 100px;
}

.tab-off
{
padding: 2px;
background-color: #f6f6f6;
color: #666666;
border-top: 1px solid #cccccc;
border-right: 1px none #cccccc;
border-bottom: 1px solid #cccccc;
border-left: 1px solid #cccccc;
width: 100px;
}

.tab-offr
{
padding: 2px;
background-color: #f6f6f6;
color: #666666;
border-top: 1px solid #cccccc;
border-right: 1px solid #cccccc;
border-bottom: 1px solid #cccccc;
border-left: 1px solid #cccccc;
width: 100px;
}

.tab-none
{
border-top-width: 1px;
border-right-width: 1px;
border-bottom-width: 1px;
border-left-width: 1px;
border-top-style: none;
border-right-style: none;
border-bottom-style: solid;
border-left-style: solid;
border-top-color: #cccccc;
border-right-color: #cccccc;
border-bottom-color: #cccccc;
border-left-color: #cccccc;
}

.show-table {display: block;}

.hide-table {display: none;}
</style>

<script>
function switchCell(m) {
  var style
  document.getElementById("navcell1").className = "tab-off";
  document.getElementById("navcell2").className = "tab-off";
  document.getElementById("navcell3").className = "tab-offr";
  document.getElementById("tb1").className = "hide-table";
  document.getElementById("tb2").className = "hide-table";
  document.getElementById("tb3").className = "hide-table";
  if(m == 3) {style = "tab-onr"} else {style = "tab-on"}
  document.getElementById("navcell" + m).className = style;
  document.getElementById("tb" + m).className = "tab-content show-table";
}
</script>
<title>Rapidget Downloader - Converted & Skin by myand.com ' Samoa</title>
</head>

<body bgcolor="#808080">
<table align="center"  border="1" bordercolor="#000000" bgcolor="#ffffff" width="630">
  <tbody>
  <tr>
    <td>
      <table id="tb_content" align="center">
        <tbody>
        <tr>
          <td align="center">
    <tr>
		<td><img src="images/logo.gif"></td>
	</tr>
	<tr>
		<td><hr width="600" size="1" color="#E1E1E1"></td>
	</tr>
	<tr height="8">
		<td></td>
	</tr>
        <table border="0" cellpadding="0" cellspacing="0" align="center">
              <tbody>
              <tr>
                <td onClick="switchCell(1)" class="tab-on" id="navcell1" align="center">
                  <a href="javascript:switchCell(1)">&#19979;&#36733;&#25991;&#20214;</a>
                </td>
                <td onClick="switchCell(2)" class="tab-off" id="navcell2"  align="center">
                   <a href="javascript:switchCell(2)">&#25805;&#20316;&#36873;&#39033;</a>
                </td>
                <td onClick="switchCell(3)" class="tab-off" id="navcell3"  align="center">
                   <a href="javascript:switchCell(3)">&#25991;&#20214;&#31649;&#29702;</a>
                </td>
              </tr>
              </tbody>
        </table>

            <table class="tab-content" id="tb1" name="tb" cellspacing="5" width="100%">
              <tbody>
              <tr>
                <td align="center">
                  <form method=get>
                  &#35201;&#19979;&#36733;&#30340;&#25991;&#20214;&#38142;&#25509;: <input name="link" size=75>
                  <input type=submit value="&#19979;&#36733;&#25991;&#20214;">
                </td>
              </tr>
              <tr>
                <td align="center">
                  <input type="checkbox" name="add_comment" onClick="javascript:var displ=this.checked?'':'none';document.getElementById('commenttr').style.display=displ;">&nbsp; &#28155;&#21152;&#27880;&#37322;[<font color="#FF0000">&#20363;&#22914;:&#25991;&#20214;&#23494;&#30721;</font>]
                </td>
              </tr>
              <tr id="commenttr" style="DISPLAY: none;">
                <td align="center">
                  <textarea name="comment" rows=4 cols=60></textarea>
                </td>
              </tr>
              </tbody>
            </table><br>
            <div align="center">
            Rapidget Script By Vyrus in Russian<br>
			Converted by <font color="#FF9900">Samoa</font> to Chinese, Skin by <font color="#FF9900">Samoa</font>, website: <a href="http://www.myand.com" title="&#26032;&#35270;&#21548;&#36164;&#28304;&#20849;&#20139;&#35770;&#22363;..." target="_blank">myand.com</a>
            </div><br>
            <table class="hide-table" id="tb2" name="tb" cellspacing="5" width="100%">
              <tbody>
              <tr>
                <td align="center">
                  <table align="center">
                    <tr>
                      <td>
                        <input type="checkbox" name=domail id=domail onClick="javascript:document.getElementById('emailtd').style.display=document.getElementById('splittd').style.display=this.checked?'':'none';document.getElementById('methodtd').style.display=(document.getElementById('splitchkbox').checked&this.checked)?'':'none';"<?PHP  echo $_COOKIE["domail"] ? " checked" : ""; ?>>&nbsp;&#21457;&#36865;&#21040;&#30005;&#23376;&#37038;&#20214; (Email)
                      </td>
                      <td>&nbsp;
                        
                      </td>
                      <td id=emailtd<?PHP  echo $_COOKIE["domail"] ? "" : " style=\"display: none;\""; ?>>
                        Email:&nbsp;<input name=email<?PHP  echo $_COOKIE["email"] ? " value=\"".$_COOKIE["email"]."\"" : ""; ?>>
                      </td>
                    </tr>
                    <tr>
                      <td>
                      </td>
                    </tr>
                    <tr id=splittd<?PHP  echo $_COOKIE["split"] ? "" : " style=\"display: none;\""; ?>>
                      <td>
                        <input id=splitchkbox type="checkbox" name=split onClick="javascript:var displ=this.checked?'':'none';document.getElementById('methodtd').style.display=displ;"<?PHP  echo $_COOKIE["split"] ? " checked" : ""; ?>>&nbsp;&#20998;&#21106;&#25991;&#20214;&#20026;&#20960;&#37096;&#20998;
                      </td>
                      <td>&nbsp;
                        
                      </td>
                      <td id=methodtd<?PHP  echo $_COOKIE["split"] ? "" : " style=\"display: none;\""; ?>>
                        <table>
                          <tr>
                            <td>
                              &#36873;&#25321;&#26041;&#24335;&nbsp;<select name=method><option value=tc<?PHP  echo $_COOKIE["method"] == "tc" ? " selected" : ""; ?>>Total Commander</option><option value=rfc<?PHP  echo $_COOKIE["method"] == "rfc" ? " selected" : ""; ?>>RFC 2046</option></select>
                            </td>
                          </tr>
                          <tr>
                            <td>
                              &#20998;&#21367;&#22823;&#23567;:&nbsp;<input name=partSize size=1 value=<?PHP  echo $_COOKIE["partSize"] ? $_COOKIE["partSize"] : 10; ?>>&nbsp;MB
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <input type="checkbox" id=useproxy name=useproxy onClick="javascript:var displ=this.checked?'':'none';document.getElementById('proxy').style.display=displ;"<?PHP  echo $_COOKIE["useproxy"] ? " checked" : ""; ?>>&nbsp;&#20351;&#29992;&#20195;&#29702;&#26381;&#21153;&#22120;
                      </td>
                      <td>&nbsp;
                        
                      </td>
                      <td id=proxy<?PHP  echo $_COOKIE["useproxy"] ? "" : " style=\"display: none;\""; ?>>
                        &#20195;&#29702;&#26381;&#21153;&#22120;:&nbsp;<input name=proxy size=19<?PHP  echo $_COOKIE["proxy"] ? " value=\"".$_COOKIE["proxy"]."\"" : ""; ?>>
                      </td>
                    </tr>
                    <tr>
                      <td>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <input type="checkbox" name=saveto id=saveto onClick="javascript:var displ=this.checked?'':'none';document.getElementById('path').style.display=displ;"<?PHP  echo $_COOKIE["saveto"] ? " checked" : ""; ?>>&nbsp;&#20445;&#23384;&#21040;...
                      </td>
                      <td>&nbsp;
                        
                      </td>
                      <td id=path<?PHP  echo $_COOKIE["saveto"] ? "" : " style=\"display: none;\""; ?>>
                        &#36335;&#24452;:&nbsp;<input name=path size=30 value="<?PHP  echo ($_COOKIE["path"] ? $_COOKIE["path"] : (strstr(realpath("./"), ":") ? addslashes(dirname(__FILE__)) : dirname(__FILE__))); ?>">
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <input type="checkbox" name=savesettings id=savesettings<?PHP  echo $_COOKIE["savesettings"] ? " checked" : ""; ?> onClick="javascript:var displ=this.checked?'':'none';document.getElementById('clearsettings').style.display=displ;">&nbsp;&#20445;&#23384;&#35774;&#32622;
                      </td>
                       <td>&nbsp;
                        
                      </td>
                      <td id=clearsettings<?PHP  echo $_COOKIE["savesettings"] ? "" : " style=\"display: none;\""; ?>>
                        <script>
                          function clearSettings() {
                            clear("domail"); clear("email"); clear("split"); clear("method");
                            clear("partSize"); clear("useproxy"); clear("proxy"); clear("saveto");
                            clear("path"); clear("savesettings");

                            document.getElementById('domail').checked =
                            document.getElementById('splitchkbox').checked =
                            document.getElementById('useproxy').checked =
                            document.getElementById('saveto').checked =
                            document.getElementById('savesettings').checked = "";

                            document.getElementById('emailtd').style.display =
                            document.getElementById('splittd').style.display =
                            document.getElementById('methodtd').style.display =
                            document.getElementById('proxy').style.display =
                            document.getElementById('path').style.display =
                            document.getElementById('clearsettings').style.display = "none";

                            document.cookie = "clearsettings = 1;";
                          }

                          function clear(name) {
                            document.cookie = name + " = " + "; expires=Thu, 01-Jan-70 00:00:01 GMT";
                          }
                        </script>
                        <a href="javascript:clearSettings();">&#28165;&#38500;&#35774;&#32622;</a>
                      </td>
                    </tr>
                  </table>
                  </form>

                </td>
              </tr>
              </tbody>
            </table>

            <table class="hide-table" id="tb3" name="tb" cellspacing="5" width="100%">
              <tbody><?PHP  echo @$_GET['act'] ? "<script>switchCell(3);</script>" : "<script>switchCell(1);</script>"; ?>
              <td align="center">
                  <?PHP 
                  if(@file_exists("files.lst"))
                    {
                        $list = file("files.lst");
                        foreach($list as $key => $record)
                          {
                              foreach(unserialize($record) as $field => $value)
                                {
                                    $listReformat[$key][$field] = $value;
                                    if($field == "date")
                                      {
                                          $date = $value;
                                      }

                                }
                              $list[$date] = $listReformat[$key];
                              unset($list[$key], $listReformat[$key]);
                          }
                    }
                  switch($_GET["act"])
                    {
                      case "delete":
                        if(count($_GET["files"]) < 1)
                          {
                            echo "&#35831;&#33267;&#23569;&#36873;&#25321;&#19968;&#20010;&#25991;&#20214;.<br><br>";
                          }
                        else
                          {
                            ?>
                              <form method="post">
                              <input type=hidden name=act value="delete_go">
                              &#25991;&#20214;<?PHP  echo count($_GET["files"]) > 1 ? "" : ""; ?>:
                              <?PHP 
                              for($i = 0; $i < count($_GET["files"]); $i++)
                                {
                                  $file = $list[$_GET["files"][$i]];
                                  ?>
                                  <input type=hidden name="files[]" value="<?PHP  echo $_GET["files"][$i]; ?>">
                                  <b><?PHP  echo basename($file["name"]); ?></b><?PHP  echo $i == count($_GET["files"]) - 1 ? "." : ",&nbsp"; ?>
                                  <?PHP 
                                }
                              ?><br>&#21024;&#38500;<?PHP  echo count($_GET["files"]) > 1 ? "&#36825;&#20123;&#25991;&#20214;" : "&#36825;&#20010;&#25991;&#20214;"; ?>&#65311;<br>
                              <table>
                                <tr>
                                  <td>
                                    <input type=submit name="yes" style="width:33px; height:23px" value="&#26159;">
                                  </td>
                                  <td>
                                    &nbsp;&nbsp;&nbsp;
                                  </td>
                                  <td>
                                    <input type=submit name="no" style="width:33px; height:23px" value="&#21542;">
                                  </td>
                                </tr>
                              </table>
                              </form>
                            <?PHP 
                          }
                      break;

                      case "delete_go":
                        if($_GET["yes"])
                          {
                            for($i = 0; $i < count($_GET["files"]); $i++)
                              {
                                $file = $list[$_GET["files"][$i]];
                                if(file_exists($file["name"]))
                                  {
                                    if(@unlink($file["name"]))
                                      {
                                        echo "&#25991;&#20214; <b>".$file["name"]."</b> &#24050;&#34987;&#21024;&#38500;.<br><br>";
                                        unset($list[$_GET["files"][$i]]);
                                      }
                                    else
                                      {
                                        echo "&#19981;&#33021;&#31227;&#38500;&#25991;&#20214; <b>".$file["name"]."</b>!<br><br>";
                                      }
                                  }
                                else
                                  {
                                    echo "&#25991;&#20214; <b>".$file["name"]."</b> &#26410;&#21457;&#29616;<br><br>";
                                  }
                              }
                            if(!updateListInFile($list))
                              {
                                  echo "&#23427;&#19981;&#21487;&#33021;&#26356;&#26032;&#21015;&#34920;!<br><br>";
                              }
                          }
                        else
                          {
                            ?>
                              <script>
                                location.href="<?PHP  echo substr($PHP_SELF, 0, strlen($PHP_SELF) - strlen(strstr($PHP_SELF, "?")))."?act=files"; ?>";
                              </script>
                            <?PHP 
                          }
                      break;

                      case "mail":
                        if(count($_GET["files"]) < 1)
                          {
                            echo "&#35831;&#36873;&#25321;&#33267;&#23569;&#19968;&#20010;&#25991;&#20214;.<br><br>";
                          }
                        else
                          {
                            ?>
                              <form method="post">
                              <input type=hidden name=act value="mail_go">
                              &#25991;&#20214;<?PHP  echo count($_GET["files"]) > 1 ? "" : ""; ?>:
                              <?PHP 
                              for($i = 0; $i < count($_GET["files"]); $i++)
                                {
                                  $file = $list[($_GET["files"][$i])];
                                  ?>
                                  <input type=hidden name="files[]" value="<?PHP  echo $_GET["files"][$i]; ?>">
                                  <b><?PHP  echo basename($file["name"]); ?></b><?PHP  echo $i == count($_GET["files"]) - 1 ? "." : ",&nbsp"; ?>
                                  <?PHP 
                                }
                              ?><br><br>
                              <table align="center">
                                <tr>
                                  <td>
                                    Email:&nbsp;<input name=email<?PHP  echo $_COOKIE["email"] ? " value=\"".$_COOKIE["email"]."\"" : ""; ?>>
                                  </td>
                                  <td>
                                    <input type=submit value="&#21457;&#36865;&#37038;&#20214;">
                                  </td>
                                </tr>
                                <tr>
                                  <td>
                                  </td>
                                </tr>
                                <tr>
                                  <table>
                                    <tr>
                                      <td>
                                        <input id=splitchkbox type="checkbox" name=split onClick="javascript:var displ=this.checked?'':'none';document.getElementById('methodtd2').style.display=displ;"<?PHP  echo $_COOKIE["split"] ? " checked" : ""; ?>>&nbsp;&#20998;&#21106;&#20026;&#20960;&#37096;&#20998;
                                      </td>
                                      <td>&nbsp;
                                        
                                      </td>
                                      <td id=methodtd2<?PHP  echo $_COOKIE["split"] ? "" : " style=\"display: none;\""; ?>>
                                        <table>
                                          <tr>
                                            <td>
                                              &#36873;&#25321;&#26041;&#24335;:&nbsp;<select name=method><option value=tc<?PHP  echo $_COOKIE["method"] == "tc" ? " selected" : ""; ?>>Total Commander</option><option value=rfc<?PHP  echo $_COOKIE["method"] == "rfc" ? " selected" : ""; ?>>RFC 2046</option></select>
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              &#20998;&#21367;&#22823;&#23567;:&nbsp;<input name=partSize size=1 value=<?PHP  echo $_COOKIE["partSize"] ? $_COOKIE["partSize"] : 10; ?>>&nbsp;&#1052;&#1073;
                                            </td>
                                          </tr>
                                        </table>
                                      </td>
                                    </tr>
                                  </table>
                              </form>
                            <?PHP 
                          }
                      break;

                      case "mail_go":
                        if(!checkmail($_GET["email"]))
                          {
                            echo "&#24744;&#36755;&#20837;&#30340;&#19981;&#26159;&#19968;&#20010;&#26377;&#25928;&#30340;&#30005;&#23376;&#37038;&#20214;&#22320;&#22336;.<br><br>";
                          }
                        else
                          {
                            ?>
                              <script>
                                function mail(str, field) {
                                  document.getElementById("mailPart." + field).innerHTML = str;
                                  return true;
                                }
                              </script>
                            <?PHP 
                            $_GET["partSize"] = ((isset($_GET["partSize"]) & $_GET["split"] == "on") ? $_GET["partSize"] * 1024 * 1024 : FALSE);
                            for($i = 0; $i < count($_GET["files"]); $i++)
                              {
                                $file = $list[$_GET["files"][$i]];
                                if(file_exists($file["name"]))
                                  {
                                    if(xmail("Myfiles@down.load", $_GET[email], "&#25991;&#20214;: ".basename($file["name"]), "&#25991;&#20214;: ".basename($file["name"])."\r\n"."&#26469;&#28304;: ".$file["link"].($file["comment"] ? "\r\n&#27880;&#37322;: ".str_replace("\\r\\n", "\r\n", $file["comment"]) : ""), $file["name"], $_GET["partSize"], $_GET["method"]))
                                      {
                                        echo "<script>mail('&#25991;&#20214; <b>".basename($file["name"])."</b>&#24050;&#32463;&#21457;&#36865;&#21040;: <b>".$_GET["email"]."</b>', '".basename($file["name"])."');</script>\r\n<br>";
                                      }
                                    else
                                      {
                                        echo "&#21457;&#36865;&#25991;&#20214;&#38169;&#35823;!<br>";
                                      }
                                  }
                                else
                                  {
                                    echo "&#25991;&#20214; <b>".$file["name"]."</b>&#26410;&#21457;&#29616;<br><br>";
                                  }
                              }
                          }
                      break;

                      case "split":
                        if(count($_GET["files"]) < 1)
                          {
                            echo "&#35831;&#33267;&#23569;&#36873;&#25321;&#19968;&#20010;&#25991;&#20214;.<br><br>";
                          }
                        else
                          {
                            ?>
                            <form method="post">
                              <input type=hidden name=act value="split_go">
                               <table align="center">
                                <tr>
                                  <td>
                                    <table>
                              <?PHP 
                                for($i = 0; $i < count($_GET["files"]); $i++)
                                  {
                                    $file = $list[$_GET["files"][$i]];
                                    ?>
                                      <input type=hidden name="files[]" value="<?PHP  echo $_GET["files"][$i]; ?>">
                                          <tr>
                                            <td align="center"><b><?PHP  echo basename($file["name"]); ?></b></td>
                                          </tr>
                                          <tr>
                                            <td>
                                              &#20998;&#21367;&#22823;&#23567;:&nbsp;<input name="partSize[]" size=1 value=<?PHP  echo $_COOKIE["partSize"] ? $_COOKIE["partSize"] : 10; ?>>&nbsp;MB
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                              &#20445;&#23384;&#21040;:&nbsp;<input name="saveTo[]" size=40 value="<?PHP  echo addslashes(dirname($file["name"])); ?>">
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                            </td>
                                          </tr>
                                    <?PHP 
                                  }
                              ?>
                                    </table>
                                  </td>
                                  <td>
                                    <input type=submit value="&#20998;&#21106;">
                                  </td>
                                </tr>
                                <tr>
                                  <td>
                                  </td>
                                </tr>
                              </table>
                            </form>
                            <?PHP 
                          }
                      break;

                      case "split_go":
                        for($i = 0; $i < count($_GET["files"]); $i++)
                          {
                            $file = $list[$_GET["files"][$i]];
                            $partSize = urldecode($_GET["partSize"][$i]) * 1024 * 1024;
                            $saveTo = urldecode($_GET["saveTo"][$i]);
                            $partSize = round($partSize);
                            $fileContents = read_file($file["name"]);
                            $fileSize = strlen($fileContents);
                            $crc = strtoupper(dechex(crc32($fileContents)));
                            $crc = str_repeat("0", 8 - strlen($crc)).$crc;
                            if(file_exists($file["name"]))
                              {
                            echo "Laying out the File <b>".basename($file["name"])."</b> on the part ".bytesToKbOrMb($partSize).", Method Total Commander...<br>";
                            $totalParts = ceil($fileSize / $partSize);
                            echo "&#20998;&#21367;&#25968;&#30446;: <b>".$totalParts."</b><br><br>";
                            $fileTmp = $fileNamePerman = basename($file["name"]);
                            while(strpos($fileTmp, "."))
                              {
                                $fileName .= substr($fileTmp, 0, strpos($fileTmp, ".") + 1);
                                $fileTmp = substr($fileTmp, strpos($fileTmp, ".") + 1);
                              }
                            $fileName = substr($fileName, 0, -1);
                            $path = stripslashes($saveTo.(strstr(realpath("./"), ":") ? "\\\\" : "/"));
                            for($j = 0; $j < $totalParts; $j++)
                              {
                                if($j == 0)
                                  {
                                    $fileChunk = substr($fileContents, 0, $partSize);
                                    if(!@write_file($path.$fileName.".crc", "filename=".basename($file["name"])."\r\n"."size=".$fileSize."\r\n"."crc32=".$crc."\r\n"))
                                      {
                                        echo "&#22312;&#25991;&#20214;&#20013;&#23427;&#19981;&#21487;&#33021;&#23548;&#20837;&#35760;&#24405;<b>".$fileName.".crc"."</b> !<br><br>";
                                      }
                                    else
                                      {
                                        $time = explode(" ", microtime());
                                        $time = str_replace("0.", $time[1], $time[0]);
                                        $list[$time] = array("name"    => $path.$fileName.".crc",
                                                             "size"    => bytesToKbOrMb(strlen(read_file($path.$fileName.".crc"))),
                                                             "date"    => $time,
                                                             "comment" => "&#26631;&#39064;&#20998;&#21367; ".$fileNamePerman);
                                      }
                                    if(!@write_file($path.$fileName.".001", $fileChunk))
                                      {
                                        echo "&#22312;&#25991;&#20214;&#20013;&#23427;&#19981;&#21487;&#33021;&#23548;&#20837;&#35760;&#24405;<b>".$fileName.".001"."</b> !<br><br>";
                                      }
                                    else
                                      {
                                        $time = explode(" ", microtime());
                                        $time = str_replace("0.", $time[1], $time[0]);
                                        $list[$time] = array("name"    => $path.$fileName.".001",
                                                             "size"    => bytesToKbOrMb(strlen($fileChunk)),
                                                             "date"    => $time,
                                                             "comment" => ($j + 1)."-&#1072;&#1103; &#1095;&#1072;&#1089;&#1090;&#1100; (&#1080;&#1079; ".$totalParts.") File&#1072; ".$fileNamePerman);
                                      }
                                  }
                                elseif($j == $totalParts - 1)
                                  {
                                    $fileChunk = substr($fileContents, $j * $partSize);
                                    $num = strlen($j + 1) == 2 ? "0".($j + 1) : (strlen($j + 1) == 1 ? "00".($j + 1) : ($j + 1));
                                    if(!@write_file($path.$fileName.".".$num, $fileChunk))
                                      {
                                        echo "&#22312;&#25991;&#20214;&#20013;&#23427;&#19981;&#21487;&#33021;&#23548;&#20837;&#35760;&#24405; <b>".$fileName.".".$num."</b> !<br><br>";
                                      }
                                    else
                                      {
                                        $time = explode(" ", microtime());
                                        $time = str_replace("0.", $time[1], $time[0]);
                                        $list[$time] = array("name"    => $path.$fileName.".".$num,
                                                             "size"    => bytesToKbOrMb(strlen($fileChunk)),
                                                             "date"    => $time,
                                                             "comment" => ($j + 1)."-&#1072;&#1103; &#1095;&#1072;&#1089;&#1090;&#1100; (&#1080;&#1079; ".$totalParts.") File&#1072; ".$fileNamePerman);
                                      }
                                  }
                                else
                                  {
                                    $fileChunk = substr($fileContents, $j * $partSize, $partSize);
                                    $num = strlen($j + 1) == 2 ? "0".($j + 1) : (strlen($j + 1) == 1 ? "00".($j + 1) : ($j + 1));
                                    if(!@write_file($path.$fileName.".".$num, $fileChunk))
                                      {
                                        echo "&#22312;&#25991;&#20214;&#20013;&#23427;&#19981;&#21487;&#33021;&#23548;&#20837;&#35760;&#24405; <b>".$fileName.".".$num."</b> !<br><br>";
                                      }
                                    else
                                      {
                                        $time = explode(" ", microtime());
                                        $time = str_replace("0.", $time[1], $time[0]);
                                        $list[$time] = array("name"    => $path.$fileName.".".$num,
                                                             "size"    => bytesToKbOrMb(strlen($fileChunk)),
                                                             "date"    => $time,
                                                             "comment" => ($j + 1)."-&#1072;&#1103; &#1095;&#1072;&#1089;&#1090;&#1100; (&#1080;&#1079; ".$totalParts.") File&#1072; ".$fileNamePerman);
                                      }
                                  }
                              }
                            unset($fileName);
                            if(!updateListInFile($list))
                              {
                                  echo "&#23427;&#19981;&#21487;&#33021;&#26356;&#26032;&#21015;&#34920;!<br><br>";
                              }
                             } //if(file_exists($file["name"]))
                          }
                      break;

                      case "rename":
                        if(count($_GET["files"]) < 1)
                          {
                            echo "&#35831;&#36873;&#25321;&#33267;&#23569;&#19968;&#20010;&#25991;&#20214;.<br><br>";
                          }
                        else
                          {
                            ?>
                            <form method="post">
                              <input type=hidden name=act value="rename_go">
                               <table align="center">
                                <tr>
                                  <td>
                                    <table>
                              <?PHP 
                                for($i = 0; $i < count($_GET["files"]); $i++)
                                  {
                                    $file = $list[$_GET["files"][$i]];
                                    ?>
                                      <input type=hidden name="files[]" value="<?PHP  echo $_GET["files"][$i]; ?>">
                                          <tr>
                                            <td align="center"><b><?PHP  echo basename($file["name"]); ?></b></td>
                                          </tr>
                                          <tr>
                                            <td>
                                              &#26032;&#25991;&#20214;&#21517;:&nbsp;<input name="newName[]" size=25 value="<?PHP  echo basename($file["name"]); ?>">
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>
                                            </td>
                                          </tr>
                                    <?PHP 
                                  }
                              ?>
                                    </table>
                                  </td>
                                  <td>
                                    <input type=submit value="&#37325;&#21629;&#21517;">
                                  </td>
                                </tr>
                                <tr>
                                  <td>
                                  </td>
                                </tr>
                              </table>
                            </form>
                            <?PHP 
                          }
                      break;

                      case "rename_go":
                         $smthExists = FALSE;
                         for($i = 0; $i < count($_GET["files"]); $i++)
                          {
                            $file = $list[$_GET["files"][$i]];
                            if(file_exists($file["name"]))
                              {
                                $smthExists = TRUE;
                                $newName = dirname($file["name"]).(strstr(realpath("./"), ":") ? "\\" : "/").$_GET["newName"][$i];
                                if(@rename($file["name"], $newName))
                                  {
                                    echo "&#25991;&#20214; <b>".$file["name"]."</b> &#37325;&#21629;&#21517;&#20026; <b>".basename($newName)."</b><br><br>";
                                    $list[$_GET["files"][$i]]["name"] = $newName;
                                  }
                                else
                                  {
                                    echo "&#19981;&#33021;&#37325;&#21629;&#21517;&#25991;&#20214; <b>".$file["name"]."</b>!<br><br>";
                                  }
                              }
                            else
                             {
                               echo "&#25991;&#20214; <b>".$file["name"]."</b> &#26410;&#21457;&#29616;<br><br>";
                             }
                          }
                         if($smthExists)
                           {
                             if(!updateListInFile($list))
                              {
                                  echo "&#23427;&#19981;&#21487;&#33021;&#26356;&#26032;&#21015;&#34920;!<br><br>";
                              }
                           }
                      break;

                    }
                  if($list)
                    {
                  ?>
                   <script>
                    function setCheckboxes(act)
                    {
                      elts =  document.forms["flist"].elements["files[]"];
                      var elts_cnt  = (typeof(elts.length) != 'undefined') ? elts.length : 0;
                      if (elts_cnt)
                        {
                          for (var i = 0; i < elts_cnt; i++)
                            {
                              elts[i].checked = (act == 1 || act == 0) ? act : elts[i].checked ? 0 : 1;
                            }
                        }
                    }
                  </script>
                  <form name="flist" method="post">
                  <a href="javascript:setCheckboxes(1);" style="color: #0000FF;">&#36873;&#25321;&#20840;&#37096;</a> |
                  <a href="javascript:setCheckboxes(0);" style="color: #0000FF;">&#21462;&#28040;&#20840;&#37096;</a> |
                  <a href="javascript:setCheckboxes(2);" style="color: #0000FF;">&#21453;&#36873;</a> <br><br>
                  <table cellpadding="3" cellspacing="1">
                    <tbody>
                      <tr bgcolor="#E1E1E1" valign="bottom" align="center">
                        <td>
                          <select name="act" onChange="javascript:void(document.flist.submit());">
                            <option>&#25805;&#20316;</option>
                            <option value="mail">&#21457;&#36865;&#37038;&#20214;</option>
                            <option value="split">&#20998;&#21106;</option>
                            <option value="rename">&#37325;&#21629;&#21517;</option>
                            <option value="delete">&#21024;&#38500;</option>
                          </select>
                        </td>
                        <td>&#25991;&#20214;&#21517;</td>
                        <td>&#22823;&#23567;</td>
                        <td>&#26469;&#28304;</td>
                        <td>&#27880;&#37322;</td>
                        <td>&#26085;&#26399;</td>
                      </tr>
                  <?PHP 
                    }
                  else
                    {
                      echo "&#27809;&#26377;&#25991;&#20214;&#34987;&#21457;&#29616;";
                    }
                  if($list)
                    {
                      foreach($list as $key => $file)
                        {
                          if(file_exists($file["name"]))
                            {
                              $inCurrDir = strstr(dirname($file["name"]), realpath("./")) ? TRUE : FALSE;
                              if($inCurrDir)
                                {
                                  $Path = parse_url($PHP_SELF);
                                  $Path = substr($Path["path"], 0, strlen($Path["path"]) - strlen(strrchr($Path["path"], "/")));
                                }
                              ?>
                              <tr onmouseover="this.bgColor='#C8D0E6';" onmouseout="this.bgColor='#F2F2F2';" align="center" bgcolor="#f2f2f2" title="<?PHP  echo $file["name"]; ?>">
                                <td><input type=checkbox name="files[]" value="<?PHP  echo $file["date"]; ?>"></td>
                                <td><?PHP  echo $inCurrDir ? "<a href=\"".$Path.substr(dirname($file["name"]), strlen(realpath("./")) + 1)."/".basename($file["name"]) : ""; echo $inCurrDir ? "\">".basename($file["name"])."</a>" : basename($file["name"]); ?></td>
                                <td><?PHP  echo $file["size"]; ?></td>
                                <td><?PHP  echo $file["link"] ? "<a href=\"".$file["link"]."\" style=\"color: #0000FF;\">".$file["link"]."</a>" : "" ; ?></td>
                                <td><?PHP  echo $file["comment"] ? str_replace("\\r\\n", "<br>", $file["comment"]) : ""; ?></td>
                                <td><?PHP  echo date("d.m.Y H:i:s", $file[date]) ?></td>
                              </tr>
                              <?PHP 
                            }
                        }
                    }
                  if($list)
                    {
                  ?>
                    </tbody>
                </table>
                    <form>
                  <?PHP 
                    }
                  ?>
                </td>
              </tr>
            </table>

          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<br>
</body>
</html>
        <?PHP 
        die;
      }
    if(@$_GET["savesettings"] == "on")
      {
        setcookie("savesettings", TRUE);
        if($_GET["domail"] == "on")
          {
            setcookie("domail", TRUE);
            if(checkmail($_GET["email"]))
              {
                setcookie("email", $_GET["email"]);
              }
            else
              {
                setcookie("email", "", time() - 3600);
              }
            if($_GET["split"] == "on")
              {
                setcookie("split", TRUE);
                if(is_numeric($_GET["partSize"]))
                  {
                    setcookie("partSize", $_GET["partSize"]);
                  }
                else
                  {
                    setcookie("partSize", "", time() - 3600);
                  }
                if(in_array($_GET["method"], array("tc", "rfc")))
                  {
                    setcookie("method", $_GET["method"]);
                  }
                else
                  {
                    setcookie("method", "", time() - 3600);
                  }
              }
            else
              {
                setcookie("split", "", time() - 3600);
              }
          }
        else
          {
            setcookie("domail", "", time() - 3600);
          }
        if($_GET["saveto"] == "on")
          {
            setcookie("saveto", TRUE);
            if(isset($_GET["path"]))
              {
                setcookie("path", $_GET["path"]);
              }
            else
              {
                setcookie("path", "", time() - 3600);
              }
          }
        else
          {
            setcookie("saveto", "", time() - 3600);
          }
        if($_GET["useproxy"] == "on")
          {
            setcookie("useproxy", TRUE);
            if(strlen(strstr($_GET["proxy"], ":")) > 0)
              {
                setcookie("proxy", $_GET["proxy"]);
              }
            else
              {
                setcookie("proxy", "", time() - 3600);
              }
          }
        else
          {
            setcookie("useproxy", "", time() - 3600);
          }
      }
    if(isset($_GET['saveto']) & !$_GET['path'])
      {
        html_error("Is not correct way for retaining of File");
      }
    if(isset($_GET['useproxy']) & (!$_GET['proxy'] || !strstr($_GET['proxy'], ":")))
      {
        html_error("The address of proxy of the server is not correct");
      }
    if(isset($_GET['domail']) & !checkmail(@$_GET['email']))
      {
        html_error("Incorrect E-mail");
      }
    elseif(@$_GET['domail'] & @$_GET['split'] & !is_numeric($_GET['partSize']))
      {
        html_error("The size of the part is erroneously indicated");
      }
    $Referer = $LINK;
    $Url = parse_url($LINK);
    if(@$Url['scheme'] == "ftp")
      {
        $Url = parse_url($_SERVER[HTTP_REFERER]."?act=ftp_get&host=".$Url['host']."&port=".$Url['port']."&user=".$Url[user]."&pass=".$Url[pass]."&path=".str_replace("&", "%26", $Url[path]));
      }
    if(!in_array($Url['host'], array("rapidshare.de", "www.megaupload.com", "www.upload2.net", "sr1.mytempdir.com", "www.mytempdir.com",
                                   "getfile.biz", "webfile.ru", "slil.ru", "zalil.ru")))
      {
          $directLink = TRUE;
      }
    else
      {
          $page = geturl($Url[host], 80, $Url[path]."?".$Url[query], 0, 0, 0, 0, isset($_GET["useproxy"]) ? $_GET["proxy"] : "");
      }
    if(!@$page & !$directLink)
      {
        html_error("Error with obtaining of the page".$LINK."<br>".$lastError);
      }
    else
      {
        if(!$directLink)
        {
        switch($Url[host]) {
          case "rapidshare.de":
            $post["uri"] = $Url[path];
            //$post[hint] = cut_str($page, "name=\"hint\" value=\"", "\">");
            //$post[letsgo] = "Free";
            $post["dl.start"] = "Free";
            $url = "/";
            $countDownLeft = "var c = ";
            $countDownRight = ";";
          break;

          case "www.megaupload.com":
            $noSecondPage = TRUE;
            $countDownLeft = "</table>\t\t\t\r\n\t\t\t\t<script language=\"Javascript\">\r\n\t\t\t\t";
            $countDownRight = ";";
          break;

          case "www.upload2.net":
            $noSecondPage = TRUE;
            $countDownLeft = "wt=";
            $countDownRight = ";func";
          break;

          case "sr1.mytempdir.com":
          case "www.mytempdir.com":
            $noSecondPage = TRUE;
            $countDownLeft = "loading()', ";
            $countDownRight = "000);";
          break;

          case "getfile.biz":
          case "webfile.ru":
          case "slil.ru":
          case "zalil.ru":
            $noSecondPage = TRUE;
            $noCountDown = TRUE;
          break;

          default:
            $directLink = TRUE;
            $noSecondPage = TRUE;
            unset($post);
          break;
        }
        if(!$noSecondPage)
          {
            $Referer = "http://".$Url[host].$url;
            $page = geturl($Url[host], 80, $url, $LINK, 0, $post, 0, isset($_GET["useproxy"]) ? $_GET["proxy"] : "");
            if(!$page)
              {
                html_error("Error with the demand of the second page");
              }
          }
        if(strstr($page, "This file exceeds your download-limit.") || strstr($page, "KB in one hour"))
          {
            html_error("Service indicates that they will reach the limit of running off to this IP-Address.");
          }
        if(strstr($page, "is already downloading a file"))
          {
            html_error("Service indicates that from your IP-Address has already been drawn off one File.");
          }
        if(strstr($page, "Too many users downloading right now."))
          {
            html_error("Service indicates that too many users right now..<br><span id=\"repeat\"></span>
                            <script>
                              var c = 10;
                              fc();
                              function fc() {
                                if(c > 0)
                                  {
                                    document.getElementById(\"repeat\").innerHTML = \"&#1055;&#1086;&#1074;&#1090;&#1086;&#1088; &#1095;&#1077;&#1088;&#1077;&#1079; \" + c + ' Sec.';
                                    c = c - 1;
                                    setTimeout(\"fc()\", 1000)
                                  }
                                else
                                  {
                                    location.reload();
                                  }
                            }
                            </script>");
          }
        if(strstr($page, "is not allowed to use the free-service anymore today"))
          {
            html_error("Service indicates that this IP-Address is not allowed to use the free-service anymore today.");
          }
        if(strstr($page, "html not found."))
          {
            html_error("Service indicates that such File is not found.");
          }
        /*if($Url["host"] == "rapidshare.de")
          {
              $Uuid = cut_str($page, "uuid=", ";");
              if(!is_numeric($Uuid) || !$Uuid)
                {
                  html_error("&#1054;&#1096;&#1080;&#1073;&#1082;&#1072; &#1087;&#1088;&#1080; &#1087;&#1086;&#1083;&#1091;&#1095;&#1077;&#1085;&#1080;&#1080; &#1085;&#1086;&#1084;&#1077;&#1088;&#1072; &#1089;&#1077;&#1089;&#1089;&#1080;&#1080;.".pre($page));
                }
          }
        */
            switch($Url["host"])
              {
                case "rapidshare.de":
                  $Href = cut_str(urldecode(cut_str($page, "unescape('", "')")), "href=\"", "\"");
                break;

                case "www.megaupload.com":
                  $Href = cut_str($page, "document.getElementById(\"downloadhtml\").innerHTML = '<a href=\"", "\"");
                break;

                case "www.upload2.net":
                  $Href = cut_str($page, "document.getElementById(\"dl\").innerHTML='<a class=common href=\"", "\">Click here to");
                break;

                case "sr1.mytempdir.com":
                case "www.mytempdir.com":
                   $Href = cut_str(urldecode(cut_str($page, "unescape('", "')")), "Download: <a href=\"", "\"");
                break;

                case "getfile.biz":
                  $Href = cut_str($page, "</b><br><br>\n<a href=\"", "\"");
                break;

                case "webfile.ru":
                  $Href = "http://webfile.ru".cut_str($page, "<p class=link><b>&gt;&gt;&gt <a href=\"", "\"");
                  $Tmp = parse_url($Href);
                  $Referer = $Href;
                  $Href = cut_str(geturl($Tmp["host"], 80, $Tmp["path"], $Href, 0, 0, 0,
                                  isset($_GET["useproxy"]) ? $_GET["proxy"] : ""), "Location: ", "\r");
                  unset($Tmp);
                break;

                case "slil.ru":
                case "zalil.ru":
                  $Href = "http://".$Url["host"].cut_str($page, ";URL=", "\"");
                break;
              }
            $Url = parse_url($Href);
            if(!$Href)
              {
                html_error("Error with obtaining of references.".pre($page));
              }
            if($noCountDown)
              {
                  $countDown = 0;
              }
            else
              {
                  $countDown = cut_str($page, $countDownLeft, $countDownRight);
              }
             if(strstr($Url[host], "megaupload.com"))
              {
                list($var, $countDown) = explode("=", $countDown);
                if(!$countDown)
                  {
                    $countDown = 50;
                  }
              }
            if(!$countDown & $countDown != 0)
              {
                html_error("Error with obtaining of waiting time.".pre($page));
              }
            $tmp = explode("/", $Url[path]);
            $FileName = array_pop($tmp);
        }
      else
        {
           $countDown = 0;
           $tmp = explode("/", $LINK);
           $FileName = array_pop($tmp);
        }
            ?>
            <html>
              <head>
                <meta http-equiv=Content-Type content="text/html; charset=gb2312">
                <title>&#35831;&#31561;&#24453;....</title>
              </head>
              <body style="FONT-FAMILY: Tahoma; FONT-SIZE: 11px;">
              <center><span id="dl"></span></center>
              <script>
                var c = <?PHP  echo $countDown; ?>;
                fc();
                function fc() {
                  if(c > 0)
                    {
                      document.getElementById("dl").innerHTML = "Intializing..Please wait for " + c + ' Secs.';
                      c = c - 1;
                      setTimeout("fc()", 1000)
                    }
                  else
                    {
                      location.href = '?FileName=<?PHP  echo urlencode($FileName); ?>&host=<?PHP  echo $Url[host]; ?>&path=<?PHP  echo urlencode($Url[path].($Url["query"] ? "?".$Url["query"] : "")); ?>&referer=<?PHP  echo urlencode($Referer); ?>&uuid=<?PHP  echo $Uuid; ?>&email=<?PHP  echo $_GET["domail"] ? $_GET["email"] : ""; ?>&partSize=<?PHP  echo $_GET[split] ? $_GET[partSize] : ""; ?>&method=<?PHP  echo $_GET[method]; ?>&proxy=<?PHP  echo $_GET["useproxy"] ? $_GET["proxy"] : ""; ?>&saveto=<?PHP  echo $_GET["path"]; ?>&link=<?PHP  echo urlencode($_GET["link"]); ?><?PHP  echo $_GET["add_comment"] == "on" ? "&comment=".urlencode($_GET["comment"]) : ""; ?>';
                    }
                }
              </script>
              </body>
            </html>
            <?PHP 
      }

  }
else
  {
    ?>
    <html>
      <head>
        <meta http-equiv=Content-Type content="text/html; charset=gb2312">
        <title>&#27491;&#22312;&#19979;&#36733;&#25991;&#20214;...</title>
      </head>
      <body style="FONT-FAMILY: Tahoma; FONT-SIZE: 11px;">
      <center>
    <?PHP 
    set_time_limit(0);
    $_GET["FileName"] = urldecode($_GET["FileName"]);
    $_GET["path"] = urldecode($_GET["path"]);
    $_GET["referer"] = urldecode($_GET[""]);
    $_GET["link"] = urldecode($_GET["link"]);
    $_GET["path"] = str_replace("act=ftp_get", "act=ftpget", $_GET["path"]);
    //$pathWithName = $_GET["saveto"].(strstr(realpath("./"), ":") ? "\\\\" : "/").$_GET["FileName"];
    $pathWithName = $_GET["saveto"].(strstr(realpath("./"), ":") ? "\\" : "/").$_GET["FileName"];
    echo "&#36830;&#25509;&#30830;&#23450;&#21040;...<b>".(strstr(strtolower($_GET[path]), strtolower(basename(__FILE__))) ? cut_str($_GET[path], "&host=", "&port") : $_GET[host])."</b>...<br>";
    $file = geturl($_GET[host], 80, $_GET[path], $_GET[referer], ($_GET[uuid] ? "uuid=".$_GET[uuid] : ""), 0, $pathWithName, $_GET["proxy"]);
    if($lastError)
      {
        echo $lastError;
      }
    elseif($file[bytesReceived] == $file[bytesTotal])
      {
        $inCurrDir = strstr(dirname($pathWithName), realpath("./")) ? TRUE : FALSE;
        if($inCurrDir)
          {
            $Path = parse_url($PHP_SELF);
            $Path = substr($Path["path"], 0, strlen($Path["path"]) - strlen(strrchr($Path["path"], "/")));
          }
        echo "<script>pr(100, '".$file[size]."', '".$file[speed]."')</script>\r\n";
        echo "&#25991;&#20214; <b>".($inCurrDir ? "<a href=\"".$Path.substr(dirname($pathWithName), strlen(realpath("./")) + 1)."/".$_GET[FileName]."\">" : "").$_GET[FileName].($inCurrDir ? "</a>" : "")."</b> (<b>".$file[size]."</b>) &#24050;&#34987;&#20445;&#23384;<br>&#26102;&#38388;: <b>".$file[time]."</b><br>&#36895;&#24230;: <b>".$file[speed]." KB/&#31186;</b><br>";
        if(!$file["alreadyExisted"] || ($file["alreadyExisted"] & ($file["alreadyExistedMd5"] != md5_file($pathWithName))))
          {
            if(!write_file("files.lst", serialize(array("name" => $pathWithName, "size" => $file["size"], "date" => time(), "link" => $_GET["link"], "comment" => str_replace("\n", "\\n", str_replace("\r", "\\r", $_GET["comment"]))))."\r\n", 0))
              {
                echo "&#23427;&#19981;&#21487;&#33021;&#26356;&#26032;&#21015;&#34920;&#22312;&#25991;&#20214;&#37324;.<br>";
              }
          }
        if($_GET["email"])
          {
            $_GET[partSize] = (isset($_GET[partSize]) ? $_GET[partSize] * 1024 * 1024 : FALSE);
            if(xmail("Myfiles@down.load", $_GET["email"], "&#25991;&#20214;: ".basename($_GET["FileName"]), "&#25991;&#20214;: ".basename($_GET["FileName"])."\r\n"."&#26469;&#28304;:".$_GET["link"].($_GET["comment"]? "\r\n"."&#27880;&#37322;: ".str_replace("\\r\\n", "\r\n", $_GET["comment"]) : ""), $pathWithName, $_GET["partSize"], $_GET["method"]))
              {
                echo "<script>mail('&#25991;&#20214;&#24050;&#32463;&#34987;&#21457;&#36865;&#21040; <b>".$_GET[email]."</b>', '".basename($_GET["FileName"])."');</script>\r\n";
              }
            else
              {
                echo "&#21457;&#36865;&#25991;&#20214;&#38169;&#35823;!<br>";
              }
          }
        $Path = parse_url($PHP_SELF);
        $Path = substr($Path["path"], 0, strlen($Path["path"]) - strlen(strrchr($Path["path"], "?")));
        echo "<br><a href=\"".$Path."\" style=\"color: #0000FF;\">&#36820;&#22238;&#39318;&#39029;</a>";
      }
    else
      {
        echo "&#25918;&#24323;&#36830;&#25509;:-(<br><a href=\"javascript:location.reload();\">&#37325;&#26032;&#23581;&#35797;</a>";
      }
    ?>
      </center>
      </body>
    </html>
    <?PHP 
  }

function geturl($host, $port, $url, $referer = 0, $cookie = 0, $post = 0, $saveToFile = 0, $proxy = 0, $deploy = 0) {
global $nn, $lastError, $PHP_SELF;
if($post !== 0)
  {
    $method = "POST";
    $postdata = formpostdata($post);
    $length = strlen($postdata);
    $content_tl = "Content-Type: application/x-www-form-urlencoded".$nn."Content-Length: ".$length.$nn;
  }
else
  {
    $method = "GET";
    $postdata = "";
    $content_tl = "";
  }
if($cookie !== 0)
  {
    $cookies = "Cookie: ".$cookie.$nn;
  }
else
  {
    $cookies = "";
  }
if($referer !== 0)
  {
    $referer = "Referer: ".$referer.$nn;
  }
else
  {
    $referer = "";
  }
if($proxy)
  {
    list($proxyHost, $proxyPort) = explode(":", $proxy);
    $url = "http://".$host.":".$port.$url;
    $host = $host.":".$port;
  }
$zapros=
$method." ".str_replace(" ", "%20", $url)." HTTP/1.0".$nn.
"Host: ".$host.$nn.
"User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.0.3705)".$nn.
"Accept: */*".$nn.
"Accept-Language: ru".$nn.
"Connection: Keep-Alive".$nn.
$referer.
$cookies.
$content_tl.$nn.$postdata;
if($deploy !== 0)
  {
  pre($zapros);
  exit;
  }
//write_file("debug", $zapros);

//$fp = @fsockopen($proxyHost ? $proxyHost : $host, $proxyPort ? $proxyPort : $port, &$errno, &$errstr, 15);
$fp = @fsockopen($proxyHost ? $proxyHost : $host, $proxyPort ? $proxyPort : $port, $errno, $errstr, 15);

if($errno || $errstr)
  {
  //echo $errno;
  $lastError = $errstr;
  return false;
  }
fputs($fp,$zapros);
if($saveToFile)
  {
    if(file_exists($saveToFile))
      {
        $alreadyExisted = TRUE;
        $alreadyExistedMd5 = md5_file($saveToFile);
      }
    $fs = @fopen($saveToFile, "w");
    if(!$fs)
      {
        $secondName = dirname($saveToFile).(strstr(realpath("./"), ":") ? "\\\\" : "/").str_replace(":", "", str_replace("?", "", basename($saveToFile)));
        $fs = @fopen($secondName, "w");
        if(!$fs)
          {
            $lastError = "&#25991;&#20214; ".$saveToFile." &#26080;&#27861;&#34987;&#20445;&#23384;.<br>".
                         "&#22312;&#27492;&#25991;&#20214;&#22841;&#37324;&#38754;&#19981;&#33021;&#23384;&#20648; (&#25991;&#20214;&#22841;&#23646;&#24615;&#20063;&#35768;&#19981;&#26159; 777)".
                         " &#35831;&#24744;&#26816;&#26597;&#24182;&#20462;&#27491;<br>".
                         "<a href=javascript:location.reload(); style=\"color: #0000FF;\">&#37325;&#22797;</a>";
            return FALSE;
          }
      }
    flock($fs, LOCK_EX);
    $timeStart = getmicrotime();
  }
//socket_set_timeout($fp, 10);
//$f = fopen("debug", "w");
while($data = fgets($fp, 128))
  {
    //fwrite($f, $data);
    if($saveToFile)
      {
        if($headersReceived)
          {
            $bytesSaved = fwrite($fs, $data);
            if($bytesSaved > -1)
              {
                $bytesReceived += $bytesSaved;
              }
            else
              {
                echo "&#23427;&#26159;&#19981;&#21487;&#33021;&#22312;&#25991;&#20214;&#20013;&#25191;&#34892;&#23384;&#20648;&#30340; ".$saveToFile;
                return false;
              }
              if($bytesReceived >= $bytesTotal)
                {
                  $percent = 100;
                }
              else
                {
                  $percent = round($bytesReceived / $bytesTotal * 100, 2);
                }
              if($bytesReceived > $last + $chunkSize)
              {
                $received = bytesToKbOrMb($bytesReceived);
                $time = getmicrotime() - $timeStart;
                $chunkTime = $time - $lastChunkTime;
                $lastChunkTime = $time;
                $speed = round($chunkSize / 1024 / $chunkTime, 2);
                //echo "&#1057;&#1082;&#1072;&#1095;&#1072;&#1085;&#1086; <b>".$received."</b> &mdash; <b>".$percent."%</b>; &#1074;&#1088;&#1077;&#1084;&#1103; &mdash; <b>".$tmpTime."</b>; &#1089;&#1082;&#1086;&#1088;&#1086;&#1089;&#1090;&#1100; &mdash; <b>".$speed." &#1050;&#1073;/&#1089;</b><br>";
                echo "<script>pr(".$percent.", '".$received."', ".$speed.")</script>\r\n";
                $last = $bytesReceived;
              }
          }
        else
          {
            $tmp .= $data;
            if(strstr($tmp, "\n\n"))
              {
                  $det = "\n\n";
              }
            elseif(strstr($tmp, $nn.$nn))
              {
                  $det = $nn.$nn;
              }
            if($det)
              {
                $tmp = explode($det, $tmp);
                $bytesSaved = fwrite($fs, $tmp[1]);
                if($bytesSaved > -1)
                  {
                    $bytesReceived += $bytesSaved;
                  }
                else
                  {
                    echo "&#23427;&#26159;&#19981;&#21487;&#33021;&#22312;&#25991;&#20214;&#20013;&#25191;&#34892;&#23384;&#20648;&#30340; ".$saveToFile."<br>";
                    return FALSE;
                  }
                $headersReceived = true;
                $redirect = cut_str($tmp[0], "Location:", "\n");
                if($redirect)
                {
                    $lastError = "Is obtained perenapravleniye on<b>".$redirect."</b><br>On the course of events, the reference became obsolete. So that from the beginning...<br><br><a href=\"".$PHP_SELF."\" style=\"color: #0000FF;\">To the main thing</a>";
                    fclose($fs);
                    unlink($saveToFile) ? "" : unlink($secondName);
                    return FALSE;
                }
                $bytesTotal = cut_str($tmp[0], "Content-Length: ", "\n");
                $fileSize = bytesToKbOrMb($bytesTotal);
                $chunkSize = round($bytesTotal / 333);
                echo "&#19979;&#36733;&#21040; :  <b>".$saveToFile."</b>, &#25991;&#20214;&#22823;&#23567; : <b>".$fileSize."</b>...<br>";
                ?>
                <br>
                <table cellspacing=0 cellpadding=0 style="FONT-FAMILY: Tahoma; FONT-SIZE: 11px;">
                  <tr>
                    <td></td>
                    <td>
                      <div style='border:#BBBBBB 1px solid; width:300px; height:10px;'>
                        <div id=progress style='background-color:#000099; margin:1px; width:0%; height:8px;'>
                        </div>
                      </div>
                    </td>
                    <td></td>
                  <tr>
                  <tr>
                   <td align=left id=received>0 KB</td>
                   <td align=center id=percent>0%</td>
                   <td align=right id=speed>0 KB/&#31186;</td>
                  </tr>
                </table>
                <script>
                  function pr(percent, received, speed){
                    document.getElementById("received").innerHTML = '<b>' + received + '</b>';
                    document.getElementById("percent").innerHTML = '<b>' + percent + '%</b>';
                    if(percent > 90){percent = percent - 1;}
                    document.getElementById("progress").style.width = percent + '%';
                    document.getElementById("speed").innerHTML = '<b>' + speed + ' KB/&#31186;</b>';
                    return true;
                  }

                  function mail(str, field) {
                    document.getElementById("mailPart." + field).innerHTML = str;
                    return true;
                  }
                </script>
                <br>
                <?PHP 
              }
          }
      }
    else
      {
        $page .= $data;
      }
  }
//fclose($f);
if($saveToFile)
  {
    flock($fs, LOCK_UN);
    fclose($fs);
    if($bytesReceived <= 0)
      {
        $lastError = "&#19968;&#20010;&#26410;&#30693;&#38169;&#35823;(&#25991;&#20214;&#26469;&#28304;&#26159;&#27491;&#30830;&#30340;&#21527;&#65311;)<br><a href=\"javascript:history.back(-1);\">&#36820;&#22238;</a>";
        fclose($fp);
        return FALSE;
      }
  }
fclose($fp);
if($saveToFile)
  {
    return array("time"              => sec2time(round($time)),
                 "speed"             => round($bytesTotal / 1024 / (getmicrotime() - $timeStart), 2),
                 "received"          => true,
                 "size"              => $fileSize,
                 "bytesReceived"     => $bytesReceived,
                 "bytesTotal"        => $bytesTotal,
                 "alreadyExisted"    => $alreadyExisted,
                 "alreadyExistedMd5" => $alreadyExistedMd5);
  }
else
  {
    return $zapros.$nn.$nn.$page;
  }
}

function formpostdata($post) {
global $postdata, $first;
$first = "";
array_walk($post, "fpd_f");
return $postdata;
}

function fpd_f($value, $key) {
global $postdata, $first;
$postdata .= $first.$key."=".urlencode($value);
$first = "&";
}

function cut_str($str, $left, $right){
$str = substr(stristr($str, $left), strlen($left));
$leftLen = strlen(stristr($str, $right));
$leftLen = $leftLen ? -($leftLen) : strlen($str);
$str = substr($str, 0, $leftLen);
return $str;
}

function write_file ($file_name, $data, $trunk = 1) {
if($trunk == 1) {$mode = "w";} elseif ($trunk == 0) {$mode = "a";}
$fp = fopen($file_name, $mode);
if(!$fp)
  {
    return FALSE;
  }
else
  {
    if(!flock($fp, LOCK_EX))
      {
        return FALSE;
      }
    else
      {
        if(!fwrite($fp, $data))
          {
            return FALSE;
          }
        else
          {
            if(!flock($fp, LOCK_UN))
              {
                return FALSE;
              }
            else
              {
                if(!fclose($fp))
                  {
                    return FALSE;
                  }
              }
          }
      }
  }
return TRUE;
}

function read_file($file_name, $count = -1) {
if($count == -1) {$count = filesize($file_name);}
$fp = fopen($file_name, "r");
flock($fp, LOCK_SH);
$ret = fread($fp, $count);
flock($fp, LOCK_UN);
fclose($fp);
return $ret;
}

function pre($var) {
echo "<pre>";
print_r($var);
echo "</pre>";
}

function getmicrotime(){
list($usec, $sec) = explode(" ",microtime());
return ((float)$usec + (float)$sec);
}

function html_error($msg){
?>
<html>
  <head>
    <meta http-equiv=Content-Type content="text/html; charset=windows-1251">
    <title>[:-!-:]</title>
  </head>
  <body style="FONT-FAMILY: Tahoma; FONT-SIZE: 11px;">
  <center>
    <?PHP  echo $msg; ?><br>
    <a href="javascript:history.back(-1);">&#36820;&#22238;</a>
  </center>
  </body>
</html>
<?PHP 
die;
}

function sec2time($time){
$hour = round($time / 3600, 2);
if($hour >= 1)
  {
    $hour = floor($hour);
    $time -= $hour * 3600;
  }
$min = round($time / 60, 2);
if($min >= 1)
  {
    $min = floor($min);
    $time -= $min * 60;
  }
$sec = $time;
$hour = ($hour >= 1) ? $hour." &#23567;&#26102;. " : "";
$min = ($min >= 1) ? $min." &#20998;&#38047;. " : "";
$sec = ($sec >= 1) ? $sec." &#31186;&#38047;" : "";
return $hour.$min.$sec;
}

function bytesToKbOrMb($bytes){
$size = ($bytes > (1024 * 1024)) ? round($bytes / (1024 * 1024), 2)." &#1052;&#1073;" : round($bytes / 1024, 2)." Kb";
return $size;
}

function checkmail($mail) {
   if(strlen($mail) == 0)
     {
      return false;
     }
   if(!preg_match("/^[a-z0-9_\.-]{1,20}@(([a-z0-9-]+\.)+(com|net|org|mil|".
   "edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-".
   "9]{1,3}\.[0-9]{1,3})$/is", $mail))
     {
       return false;
     }
   return true;
}

function xmail($from, $to, $subj, $text, $filename, $partSize = FALSE, $method = FALSE) {
  global $un;
  $fileContents = read_file($filename);
  $fileSize = strlen($fileContents);
  if($method == "tc" & $partSize != FALSE)
    {
      $crc = strtoupper(dechex(crc32($fileContents)));
      $crc = str_repeat("0", 8 - strlen($crc)).$crc;
    }
  $file = base64_encode($fileContents);
  $file = chunk_split($file);
  if($method != "tc")
    {
      unset($fileContents);
    }
  if(!$file)
    {
      return FALSE;
    }
  else
    {
      echo "&#21457;&#36865;&#25991;&#20214;&#21040;&#30005;&#23376;&#37038;&#20214;...<br>";
      for($i = 0; $i < strlen($subj); $i++)
        {
            $subzh .= "=".strtoupper(dechex(ord(substr($subj, $i, 1))));
        }
      $subj = "=?Windows-1251?Q?".$subzh.'?=';
      $un = strtoupper(uniqid(time()));
      $head = "From: ".$from."\n".
              "X-Mailer: Pramod's Rapid Get Downloader\n".
              "Reply-To: ".$from."\n".
              "Mime-Version: 1.0\n".
              "Content-Type: multipart/mixed; boundary=\"----------".$un."\"\n\n";
      $zag = "------------".$un."\nContent-Type: text/plain; charset=Windows-1251\n".
             "Content-Transfer-Encoding: 8bit\n\n".$text."\n\n".
             "------------".$un."\n".
             "Content-Type: application/octet-stream; name=\"".basename($filename)."\"\n".
             "Content-Transfer-Encoding: base64\n".
             "Content-Disposition: attachment; filename=\"".basename($filename)."\"\n\n";
      echo "<span id=mailPart.".basename($filename)."></span><br>";
      if($partSize)
        {
          $partSize = round($partSize);
          if($method == "rfc")
            {
              $multiHeadMain = "From: ".$from."\n".
                               "X-Mailer: Pramod RapidGet Downloader\n".
                               "Reply-To: ".$from."\n".
                               "Mime-Version: 1.0\n".
                               "Content-Type: message/partial; ";
              echo "Split into parts".bytesToKbOrMb($partSize).", &#1084;&#1077;&#1090;&#1086;&#1076; RFC 2046...<br>";
              $totalParts = ceil(strlen($file) / $partSize);
              echo "In all the Parts: <b>".$totalParts."</b><br>";
              $mailed = TRUE;
              for($i = 0; $i < $totalParts; $i++)
               {
                $multiHead = $multiHeadMain."id=\"".$filename."\"; number=".($i + 1)."; total=".$totalParts."\n\n";
                if($i == 0)
                  {
                    $multiHead = $multiHead.$head;
                    $fileChunk = $zag.substr($file, 0, $partSize);
                  }
                elseif($i == $totalParts - 1)
                  {
                    $fileChunk = substr($file, $i * $partSize);
                  }
                else
                  {
                    $fileChunk = substr($file, $i * $partSize, $partSize);
                  }
                echo "<script>mail('Sending of part No&#8470; <b>".($i + 1)."</b>...', '".basename($filename)."');</script>\r\n";
                $mailed = $mailed & mail($to, $subj, $fileChunk, $multiHead);
               }
            }
          elseif($method == "tc")
            {
              echo "Breakdown into parts on ".bytesToKbOrMb($partSize).", The Method Total Commander...<br>";
              $totalParts = ceil($fileSize / $partSize);
              echo "In all the Parts: <b>".$totalParts."</b><br>";
              $mailed = TRUE;
              $fileTmp = $filename;
              while(strpos($fileTmp, "."))
                {
                  $fileName .= substr($fileTmp, 0, strpos($fileTmp, ".") + 1);
                  $fileTmp = substr($fileTmp, strpos($fileTmp, ".") + 1);
                }
              $fileName = substr($fileName, 0, -1);
              for($i = 0; $i < $totalParts; $i++)
                {
                  if($i == 0)
                    {
                      $fileChunk = substr($fileContents, 0, $partSize);
                      $addHeads = addAdditionalHeaders(array("msg" => $text."\r\n"."File ".basename($filename)." (&#1095;&#1072;&#1089;&#1090;&#1100; ".($i + 1)." &#1080;&#1079; ".$totalParts .").", "file" => array("filename" => $fileName.".crc", "stream" => chunk_split(base64_encode("filename=".basename($filename)."\r\n"."size=".$fileSize."\r\n"."crc32=".$crc."\r\n")))));
                      $addHeads .= addAdditionalHeaders(array("file" => array("filename" => $fileName.".001", "stream" => chunk_split(base64_encode($fileChunk)))));
                      //write_file($fileName.".crc", "filename=".basename($filename)."\r\n"."size=".$fileSize."\r\n"."crc32=".$crc."\r\n");
                      //write_file($fileName.".001", $fileChunk);
                    }
                  elseif($i == $totalParts - 1)
                    {
                      $fileChunk = substr($fileContents, $i * $partSize);
                       $addHeads =  addAdditionalHeaders(array("msg" => "File ".basename($filename)." (&#1095;&#1072;&#1089;&#1090;&#1100; ".($i + 1)." &#1080;&#1079; ".$totalParts .").",
                                           "file" => array("filename" => $fileName.".".(strlen($i + 1) == 2 ? "0".($i + 1) : (strlen($i + 1) == 1 ? "00".($i + 1) : ($i + 1))),
                                           "stream" => chunk_split(base64_encode($fileChunk)))));
                      //write_file($fileName.".".(strlen($i + 1) == 2 ? "0".($i + 1) : (strlen($i + 1) == 1 ? "00".($i + 1) : ($i + 1))), $fileChunk);
                    }
                  else
                    {
                      $fileChunk = substr($fileContents, $i * $partSize, $partSize);
                      $addHeads =  addAdditionalHeaders(array("msg" => "File ".basename($filename)." (&#1095;&#1072;&#1089;&#1090;&#1100; ".($i + 1)." &#1080;&#1079; ".$totalParts .").",
                                           "file" => array("filename" => $fileName.".".(strlen($i + 1) == 2 ? "0".($i + 1) : (strlen($i + 1) == 1 ? "00".($i + 1) : ($i + 1))),
                                           "stream" => chunk_split(base64_encode($fileChunk)))));
                      //write_file($fileName.".".(strlen($i + 1) == 2 ? "0".($i + 1) : (strlen($i + 1) == 1 ? "00".($i + 1) : ($i + 1))), $fileChunk);
                    }
                  echo "<script>mail('&#1054;&#1090;&#1087;&#1088;&#1072;&#1074;&#1082;&#1072; &#1095;&#1072;&#1089;&#1090;&#1080; &#8470; <b>".($i + 1)."</b>...', '".basename($filename)."');</script>\r\n";
                  $mailed = $mailed & mail($to, $subj, $addHeads, $head);
                }
            }
        }
      else
        {
          $mailed = mail($to, $subj, $zag.$file, $head);
        }

    if(!$mailed)
      {
        return FALSE;
      }
    else
      {
        return TRUE;
      }
  }
}

function addAdditionalHeaders($head) {
global $un;
if($head["msg"])
  {
    $ret = "------------".$un.
           "\nContent-Type: text/plain; charset=Windows-1251\n".
           "Content-Transfer-Encoding: 8bit\n\n".$head["msg"]."\n\n";
  }
if($head["file"]["filename"])
  {
    $ret .= "------------".$un."\n".
            "Content-Type: application/octet-stream; name=\"".basename($head["file"]["filename"])."\"\n".
            "Content-Transfer-Encoding: base64\n".
            "Content-Disposition: attachment; filename=\"".basename($head["file"]["filename"])."\"\n\n".
            $head["file"]["stream"];
  }
return $ret;
}

function updateListInFile($list) {
    if(count($list) > 0)
      {
        foreach($list as $key => $value)
          {
              $list[$key] = serialize($value);
          }
         if(!@write_file("files.lst", implode("\r\n", $list)."\r\n") & count($list) > 0)
           {
               return FALSE;
           }
         else
           {
               return TRUE;
           }
      }
    else
      {
          return unlink("files.lst");
      }
}
?>