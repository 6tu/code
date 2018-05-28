<?php
header('Content-Type: text/html; charset=utf-8');
$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 4); 
if (preg_match("/zh/i", $lang)){
	# include('domain_cn.html');
	$domain_cn =
<<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 <head><meta charset="utf-8">
  <title>由于注册人验证失败，此域名被暂停</title>
  <meta name="author" content="Registrar Administrator" />
  <style type="text/css">
  body {background-color:#eee;}
  h1 {margin: 10% 20% 0 20%; padding: 5px 0 5px 0; font-size:2.5em; font-family:Times; text-align:center; color:#fff; background-color:#d33; border-radius:15px 15px 0 0;}
  p {padding: 0 20px 0 20px; color:#333; font-size:1.2em; font-family:Times, Serif; text-align:justify; line-height:1.2em;} 
  .container {margin: 0 20% 0 20%; background-color:#fff; border: 2px solid #fff; border-radius:0 0 15px 15px;}
  .note {font-size:1.2em; border-top: 2px solid #ccc; margin: 0 15px 10px 15px; padding: 5px 5px 0 5px;}
  .line {color:#555;}
  </style>
</head>

<body>
<h1>此域名被暂停</h1>
<div class="container">
<p>您输入的域名不可用。由于域名持有者（注册人）的电子邮件地址尚未得到验证，因此它已被删除。</p>
<p>如果您是该域名的注册人，请联系您的域名注册服务提供商以完成验证并激活域名。</p>
<p class="note"><b>注意：</b> 域名验证后可能需要长达48小时才能重新开始解析其网站。</p></div>
</body>
</html>
EOF;
    echo $domain_cn;
}
else{
	# include('domain_en.html');
	$domain_en =
<<<EOF
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 <head><meta charset="utf-8">
  <title>Domain name suspended due to Registrant verification failure</title>
  <meta name="author" content="Registrar Administrator" />
  <style type="text/css">
  body {background-color:#eee;}
  h1 {margin: 10% 20% 0 20%; padding: 5px 0 5px 0; font-size:2.5em; font-family:Times; text-align:center; color:#fff; background-color:#d33; border-radius:15px 15px 0 0;}
  p {padding: 0 20px 0 20px; color:#333; font-size:1.2em; font-family:Times, Serif; text-align:justify; line-height:1.2em;} 
  .container {margin: 0 20% 0 20%; background-color:#fff; border: 2px solid #fff; border-radius:0 0 15px 15px;}
  .note {font-size:1.2em; border-top: 2px solid #ccc; margin: 0 15px 10px 15px; padding: 5px 5px 0 5px;}
  .line {color:#555;}
  </style>
</head>

<body>
<h1>This Domain Name is Suspended</h1>
<div class="container">
<p>The domain name you have entered is not available. It has been taken down because the email address of the domain holder (Registrant) has not been verified.</p><p>If you are the Registrant of this domain name, please contact your domain registration service provider to complete the verification and activate the domain name.</p>
<p class="note"><b>NOTE:</b> It may take upto 48 hours after verification for the domain name to start resolving to its website again.</p></div>
</body>
</html>
EOF;
    echo $domain_en;
}

$ip = $_SERVER['REMOTE_ADDR'] . "\r\n";
file_put_contents('allow.txt', $ip, FILE_APPEND | LOCK_EX);
?>