<?php
/**
 * 
 * http://blog.csdn.net/clh604/article/details/20224735/
 * http://www.oschina.net/code/snippet_248412_18578
 * 
 * 用 PRIVATE KEY 生成 PUBLI CKEY
 * openssl genrsa -out key.pem 1024
 * openssl rsa -in key.pem -pubout -out pubkey.pem
 * 
 * 从证书中提取 PUBLIC KEY
 * openssl x509 -pubkey -in cert.pem -out pubkey.pem
 *
 * 对应的PHP语法
 * openssl_pkey_get_public(mixed $certificate);
 * 
 * RSA一般有两种应用场景
 * 1. 公钥加密\私钥解密,用于数据安全通信
 * 2. 私钥加密\公钥解密,主要用于数字签名
 *
 * 用 pkcs7 加解密时务必使用绝对路径
 * 
 * 
 */

if (!extension_loaded('openssl')) exit("no openssl extension loaded.");

echo "<pre>\r\n\r\n<center>如果能读懂, 说明加密及解密成功\r\n\r\n";

$string = "This is a read able string.你好";

/************ RSA Method ****************/
$passwd = 'abc123';
$enstr = encrypted($string, $passwd);
$destr = decrypted($enstr, $passwd);
echo $destr . "\r\n\r\n";

/************ RSA KEY ****************/
$key = pw_key()['key'];
$pub = pw_key()['pub'];
$type = 'private';

$enstr = rsa_encrypted($string, $type, $key);
echo rsa_decrypted($enstr, $type, $pub) . "\r\n\r\n";
//echo chunk_split($enstr, 76);

/************ PKCS7 ****************/
pkcs7_encrypt($string) . " => ";
echo pkcs7_decrypt($enc_file = null);


/************ 函数区 ****************/

function encrypted($string, $passwd){
    $iv = "1234567812345678";
    $options = '0';
    $method = 'aes-256-cfb';
    $enc_string = openssl_encrypt($string, $method, $passwd, $options, $iv);
    return $enc_string;
}

function decrypted($enc_string, $passwd){
    $iv = "1234567812345678";
    $options = '0';
    $method = 'aes-256-cfb';
    $dec_string = openssl_decrypt($enc_string, $method, $passwd, $options, $iv);
    return $dec_string;
}

# 若用 $key 加密,则用 $pubkey 解密,两者可以互换
function rsa_encrypted($src, $type = 'private', $key){
    # 对明文进行分片段(块)加密
	# ceil()进一法取整, 对余数$rem 用空格补充为整数后多加两个空格
	
    $chunks = 64;
    $max = ceil(strlen($src) / $chunks);
    $rem = $chunks - strlen($src) % $chunks;
    $src = str_pad($src, strlen($src) + $rem);
	$src .= '  ';
    $output = '';
    while($src){
        $input = substr($src, 0, $max);
        $src = substr($src, $max);
        if($type == 'private'){
            $ok = openssl_private_encrypt($input, $enc_string, $key);
        }else{
            $ok = openssl_public_encrypt($input, $enc_string, $key);
        }
        $output .= $enc_string;
    }
    return base64_encode($output);
}

function rsa_decrypted($enc_string, $type = 'private', $pubkey){
    # 循环拆分为128字节片段进行解密
	
    $src = base64_decode($enc_string);
    $max = 128;
    $output = '';
    while($src){
        $input = substr($src, 0, $max);
        $src = substr($src, $max);
        if($type == 'private'){
            $ok = openssl_public_decrypt($input, $out, $pubkey);
        }else{
            $ok = openssl_private_decrypt($input, $out, $pubkey);
        }
        $output .= $out;
    }
    return trim($output);
}

function pkcs7_encrypt($string){
    $cert = pw_key()['cert'];
	$src_file = file_path()['src'];
	$enc_file = file_path()['enc'];	
	file_put_contents($src_file, $string);
	
    $headers = array(
					"To" => "info@yourshell.info", 
					"From" => "webmaster<postmaster@yourshell.info>", 
					"Reply-to" => "support@example.com", 
					"Subject" => "Test", 
					"Date" => date("r"), 
					"X-Mailer" => "Bynews(PHP/" . phpversion() . ")"
					);
    openssl_pkcs7_encrypt($src_file, $enc_file, $cert, NULL); # $headers替换null
	$enc = file_get_contents($enc_file);
    unlink($src_file);
    return $enc;

}

function pkcs7_decrypt($enc_file){

    $cert = pw_key()['cert'];
    $key = pw_key()['key'];
	$passwd = pw_key()['pw'];
	if(empty($enc_file)) $enc_file = file_path()['enc'];
	$dec_file = file_path()['dec'];	

    openssl_pkcs7_decrypt($enc_file, $dec_file, $cert, array($key, $passwd));
	$dec = file_get_contents($dec_file);
	unlink($dec_file);
    return $dec;

}

# pkcs7 加解密方法中需要的文件
function file_path(){
	$path = getcwd();
	$src_file = $path . 'msg.inc';
	$enc_file = $path . 'msg.inc_smime.p7m';
    $dec_file = $path . 'msg.inc.dec';
	return array('src' => $src_file, 'enc' => $enc_file, 'dec' => $dec_file);
}

