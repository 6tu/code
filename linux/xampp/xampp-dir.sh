#!/usr/bin/env bash

# wget --content-disposition http://yisuo.asia/xampp.php?os=linux
# https://wordpress.org/plugins/wp-mail-smtp/
# 根据内存安装WEBserver  XAMPP 不能自启动

# /opt/lampp/bin/apache -V
# /opt/lampp/bin/suexec -V
# suexec 顾名思义是 对特定目录的CGI脚本有su执行权限
# 所有suexec请求必须把所有虚拟主机的文本根目录都规划在一个主Apache目录中
# 如果要关闭suEXEC功能，应该删除"suexec"文件，并停止和重新启动Apache

# ln -s /opt/lampp/bin/perl /usr/bin/perl

mkdir /var/www
mkdir /var/cgi-bin
mv /opt/lampp/cgi-bin/* /var/cgi-bin
mv /var/www/index.htm* /var/www/index.htm*.bak
echo 'hello world!'>/var/www/index.html

chmod -R 0755 /var/www
chmod -R 0755 /var/cgi-bin
chown -R daemon:daemon /var/www
chown -R daemon:daemon /var/cgi-bin

sed -i "s/\/opt\/lampp\/htdocs/\/var\/www/g" /opt/lampp/etc/httpd.conf
sed -i "s/\/opt\/lampp\/cgi-bin/\/var\/cgi-bin/g" /opt/lampp/etc/httpd.conf
sed -i "s/\/opt\/lampp\/htdocs/\/var\/www/g" /opt/lampp/etc/extra/httpd-ssl.conf
sed -i "s/\/opt\/lampp\/cgi-bin/\/var\/cgi-bin/g" /opt/lampp/etc/extra/httpd-ssl.conf

/opt/lampp/ctlscript.sh restart apache
