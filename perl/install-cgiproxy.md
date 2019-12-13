
安装方法  https://www.jmarshall.com/tools/cgiproxy/install.html 

获取源代码 
wget -O nph-proxy.cgi https://www.jmarshall.com/tools/cgiproxy/nph-proxy.txt 

或者 

wget https://www.jmarshall.com/tools/cgiproxy/releases/cgiproxy.latest.tar.gz 
tar xzvf cgiproxy.*.tar.gz 
tar xzvf cgiproxy-inner.*.tar.gz 





# 更新 perl 本地库 
rm -rf /root/.cpan/CPAN/* 
/opt/lampp/bin/cpan 
/opt/lampp/bin/cpan local::lib 

# 默认安装到 /opt/lampp/lib/perl5 

# 安装模块 ./nph-proxy.cgi install-modules 

/opt/lampp/bin/cpan Net::SSLeay 
/opt/lampp/bin/cpan JSON 
/opt/lampp/bin/cpan IO::Compress::Gzip 
/opt/lampp/bin/cpan IO::Compress::Deflate 
/opt/lampp/bin/cpan IO::Compress::Lzma 
/opt/lampp/bin/cpan FCGI 
/opt/lampp/bin/cpan FCGI::ProcManager 

# 安装

# 复制证书文件
mkdir -p /home/cgi-bin/cgiproxy
/bin/cp -rf /opt/lampp/etc/ssl.crt/server.crt /home/cgi-bin/cgiproxy/plain-cert.pem
/bin/cp -rf /opt/lampp/etc/ssl.key/server.key /home/cgi-bin/cgiproxy/plain-key.pem
/bin/cp -rf //home/cgi-bin/nph-proxy.cgi /home/cgi-bin/cgiproxy/nph-proxy.cgi

/home/cgi-bin/nph-proxy.cgi install

# 配置文件及更多内容存放位置 /home/cgi-bin/cgiproxy
# 运行模式

# 0. 一般以 APACHE + CGI 模式运行，放到 cgi-bin 目录下即可

# 1. APACHE + mod_perl

在 httpd.conf 末尾添加

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

# 2. FastCGI
先给APAPCHE添加FastCGI模块 https://fastcgi-archives.github.io/

cgiproxy/bin/nph-proxy.cgi start-fcgi
killall nph-proxy.cgi

# httpd.conf:
FastCgiExternalServer /home/www/secret -host localhost:8002

# 3. embedded server ，自带WEB服务器，须/opt/lampp/lib/perl5 
cgiproxy/bin/nph-proxy.cgi start-server
killall nph-proxy.cgi

# cgiproxy.conf
/home/cgi-bin/cgiproxy/nph-proxy.cgi config



















