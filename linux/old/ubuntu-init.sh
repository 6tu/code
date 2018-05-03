#!/bin/bash

www="/root/www"
ftppub="/root/pub"
ip=`who am i | awk '{print $5}' | sed 's/(//g' | sed 's/)//g'`

# 脚本所在目录
basepath=$(cd `dirname $0`; pwd)
cd $basepath
ubuntu=`cat /etc/issue`

if [[ $ubuntu =~ "Ubuntu" ]] ;
then
        echo $ubuntu
else
        echo "This shell applies only to Ubuntu "
        exit
fi

echo "" && echo "======== system update ========" && echo ""
apt-get update
apt-get upgrade

clear
echo "" && echo "======== install chinese-support ========" && echo ""
apt-get install language-pack-zh-hans language-pack-zh-hant
apt-get install ttf-wqy-zenhei
apt-get install ttf-wqy-* xfonts-wqy fonts-wqy-*
touch /etc/default/locale
echo LANG=zh_CN.UTF-8 >     /etc/default/locale
echo LANGUAGE=zh_CN.UTF-8 >> /etc/default/locale

clear
echo "" && echo "======== install Development Tools ========" && echo ""
apt-get install bzip2 zip unzip git vim curl wget
apt-get install zlib* bzip2 libxml* pcre openssl libssl-dev
apt-get install gawk bison flex texinfo gettext cvs
apt-get install aptitude libgmp-dev libglib2.0-dev intltool libtool subversion
apt-get install build-essential libncurses5-dev cmake automake autoconf
# apt-get install libgtk2.0*

clear
echo "" && echo "======== install python ========" && echo ""
apt-get install python python-dev
wget https://bootstrap.pypa.io/get-pip.py
python get-pip.py

clear
echo "" && echo "======== install shadowsocks and IKEv2 ========" && echo ""
mkdir ss && cd ss
pip install git+https://github.com/shadowsocks/shadowsocks.git@master
touch ss.json
echo "{"                                     >> ss.json
echo "\"    server\":\"::\","                >> ss.json
echo "\"    server_port\":11268,"            >> ss.json
echo "\"    local_address\": \"127.0.0.1\"," >> ss.json
echo "\"    local_port\":1080,"              >> ss.json
echo "\"    password\":\"12345678\","        >> ss.json
echo "\"    timeout\":300,"                  >> ss.json
echo "\"    method\":\"aes-256-cfb\","       >> ss.json
echo "\"    fast_open\": false"              >> ss.json
echo "}"                                     >> ss.json

touch start.sh && chmod +x start.sh
echo  "ssserver -c ss.json --user nobody -d start" > start.sh
./start.sh
cd ..

mkdir ikev2 && cd ikev2
apt-get install strongswan strongswan-libipsec

cd ..

clear
echo "" && echo "======== install web tools ========" && echo ""
mkdir web && cd web
wget https://www.apachefriends.org/xampp-files/7.1.6/xampp-linux-x64-7.1.6-0-installer.run
wget http://soft.vpser.net/lnmp/lnmp1.4-full.tar.gz
wget http://www.ispconfig.org/downloads/ISPConfig-3.1.5.tar.gz
git clone https://github.com/yourshell/ispconfig_setup.git
git clone https://github.com/dclardy64/ISPConfig-3-Debian-Installer.git
chmod +x xampp-linux-x64-7.1.6-0-installer.run
#tar xvfz ISPConfig-3.1.5.tar.gz
#cd ispconfig3_install/install
#php -q update.php

cd ..

clear
echo "" && echo "======== sshd white list ========" && echo ""
echo "sshd:$ip" >> /etc/hosts.allow
echo "sshd:104.131.150.174" >> /etc/hosts.allow
echo "sshd:all" >> /etc/hosts.deny
service xinetd restart

yum remove httpd
userdel apache
groupdel apache
groupadd www
groupadd ftp
useradd www -g www -m -d $www -s /sbin/nologin
useradd admin -g ftp -G www -m -d $www -s /sbin/nologin
useradd ftp -g ftp -m -d $ftppub -s /sbin/nologin

clear
echo "" && echo "======== reboot VPS ========" && echo ""
read -s -n1 -p "This command will reboot the system.  Continue?"
echo "Please enter 'yes' or 'no': $REPLY"
if [[ ! $REPLY =~ "yes" ]] ;then
	exit
fi
apt-get update
apt-get upgrade
reboot





