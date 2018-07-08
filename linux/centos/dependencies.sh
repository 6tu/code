#!/bin/bash

yum install -y --skip-broken vim dos2unix zip unzip tree bash-completion time
yum install -y --skip-broken wget curl git lrzsz nmap nc telnet ntp ntpdate net-tools iproute bind-utils whois
yum install -y --skip-broken yum-utils redhat-lsb dmidecode deltarpm vixie-cron crontabs lsof psmisc screen virt-what nohup
yum install -y --skip-broken zlib-devel* bzip2-devel xz-devel libcurl-devel
yum install -y --skip-broken libxml2 libxml2-devel libxslt libxslt-devel xmlto asciidoc man
yum install -y --skip-broken libjpeg-devel libpng-devel libtiff-devel freetype-devel pam-devel gettext-devel pcre-devel
yum install -y --skip-broken openssl openssl-devel ncurses-devel libpcap-devel ca-certificates gpgme-devel rng-tools
yum install -y --skip-broken libtool udns-devel libev-devel
# yum install -y --skip-broken gcc flex bison autoconf automake
# yum -y groupinstall "Development libraries" "Development tools"
# yum reinstall -y glibc-common

# yum install -y httpd mod_ssl mod_perl php php-devel php-gd php-pecl-memcache php-snmp php-xmlrpc php-xml php-mbstring

rngd -r /dev/urandom

rpm -e --nodeps autoconf-2.63
test -d ~/autoconf || mkdir ~/autoconf
cd ~/autoconf
wget http://ftp.gnu.org/gnu/autoconf/autoconf-2.69.tar.gz
tar -xzf autoconf-2.69.tar.gz 
cd autoconf-2.69
./configure 
make && make install

