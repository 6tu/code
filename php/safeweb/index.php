<?php
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http";
if($protocol == 'http'){
    header("Location: https://ys138.win/safeweb/");
    exit(0);
}
?>

<!DOCTYPE HTMl PubliC "-//W3C//Dtd HTMl 4.01 transitional//EN" "http://www.w3.org/tr/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>SafeWeb</title>
    <meta name="keywords" content="SafeWeb,CgiProxy,ShadowSocks,在线代理" />
    <meta name="description" content="一个用CGi建立的 WEb在线代理" />
	<meta name="description" content="Safeweb provides the first free, completely private and secure way to surf the Web anywhere, anytime">

<meta name="keywords" content="Anonymity, Anonymous Dialup, Anonymous Email, Anonymous Mailer, Anonymous Web Browsing, Anonymous Web Surfing, Anonymous, Carnivore Killer, Carnivore, Cookie Filter, Cookie Manager, Encryption, Encrypted Dialup, Encrypted Email, Encrypted Mailer, Encrypted Web Surfing, Encrypted Web Browsing, Firewall, Internet Privacy, Internet Security, Online Privacy, Online Security, Personal Firewall, Pop-up Window Manager, Privacy Service, Privacy, Private Bookmarks, Proxy, SSH, Secure Access Proxy, Secure Dialup, Secure Proxy, Secure, Security, Spawn Suppression"> 

    <script class="_proxy_jslib_jslib" type="text/javascript" src="https://ys138.win/cgi-bin/nph-proxy.cgi/zh/20/x-proxy/scripts/jslib"></script>
    <script class="_proxy_jslib_pv" type="text/javascript">_proxy_jslib_pass_vars("","://:", false,true,false,false,false,false,"","","",false,false,false,"", 1, "{}");</script>

</head>

<body onload="document._proxy_jslib_URLform.URL.focus() ; if (document._proxy_jslib_URLform.URL.value.match(/^\x7f/)) document._proxy_jslib_URLform.URL.value= _proxy_jslib_wrap_proxy_decode(document._proxy_jslib_URLform.URL.value.replace(/\x7f/, ''))">
    <center>
        <table border=0>
            <tbody>
                <tr>
                    <td vAlign=center>
                        <table>
						    <tbody>
                                <tr><td><center><img height=108 src="main.jpg" width=430></center></td>
                                    <td><img height=1 src="spacer.gif" width=5></td>
									<td><center><font face=arial,sans-serif size=2><nobr><i>一次点击即可随时随地保护隐私.</i></nobr></font></center></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td><img height=15 src="spacer.gif" width=1></td>
                </tr>
                <tr>
                    <td vAlign=center>
                        <center>
                            <form name="_proxy_jslib_URLform" action="https://ys138.win/cgi-bin/nph-proxy.cgi/zh/20/https/ys138.win/safeweb/welcome.html" method="post" onsubmit="if (!this.URL.value.match(/^\x7f/)) this.URL.value= '\x7f'+_proxy_jslib_wrap_proxy_encode(this.URL.value) ; return true">
                                <input type="hidden" name="convertGET" value="1">
                                <font face=arial,sans-serif size=2>输入您希望安全查看的网站地址:</font><br>
                                <nobr>
                                    <input name=placetogo size=50>
                                    <input type=submit value=Go!>
								</nobr>
                            </form>
                        </center>
                    </td>
                </tr>
                <tr>
                    <td align=middle>
                        <center>
                            <nobr>
                                <form method="post">
                                    <input type="hidden" name="convertGET" value="1">
                                    <font face=arial,sans-serif size=2>将浏览器起始页修改为</font>
                                    <input type=submit value="总是安全开始">
								</form>
                            </nobr>
                        </center>
                    </td>
                </tr>
                <tr>
                    <td>
                        <center>
                            <table border=0>
							    <tbody>
                                    <tr>
                                        <td vAlign=top>
                                            <font face=arial,sans-serif size=2>
											Windows &amp; linux 用户建议用 <a href="http://shadowsocks.org/en/index.html"> Shadowsocks </a>自由访问互联网.
											</font>
										</td>
									</tr>
                                </tbody>
							</table>
                        </center>
                    </td>
                </tr>
                <tr>
                    <td><img height=10 src="spacer.gif" width=1></td>
                </tr>
                <tr>
                    <td>
                        <center>
                            <table cellPadding=0 cellSpacing=0>
                                <tbody>
                                    <tr>
                                        <td><font face=arial,sans-serif size=2><b>SafeWeb 总是:</b></font></td>
                                        <td><font face=arial,sans-serif size=2><b>SafeWeb 让你:</b></font></td>
                                    </tr>
                                    <tr>
                                        <td vAlign=top>
                                            <ul>
                                                <li><font face=arial,sans-serif size=2><a href="https://216.162.97.18/#">加密</a>和保护内容</font>
												<li><font face=arial,sans-serif size=2>清理危险<a href="https://216.162.97.18/#">脚本</a>和<a href="https://216.162.97.18/#">flash</a></font>
                                                <li><font face=arial,sans-serif size=2>屏蔽计算机iP地址</font></li>
                                            </ul>
                                        </td>
                                        <td vAlign=top>
                                            <ul>
                                                <li><font face=arial,sans-serif size=2>阻止第三方分析<a href="https://ys138.win/cgi-bin/nph-proxy.cgi/zh/20/x-proxy/cookies/manage">cookies</a></font>
                                                <li><font face=arial,sans-serif size=2>自主配置<a href="https://216.162.97.18/#">分析HTMl选项</a></font>
                                                <li><font face=arial,sans-serif size=2>禁止<a href="https://216.162.97.18/#">弹出/pop-up</a>窗口</font></li>
                                            </ul>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </center>
                    </td>
                </tr>
                <tr>
                    <td align=middle>管理员:&nbsp; <font size=2>为您的网站添加 SafeWeb <a href="https://216.162.97.18/#">按钮 </a>以支持在线保护隐私.</font></td>
                </tr>
                <tr>
                    <td><img height=10 src="spacer.gif" width=1></td>
                </tr>
                <tr>
                    <td>
                        <center>
                            <a href="https://www.jmarshall.com/tools/cgiproxy/install.html"><font face=arial,sans-serif size=2>如何运作</font></a>|
                            <a href="https://www.jmarshall.com/tools/cgiproxy/faq.html"><font size=2>帮助</font></a>|
                            <a href="https://ys138.win/"><font face=arial,sans-serif size=2>关于我们</font></a>|
                            <a href="https://www.jmarshall.com/tools/cgiproxy/news.html"><font face=arial,sans-serif size=2>新闻发布室</font></a>|
                            <a href="https://www.jmarshall.com/tools/cgiproxy/#legal"><font face=arial,sans-serif size=2>隐私政策</font></a>|
                            <a href="http://chicagovps.net/downloads/TermsofService-ChicagoVPS.pdf"><font face=arial,sans-serif size=2>使用条款</font></a>
                        </center>
                    </td>
                </tr>
                <tr>
                    <td><img height=5 src="spacer.gif" width=1></td>
                </tr>
                <tr>
				    <td align=middle><br><hr><br>
					    <font color=#999999 size=-2>Copyright ©2017 SafeWeb . Power by </font>
                        <a href="mailto:james@jmarshall.com?subject=CGIProxy"><font size=-2>james@jmarshall.com</font></a>
				    </td>
				</tr>
            </tbody>
        </table>
    </center>
</body>

</html>