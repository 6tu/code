========================================================================

CGIProxy 2.2.2  (2017年7月16日发布)

基于CGI脚本中的 HTTP/FTP 代理

(c) 1996, 1998-2017 版权归 James Marshall, james@jmarshall.com 所有.
免费用于非商业用途; 商业用途需要许可证.

最新的版本发布,请浏览 http://www.jmarshall.com/tools/cgiproxy/

========================================================================

本自述文件包含：

   1.先决条件
   2.安装
   3.在您安装之后
   4.故障排除
   5.限制和错误
   6.配置选项

------------------------------------------------------------------------

1.先决条件：

在使用CGIProxy时，是检测安全性的好时机
http://www.jmarshall.com/tools/cgiproxy/security.html

CGIProxy可以运行四种方式：作为CGI脚本，作为mod_perl脚本，作为FastCGI脚本
或使用其自己的嵌入式服务器。作为CGI脚本是运行最慢的，并且作为mod_perl或
FastCGI脚本运行速度最快。取决于您选择哪种方式，您的Web服务器需要配置为支
持它，详见以下章节。

CGIProxy需要Perl和OpenSSL，但几乎所有Unix服务器已经安装了。如果您使用的
是Windows服务器，请安装这两者。

如果您正在安装CENTOS服务器：
CentOS默认不包含所有标准的Perl模块，所以请先以root身份运行"yum install epel-release"
安装它们。你没有先做这件事可能不能运行安装向导。另外，CGIProxy所需的CPAN
模块必须以root身份安装在CentOS上，所以如果是安装向导不以root身份运行，您
需要与向导分开安装这些模块。该向导会告诉你如何做到这一点。

如果您正在安装WINDOWS服务器：
运行nph-proxy.cgi的下面的命令用于Unix，但几乎相同用于Windows命令行。在Windows
中，进行两项更改：启动一个"perl"命令，并用"\"替换"/"。您也可以删除一个初
始的"./"。例如，不是运行"./nph-proxy.cgi命令"，而是运行"perl nph-proxy.cgi命令"。


作为CGI脚本：

您的Web服务器必须配置为支持CGI脚本，以便任何以".cgi"结尾的可执行文件被视
为CGI脚本。您的网站管理员通常这样做。对于Apache，你需要在httpd.conf控制
nph-proxy.cgi所在的目录：

    AddHandler cgi-script .cgi
    Options +ExecCGI

Apache的所有细节都在 https://httpd.apache.org/docs/2.4/howto/cgi.html


作为MOD_PERL SCRIPT：

mod_perl是Apache服务器的可选功能。用mod_perl，CGIProxy可以比CGI脚本运行快
得多。您的网站管理员通常会配置mod_perl的。如果你正在配置你自己的mod_perl，
你可以在httpd.conf中如下配置，使所有以".pl"结尾的脚本都使用mod_perl：

<Files *.pl>
    Options +ExecCGI
    SetHandler perl-script
    PerlResponseHandler ModPerl::Registry
    PerlOptions +ParseHeaders
    PerlSendHeader Off
    <Files nph-*>
        PerlOptions -ParseHeaders
    </Files>
</Files>

如果你知道如何配置Apache，你可以根据需要调整它。如果你使用上面的块，然后
将nph-proxy.cgi重命名为nph-proxy.pl。


作为FASTCGI脚本：

FastCGI脚本在某些方面类似于CGI脚本，但运行速度比CGI脚本快。FastCGI可以用
于大多数Web服务器，包括那些不支持CGI脚本的，例如nginx。FastCGI脚本以多个
方式托管运行进程在服务器上，并由Web服务器进程调用。

安装CGIProxy后，将您的Web服务器配置为作为FastCGI脚本使用CGIProxy，使用
$SECRET_PATH 和 $FCGI_SOCKET 的设置; 您可以在配置菜单或文件cgiproxy.conf
中查看这些设置。如何配置nginx和Apache服务器的例子如下。

要启动FastCGI进程，请运行命令"cgiproxy/bin/nph-proxy.cgi start-fcgi"。

要在Unix中停止FastCGI进程，请运行命令"killall nph-proxy.cgi"。


用nginx配置FastCGI：

在安全服务器中的nginx.conf中包含这样的一个部分：