# 私钥\私钥密码\证书
function pw_key(){
	
	$passwd = 'mm123456';
	
    $private_key = '
-----BEGIN RSA PRIVATE KEY-----
MIICXgIBAAKBgQC1z3t8kIIjLYoYlIma0qPJ8sFJZXRdwUo6qATQilkXuUrQEttR
J/EgzDIn8L7IcfUjxHYAEJPyckEaKosGlNvo3FjJ+XEtVFCjLFqN0FrE7kpq+6FA
5bFXLMuqB5i8FOzVPMnVIr+6n/WGeE+rRGIUTUuNcELJRT9SBbjtsXPQtQIDAQAB
AoGBAJS3q2MxMcJktdl1Zznlo2TF1aWb/5vRSE7CsW2EPWxSfQfG5O91pKAXZ8+T
9fswfD1NrthOtzZSjz5AHoi7q0VkyKg+aHZyj0w5CQpSmJLkMJpveSQV/8ehiSWz
Z9e7chq+1hSg5A2ICjxNz29F29e8uDOSny8B5a9ZzdwmB4nhAkEA7H/Q4mDAoVDj
jlAmMDnun69iV2Q19+ERtW7aGupvksTwZ/mABIfhhZIZm36kq5Q4b0TKyTiMgTZv
uhyeu8epewJBAMTNQkD4QchTw2XbUoUa0CIyXQg8y+XPhMNEyTeb8kGyu66RgoIn
lLzn+EQZqgFQOEc9C7jKlT1E+MLk+xW+348CQQDdCFxupySB4Dq9MFVwr0RBREZy
DPuPj2/glRkNHNxoXN2fH4WxNlnlX3XFaSh4H9Ba1f188PgIb5seY09Li0DvAkEA
msdv/xcA7aPrPnWS3fprjSmc/3iJSDHAka7Mrj6o9kCy2SW5xdGJalTqbezdRvEn
geeiC3DQlQJkvytFyiF3QwJAVidp6GnuoFqUScSXzB2xXV5R+9T0hPlqiSJAiXPN
20epQSumQ3NRork2e2FH6rXK+5DEbHacQeoXouFViWYr3g==
-----END RSA PRIVATE KEY-----';

    $certificate = '
-----BEGIN CERTIFICATE-----
MIIENDCCA52gAwIBAgICAUMwDQYJKoZIhvcNAQEFBQAwgdUxNDAyBgNVBAMTK1Jh
bmdlcnMgUGVyc29uYWwgRnJlZSBDZXJ0aWZpY2F0ZSBBdXRob3JpdHkxGjAYBgkq
hkiG9w0BCQEWC2NlcnRAUlBGLkNBMSAwHgYDVQQKExdSYW5nZXJzIE5ldHdvcmtz
IENvLkx0ZDEXMBUGA1UECxMOUEhQIExhYm9yYXRvcnkxETAPBgNVBAcTCFlpbmNo
dWFuMSYwJAYDVQQIEx1OaW5neGlhIEh1aSBBdXRvbm9tb3VzIFJlZ2lvbjELMAkG
A1UEBhMCQ04wHhcNMTAwNzAzMDQwOTMyWhcNMTEwNzAzMDQwOTMyWjCBrTEiMCAG
CSqGSIb3DQEJARYTaW5mb0B5b3Vyc2hlbGwuaW5mbzENMAsGA1UEAxMEd2FsazEL
MAkGA1UEBhMCQ04xJjAkBgNVBAgTHU5pbmd4aWEgSHVpIEF1dG9ub21vdXMgUmVn
aW9uMREwDwYDVQQHEwhZaW5jaHVhbjEgMB4GA1UEChMXUmFuZ2VycyBOZXR3b3Jr
cyBDby5MdGQxDjAMBgNVBAsTBVN0YWZmMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCB
iQKBgQC1z3t8kIIjLYoYlIma0qPJ8sFJZXRdwUo6qATQilkXuUrQEttRJ/EgzDIn
8L7IcfUjxHYAEJPyckEaKosGlNvo3FjJ+XEtVFCjLFqN0FrE7kpq+6FA5bFXLMuq
B5i8FOzVPMnVIr+6n/WGeE+rRGIUTUuNcELJRT9SBbjtsXPQtQIDAQABo4IBNzCC
ATMwHQYDVR0OBBYEFAQI/LaNrga160Q+aNeMeLgDR6oyMIIBAgYDVR0jBIH6MIH3
gBQgFsVoaeBCqmN69Y61GkI4MgbgdqGB26SB2DCB1TE0MDIGA1UEAxMrUmFuZ2Vy
cyBQZXJzb25hbCBGcmVlIENlcnRpZmljYXRlIEF1dGhvcml0eTEaMBgGCSqGSIb3
DQEJARYLY2VydEBSUEYuQ0ExIDAeBgNVBAoTF1JhbmdlcnMgTmV0d29ya3MgQ28u
THRkMRcwFQYDVQQLEw5QSFAgTGFib3JhdG9yeTERMA8GA1UEBxMIWWluY2h1YW4x
JjAkBgNVBAgTHU5pbmd4aWEgSHVpIEF1dG9ub21vdXMgUmVnaW9uMQswCQYDVQQG
EwJDToIBATAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBBQUAA4GBAKMKq9zCJ2Qx
vJ0KAe2yogzFJLCy3GCK0URcHBedGi3Emfwngm7LQoHF/PQg8BmJ3entJDMvYPAs
Rwin+2biKiz/kcrGIsOs2rfgl1ubxZi/fFf+aNbJvhDKKBvXozUZMQQp7+kPbC8u
x7W+ZnmjO8yJXTdYfwmQxv2SOulDZtoe
-----END CERTIFICATE-----';

	# 转换为openssl可用格式
    $resource_cert = openssl_pkey_get_public($certificate);
	$public_key = openssl_pkey_get_details($resource_cert)['key'];
    $pi_key = openssl_pkey_get_private(str_replace(array("\t",), array(""), $private_key));
    $pu_key = openssl_pkey_get_public(str_replace(array("\t",), array(""), $public_key));
	
    return array('cert' => $certificate, 'key' => $pi_key, 'pub' => $pu_key, 'pw' => $passwd);
}





