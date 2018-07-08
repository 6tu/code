#!/bin/bash

yum install -y vim dos2unix zip unzip tree bash-completion time
yum install -y wget curl git lrzsz nmap nc telnet ntp ntpdate net-tools iproute bind-utils whois
yum install -y yum-utils redhat-lsb dmidecode deltarpm vixie-cron crontabs lsof psmisc screen virt-what nohup
yum install -y zlib-devel* bzip2-devel xz-devel libcurl-devel
yum install -y libxml2 libxml2-devel libxslt libxslt-devel xmlto asciidoc man
yum install -y libjpeg-devel libpng-devel libtiff-devel freetype-devel pam-devel gettext-devel pcre-devel
yum install -y openssl openssl-devel ncurses-devel libpcap-devel ca-certificates gpgme-devel rng-tools
yum install -y libtool udns-devel libev-devel
yum install -y gcc flex bison autoconf automake
yum -y groupinstall "Development libraries" "Development tools"
#yum reinstall -y glibc-common

# yum install -y httpd mod_ssl mod_perl php php-devel php-gd php-pecl-memcache php-snmp php-xmlrpc php-xml php-mbstring

rngd -r /dev/urandom