location /secret/ {
    fastcgi_pass             localhost:8002;
    fastcgi_split_path_info  ^(/secret)(/?.*)$;
    fastcgi_param            SCRIPT_NAME $fastcgi_script_name;
    fastcgi_param            PATH_INFO $fastcgi_path_info;
    include                  fastcgi.conf;      # if not included elsewhere
}

前三行需要匹配您在nph-proxy.cgi中的配置：
用你设置的$SECRET_PATH替换"secret"(在两个地方！)
用你设置的$FCGI_SOCKET替换"8002"，如果你改变它的默认的话。


使用Apache配置FastCGI：

在你的httpd.conf中包含这样的一行：

FastCgiExternalServer /var/www/html/secret -host localhost:8002

用你在DocumentRoot设置的$SECRET_PATH替换"/var/www/html/secret"
用你设置的$FCGI_SOCKET替换"8002"，如果你改变它的默认的话。


使用CGIPROXY的嵌入式服务器：

CGIProxy包含一个嵌入式Web服务器，因此您不需要外部Web服务器来运行它。但是，
您需要一个密钥对。(所有安全服务器都需要这个，但外部服务器通常已经被托管
服务提供商安装了一个密钥)。密钥对由一个证书(公众可见)和一个私钥(你必须保密)。
你可以从证书颁发机构(CA)的服务器得到一个密钥对。大多数认证机构收费，但
Let's Encrypt (https://letsencrypt.org)免费提供。他们还提供了Certbot工具
(https://certbot.eff.org/)来自动管理你的证书。安装CGIProxy后，您需要复制
证书和私钥文件放到主目录下的"cgiproxy"目录中。

安装CGIProxy后，通过运行命令启动嵌入式服务器"cgiproxy/bin/nph-proxy.cgi start-server"。
之后，它会告诉你访问代理的URL(包括实际的端口号)以及正在运行的服务器的进程ID。

要在Unix中停止嵌入式服务器，请运行命令"killall nph-proxy.cgi"。


2.安装CGIPROXY：

一旦满足先决条件，然后安装CGIProxy：

    1) 将分发文件(以.tar.gz结尾的文件)上传到您的Web服务器。
    2) 登录到服务器上的shell帐户。
    3) 解压分发(在Unix上，运行命令"tar xzvf cgiproxy.*.tar.gz")。它有两个
        文件，nph-proxy.cgi和README文件。nph-proxy.cgi是您将要安装并运行
        的程序。就是这样——只有一个文件。
    4) 如果你想重命名nph-proxy.cgi(见下面)，现在就做。
    5) 运行命令"./nph-proxy.cgi install"。这几乎做到了需要安装CGIProxy的
        一切。它运行一个简单的安装向导问你几个问题。理想情况下，你可以以
        root身份运行它，但它即使您没有root访问权限也应该可以工作。
    6) 如果使用嵌入式服务器：复制密钥对的两个文件(在上面的"先决条件"中描述)
        到你的主目录下的"cgiproxy"目录。文件名应该与变量$CERTIFICATE_FILE 
        和$PRIVATE_KEY_FILE匹配，它们是"plain-cert.pem"和"plain-key.pem"，
        除非你改变它们。
    7) 如果在Windows上安装：在任务计划程序中添加每日或每小时任务以清除数
        据库，使用该命令"\path\to\script\nph-proxy.cgi"(用正确的路径替换)
        和参数"purge-db"。

作为向导的一部分，您将看到一个简单的菜单，可以让您修改任何CGIProxy中的配
置变量。(你可能不需要改变任何东西)。完成后，输入"0"保存设置并退出配置菜单。
安装CGIProxy后，您可以随时运行再次运行"./nph-proxy.cgi config"配置菜单。

配置完成后，向导将安装CGIProxy需要的所有Perl CPAN模块。这可能会产生大量的
滚动文字。如果它询问你任何问题，你可以按<enter>键。只安装这些模块，而不是
做任何其他安装任务，运行"./nph-proxy.cgi install-modules"。

如果你愿意，你可以重命名nph-proxy.cgi。但是，如果您将它安装为CGI脚本或mod_perl下，
确保名称仍然以"nph-"开头。(您可以通过重新配置mod_perl来改变mod_perl的需求。)


3.在您安装之后：

在进一步做任何事之前，请仔细阅读安全指南
http://www.jmarshall.com/tools/cgiproxy/security.html

如果你希望你的代理可以被其他人使用，你需要他们的代理网址。尝试使用安全的
方法来做到这一点，否则代理很容易被检查员发现并被封锁，或者更糟。

特别是，不要试图将您的代理网址发布到任何公共网站列表可用的代理。如果你这
样做，检查员会很快看到你的代理和阻止它，或者可能会有更严重的后果。这些委
托代理网站可能对匿名有用，但对于进行全面检查他们是危险的！他们使审查员的
工作更容易。

此外，人们只应使用他们信任的组织人员安装的代理程序。

如果大量使用此代理会给您的服务器带来太多负载，请参阅"性能注意事项"在源代
码的顶部附近，最大的改进来了从mod_perl或FastCGI下运行。


4.故障排除：

"错误的请求"错误

当您为Web服务器处理太多Cookie时会发生这种情况。
要修复它，请使用带有CGIProxy的数据库。最简单的数据库使用SQLite，使用它只
需将$DB_DRIVER设置为"SQLite"即可。使用正在运行的数据库引擎如MariaDB/MySQL
或Oracle，您还必须创建一个数据库帐户供CGIProxy使用，并设置$DB_USER和$DB_PASS。

重要提示：无论何时使用数据库，出于性能和安全原因需要定期清除累积的旧数据。
清除数据库，定期运行"./nph-proxy.cgi purge-db"。在Unix服务器上，请执行一
个cron作业。 CGIProxy安装向导通常为你添加了这个cron作业，但是如果没有，那
么你可以自己做：运行"crontab -e"编辑你的cron作业列表，然后添加一行这样的行
来清除数据库，每天早上在例如上午2点13分：

    13 2 * * * /path/to/script/nph-proxy.cgi purge-db

将"/path/to/script/"替换为nph-proxy.cgi的完整路径。如果你愿意每隔一小时清
除一次数据库，将上面的"2"更改为"*"。

如果你以root身份运行"./nph-proxy.cgi install"，运行"crontab -e -u $RUN_AS_USER"，
其中$RUN_AS_USER是来自CGIProxy配置的用户名。这个修改属于$RUN_AS_USER的cron作业列表。

在Windows服务器上，使用任务计划程序执行此操作而不是cron作业。


5.限制和错误：

匿名可能不完美！特别是，其中可能会有一些漏洞，未加密的JavaScript或Flash内
容可能会漏掉。如果你发现了请告诉我。为了获得最佳的匿名性，请关闭浏览器
JavaScript和Flash（最佳），或配置CGIProxy以删除脚本。

理想情况下，使用数据库来存储cookie。如果你不能存储cookie，那么如果你浏览
使用Cookie的网站，CGIProxy可能会导致一些网站无法工作。如果发生这种情况，
请删除部分或全部现有Cookie（通过"管理Cookies"屏幕）并重试。

我没有遵循HTTP代理的规范，并且存在违规行为协议。实际上，这整个概念是对代
理模式的违反，所以我并不担心。如果任何协议违规导致您的问题，请告诉我。

目前只支持HTTP / HTTPS和FTP。


6.配置选项：

这是CGIProxy中所有配置选项的列表，分类为粗略类别。 默认设置在方括号[]内，
并且几乎适用于所有情况，但请参阅"与您的服务器/网络环境相关选项"一节。另
请参见"FASTCGI 配置选项"，"嵌入式服务器配置选项"和"数据库配置选项"(如果需要的话)。
有关任何选项的更多信息，请参阅源代码位于用户配置部分中设置它的注释。

安装后配置CGIProxy，运行"./nph-proxy.cgi config"。


与您的服务器/网络环境相关的选项：
----------------------------------------------------

$PROXY_DIR ["cgiproxy"]
    The directory on the server where the program can place files.  A relative
    path will be resolved relative to the home directory of the script owner.

$RUN_AS_USER, $RUN_AS_GROUP [none]
    User ID and group to run as, in case you need to start the embedded server
    as root.  Also used to indicate Web server's user ID and group when running
    as a CGI or mod_perl script.

$SECRET_PATH [randomly-generated]
    If using FastCGI or the embedded server, set this to an alphanumeric
    sequence of characters that is hard to guess.  It will be part of the
    URL of your proxy.

$LOCAL_LIB_DIR ['perl5']
    The directory used by the local::lib module to install Perl (CPAN) modules.
    Only needed if local::lib is used, i.e. if installing Perl modules not as
    root.

To enable access to secure servers:
    Install the separate packages OpenSSL and Net::SSLeay.  Net::SSLeay is
    automatically installed by running "./nph-proxy.cgi install-modules".

To enable compressed (gzip'd) content:
    If you're not using Perl 5.9.4 or later, then install the IO::Compress::Gzip
    Perl module.  It is automatically installed by running "./nph-proxy.cgi install-modules".

$RUNNING_ON_SSL_SERVER ['']
    Set this if the script is running on an SSL server (i.e. accessed with
    an "https://" URL).  Or, the default value of '' means to guess based on
    the server port:  the script assumes SSL unless the server port is 80.

$NOT_RUNNING_AS_NPH [0]
    Set this if the script is not running as an NPH script (not recommended;
    see comments for possible dangers).

$HTTP_PROXY, $SSL_PROXY, $NO_PROXY [none]
    If this script has to use an HTTP proxy (like a firewall), then set
    $HTTP_PROXY to that proxy's host (and port if needed).  Set $SSL_PROXY
    similarly when using an SSL proxy.  $NO_PROXY is a comma-separated list
    of servers or domains that should be accessed directly, i.e. NOT through
    the proxies in $HTTP_PROXY and $SSL_PROXY.  Also see $USE_PASSIVE_FTP_MODE
    below when using a firewall.

$PROXY_AUTH, $SSL_PROXY_AUTH [none]
    If either or both of the proxies in $HTTP_PROXY and $SSL_PROXY require
    authentication, then set these two variables respectively to the required
    credentials.

$SOCKS_PROXY [none]
    To use a SOCKS 5 proxy (like Tor), set this to the SOCKS 5 proxy's host and
    port, or just port number to default to localhost.  It's strongly
    recommended to only use a SOCKS 5 proxy on the same server as CGIProxy, as
    all data is passed in the clear between them.  As of version 2.2.2, you no
    longer need to change @BANNED_NETWORKS to use a SOCKS proxy on localhost.

$SOCKS_USERNAME, $SOCKS_PASSWORD [none]
    If your SOCKS 5 proxy uses username/password authentication, set these.

$USER_FACING_PORT [none]
    If the users see a different port externally than CGIProxy sees internally,
    set this to the port the users see.


FASTCGI 配置选项:
------------------------------

$FCGI_SOCKET [8002]
    The local port to listen on for FastCGI communication.

$FCGI_NUM_PROCESSES [100]
    Number of FastCGI processes to maintain (same as "-n" command-line parameter).

$FCGI_MAX_REQUESTS_PER_PROCESS [1000]
    How many HTTP requests each FastCGI process should handle before being
    restarted (same as "-m" command-line parameter).


嵌入式服务器配置选项:
--------------------------------------

$CERTIFICATE_FILE ['plain-cert.pem'], $PRIVATE_KEY_FILE ['plain-key.pem']
    Filenames of your certificate (public key) and private key, in PEM format
    and in $PROXY_DIR.

$EMB_USERNAME, $EMB_PASSWORD [none]
    Optional username and password to protect your proxy with.


数据库配置选项:
-------------------------------

$DB_DRIVER ['SQLite']
    If using a database, set this to "SQLite", "MySQL", or "Oracle".
    Leave it commented out if not using a database.

$DB_SERVER [none]
    If your database isn't running on the same server as CGIProxy, or isn't on
    the default port, then set this to the database server and port in the form
    "db_host:db_port".

$DB_NAME ['cgiproxy']
    Name of the database the program can use.

$DB_USER, $DB_PASS [none]
    CGIProxy accesses the database with this username and password.

$USE_DB_FOR_COOKIES [1]
    Set this to true to use the server-side database to store cookies.  If false,
    will use the old method of storing cookies in the browser and sending all
    cookies with each request, which may result in "Bad Request" errors.


常用配置选项:
---------------------------

$TEXT_ONLY [0]
    Allow only text resources through the proxy, to save bandwidth.

$REMOVE_COOKIES [0]
    Ban all cookies to or from all servers.  To allow and ban cookies by
    specific servers, see @ALLOWED_COOKIE_SERVERS and @BANNED_COOKIE_SERVERS.

$REMOVE_SCRIPTS [0]
    Prevent any script content from any server from reaching the browser.
    This includes script statements within HTML pages, external script
    files, etc.  To allow and ban script content by specific servers,
    see @ALLOWED_SCRIPT_SERVERS and @BANNED_SCRIPT_SERVERS.  Anonymity is
    unreliable if you don't either remove scripts, or browse with scripts 
    turned off in your browser.

$FILTER_ADS [0]
    Remove ads from pages, based on the patterns in @BANNED_IMAGE_URL_PATTERNS.
    Also ban ad-related cookies by setting $NO_COOKIE_WITH_IMAGE.

$HIDE_REFERER [0]
    Don't tell servers which link you followed to get to their page.  (Yes,
    it's misspelled on purpose.)

$INSERT_ENTRY_FORM [1]
    At the top of every page, include a small form that lets you enter
    a new URL, change your options, or manage your cookies.

$ALLOW_USER_CONFIG [1]
    Let users set their own $REMOVE_COOKIES, $REMOVE_SCRIPTS, $FILTER_ADS,
    $HIDE_REFERER, and $INSERT_ENTRY_FORM, via checkboxes on the entry form.

sub proxy_encode {}, proxy_decode {}
    (Requires minor programming.)  You can customize the encoding of
    destination URLs by modifying these routines.  The default is a simple
    unobscured URL, but sample obscuring code is included in the comments.
    Note: If you're not removing scripts, then you also need to change
    _proxy_jslib_proxy_encode() and _proxy_jslib_proxy_decode()-- see the
    comments.

sub cookie_encode {}, cookie_decode {}
    (Requires minor programming.)  You can customize the encoding of cookies
    sent to the user's machine by modifying these routines.  The default is a
    simple unobscured cookie, but sample obscuring code is included in the
    comments.
    Note: If you're not removing scripts, then you also need to change
    _proxy_jslib_cookie_encode() and _proxy_jslib_cookie_decode()-- see the
    comments.

@ALLOWED_SERVERS, @BANNED_SERVERS  [empty]
    Allow or ban specific servers from being accessed through the proxy, based
    on their hostname.  Each array is a list of patterns (regular expressions)
    to match, not just single servers.

@BANNED_NETWORKS  [('127', '192.168', '172.16-31', '10', '169.254', '244.0.0')]
    Ban specific IP addresses or networks from being accessed through the
    proxy.  Recommended for security when this script is run on a firewall.
    As of version 2.2.2, a SOCKS proxy is allowed on localhost regardless of
    this setting.

@ALLOWED_COOKIE_SERVERS, @BANNED_COOKIE_SERVERS  [empty]
    Allow or ban cookies from specific servers.  Each array is a list of
    patterns (regular expressions) to match, not just single servers.

@ALLOWED_SCRIPT_SERVERS, @BANNED_SCRIPT_SERVERS  [empty]
    Allow or ban script content from specific servers.  Each array is a list
    of patterns (regular expressions) to match, not just single servers.

@BANNED_IMAGE_URL_PATTERNS  [sample list in source code]
    If $FILTER_ADS is set, then ban images that match any pattern in this list.

$RETURN_EMPTY_GIF [0]
    If an image is banned, then replace it with a 1x1 transparent GIF to
    show blank space instead of a broken image icon.

$NO_COOKIE_WITH_IMAGE [0]
    Ban all cookies that come with images or other non-text resources.  Those
    are usually just Web bugs, to track you for marketing purposes.

$QUIETLY_EXIT_PROXY_SESSION [0]
    (NOT for use with anonymous browsing!!!)  For VPN-like installations, let
    the user browse directly from proxied pages to unproxied pages, with no
    intermediate warning screens.  See the comments for more info.

$PROXIFY_SCRIPTS [1]
    Proxify all supported script content.  Currently, only JavaScript is
    supported.

$PROXIFY_SWF [1]
    Support Flash apps, i.e. reroute all network accesses in them back through
    this program.

$ENCODE_URL_INPUT [1]
    When submitting a URL through either the start form or the top form,
    encode it first by using proxy_encode().

$USER_IP_ADDRESS_TEST ['']
    This lets you call an external test to authorize the user.  See comments
    for more details.

$DESTINATION_SERVER_TEST ['']
    This lets you call an external test to determine if the destination
    server is allowed (as opposed to using @ALLOWED_SERVERS and
    @BANNED_SERVERS).  See comments for more details.

%REDIRECTS [none]
    This lets you automatically redirect a URL to another URL, perhaps to one
    that works better through CGIProxy, such as a mobile site.


在每页中插入一个标准的HEADER:
-------------------------------------------

$INSERT_HTML  [none]
    Insert your own block of HTML into the top of every page.

$INSERT_FILE  [none]
    Insert the contents of the named file into the top of every page.  Can't
    be used with $INSERT_HTML.

$ANONYMIZE_INSERTION [1]
    If $INSERT_HTML or $INSERT_FILE is used, then anonymize that HTML along
    with the rest of the page.

$FORM_AFTER_INSERTION [0]
    If $INSERT_HTML or $INSERT_FILE is used, and $INSERT_ENTRY_FORM is set,
    then put the URL entry form after the inserted HTML instead of before it.

$INSERTION_FRAME_HEIGHT [80 or 50, depending on $ALLOW_USER_CONFIG]
    On pages with frames, make the top frame containing any insertions this
    many pixels high.


次要或自我选择的选项:
-----------------------------

@PROXY_GROUP  [empty]
    This is an experimental feature which may help with load balancing, or
    may have other creative uses.  Cookies won't work if you use this.
    See the comments for further info.

$SESSION_COOKIES_ONLY [0]
    Force all cookies to expire when the current browser closes.

$MINIMIZE_CACHING [0]
    Try to prevent the user's browser from caching, i.e. from storing anything
    locally.  Better privacy, but consumes more bandwidth and seems slower.

$USER_AGENT  [none]
    Tell servers you're using this browser instead of what you're really using.

@TRANSMIT_HTML_IN_PARTS_URLS  [empty]
    Transmit each part of certain HTML pages back to the user as they are
    received, rather than wait for the whole page.  This is set to a list of
    patterns that match URLs for which you want this treatment.

$USE_PASSIVE_FTP_MODE [1]
    When doing FTP transfers, use "passive mode" instead of "non-passive mode".
    Passive mode tends to work better when this script runs behind a firewall,
    but that varies by network.

$SHOW_FTP_WELCOME [1]
    When showing FTP directories, always display the FTP welcome message,
    instead of never displaying it.

$PROXIFY_COMMENTS [0]
    Proxify the inside of HTML comments as if it's not inside comments.

$USE_POST_ON_START [1]
    Use POST instead of GET when submitting the URL entry form.

$REMOVE_TITLES [0]
    Remove titles from HTML pages.

$NO_BROWSE_THROUGH_SELF [0]
    Prevent the script from calling itself.

$NO_LINK_TO_START [0]
    Don't link to the start page from error pages.

$MAX_REQUEST_SIZE [16777216 = 16 Meg]
    (Obscure.) The largest request that can be handled in certain rare
    situations involving password-protected sites.

$ALLOW_UNPROXIFIED_SCRIPTS [0]
    Allow scripts of unsupported type to pass through the proxy.

$COOKIE_PATH_FOLLOWS_SPEC [0]
    When handling cookies with no path, treat it according to the cookie spec.
    If not set, behave as browsers (erroneously) do, i.e. set the path to "/".

$RESPECT_THREE_DOT_RULE [0]
    Restrict cookie domains as they should be, based on how many dots they
    have.  If not set, behave as browsers (erroneously) do, i.e. loosen
    restrictions on cookie domains with two dots.

$ALERT_ON_CSP_VIOLATION [0]
    When there is a Content Security Policy (CSP) violation, show a message
    to the user.  When not set, just show such error messages in the JavaScript
    console.  This is usually just used for testing.

%TIMEOUT_MULTIPLIER_BY_HOST [ "www.facebook.com" => 10 ]
    Multiply timeouts in JavaScript by this much, for the listed servers.
    This can be used to reduce crashes and improve performance on certain
    JavaScript-heavy sites.

$ALLOW_RTMP_PROXY [0]
    (Currently unused.)  Allow the creation of an RTMP proxy process.

========================================================================

最后修改时间：2017年7月16日
http://www.jmarshall.com/tools/cgiproxy/

