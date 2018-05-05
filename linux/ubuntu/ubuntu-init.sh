#!/bin/bash

# apt-get -y update        #　更新源
# apt-get -y upgrade       # 更新已安装的包  -f = --fix-missing 强制/修复安装
# apt-get -y dist-upgrade  #  更新已安装的包及依赖
# apt-get -y install wget dos2unix
# wget https://raw.githubusercontent.com/6tu/code/master/linux/ubuntu/ubuntu-init.sh
# chmod +x ubuntu-init.sh
# dos2unix ubuntu-init.sh
# ./ubuntu-init.sh
# nohup screen
#wgetgithub=wget --no-check-certificate https://raw.githubusercontent.com
# 脚本所在目录,判断系统
cd
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

echo "" && echo "======== sshd white list ========" && echo ""
ip=`who am i | awk '{print $5}' | sed 's/(//g' | sed 's/)//g'`
echo "sshd:$ip" >> /etc/hosts.allow
echo "sshd:13.113.193.*" >> /etc/hosts.allow
echo 'sshd:211.94.*.*' >> /etc/hosts.allow
echo "sshd:89.36.215.108" >> /etc/hosts.allow
echo "sshd:all" >> /etc/hosts.deny
service xinetd restart

# linode 常见错误
# echo "" && echo "======== # dpkg returned an error code (1) 错误========" && echo ""
# mv /var/lib/dpkg/info /var/lib/dpkg/info.bak
# mkdir /var/lib/dpkg/info
# cd

echo "" && echo "======== 安装 依赖库 ========" && echo ""
dpkg --configure -a
apt-get -y update --fix-missing
apt-get -y upgrade
apt-get -y install vim wget git curl libcurl3-dev
apt-get -y install tar zip unzip bzip2 zlib1g-dev libbz2-dev
apt-get -y install libpcre3 libpcre3-dev libxml2-dev icu-devtools libicu-dev xmlto asciidoc
apt-get -y install openssl libssl-dev libcurl4-openssl-dev libsasl2-dev
apt-get -y install mcrypt libmcrypt-dev libmcrypt4 libgmp-dev libgmp3-dev libmhash-dev
apt-get -y install virt-what libudns-dev ipset iptables-persistent

# apt-get -y install libgnutls28-dev libwrap0-dev libpam0g-dev libseccomp-dev libreadline-dev libnl-route-3-dev libc6-dev
# apt-get -y install libxslt1-dev libgd-dev libgeoip-dev
# apt-get -y install libperl-dev libio-pty-perl libipc-run-perl

apt-get -y install psmisc lsof glibmm-2.4 libdb-dev libev-dev
apt-get -y install gawk bison flex texinfo gettext cvs
apt-get -y install aptitude libglib2.0-dev intltool libtool subversion supervisor bash-completion ca-certificates
apt-get -y install build-essential pkg-config libncurses5-dev cmake automake autoconf

echo "" && echo "======== 安装 中文支持 ========" && echo ""
apt-get -y install language-pack-zh-hans language-pack-zh-hant
apt-get -y install ttf-wqy-zenhei
apt-get -y install ttf-wqy-* xfonts-wqy fonts-wqy-*
touch /etc/default/locale
echo LANG=zh_CN.UTF-8 >     /etc/default/locale
echo LANGUAGE=zh_CN.UTF-8 >> /etc/default/locale

echo "" && echo "======== 更新内核 ========" && echo ""
# apt-get -y install linux-generic-lts-wily
cd $basepath
mkdir kernel && cd kernel
wget http://kernel.ubuntu.com/~kernel-ppa/mainline/v4.15.1/linux-headers-4.15.1-041501_4.15.1-041501.201802031831_all.deb
wget http://kernel.ubuntu.com/~kernel-ppa/mainline/v4.15.1/linux-headers-4.15.1-041501-generic_4.15.1-041501.201802031831_amd64.deb
wget http://kernel.ubuntu.com/~kernel-ppa/mainline/v4.15.1/linux-image-4.15.1-041501-generic_4.15.1-041501.201802031831_amd64.deb
# dpkg -i linux-image-4.15.1-041501-generic_4.15.1-041501.201802031831_amd64.deb
# dpkg -i linux-headers-4.15.1-041501_4.15.1-041501.201802031831_all.deb
# dpkg -i linux-headers-4.15.1-041501-generic_4.15.1-041501.201802031831_amd64.deb

echo "" && echo "======== install python ========" && echo ""
cd $basepath
mkdir python && cd python
apt-get -y install python python-dev
wget https://bootstrap.pypa.io/get-pip.py
python get-pip.py
pip install --upgrade pip

echo "" && echo "======== 安装 shadowsocks-libev ========" && echo ""
cd $basepath
mkdir ss && cd ss
wget --no-check-certificate https://raw.githubusercontent.com/teddysun/shadowsocks_install/master/shadowsocks-libev-debian.sh
chmod +x shadowsocks-libev-debian.sh
./shadowsocks-libev-debian.sh 2>&1 | tee shadowsocks-libev-debian.log

wget --no-check-certificate https://raw.githubusercontent.com/teddysun/shadowsocks_install/master/shadowsocks-all.sh
chmod +x shadowsocks-all.sh
./shadowsocks-all.sh 2>&1 | tee shadowsocks-all.log

#vim /etc/shadowsocks-libev/config.json
echo ''> port.log
sed -n '/port = /p' shadowsocks-libev-debian.log > sstmp.log
sed -n '/password =/p' shadowsocks-libev-debian.log >> sstmp.log
# grep 'port =' shadowsocks-libev-debian.log > sstmp.log
port=`sed -n '1p' sstmp.log`
shadowsocksport=`echo $port | awk '{split($0,arr," ");  print arr[3]}'`
password=`sed -n '2p' sstmp.log`
shadowsockspassword=`echo $password | awk '{split($0,arr," ");  print arr[3]}'`
sed -i "s/${shadowsocksport}/11269/g" /etc/shadowsocks-libev/config.json
sed -i "s/teddysun.com/123456789com/g" /etc/shadowsocks-libev/config.json
service shadowsocks restart

echo "" && echo "======== make Certs and install IKEv2 VPN ========" && echo ""
cd $basepath
mkdir vpn && cd vpn
wget --no-check-certificate https://raw.githubusercontent.com/6tu/code/master/certs/certs-init.sh
wget --no-check-certificate https://raw.githubusercontent.com/6tu/code/master/certs/makecert.sh
# wget --no-check-certificate https://raw.githubusercontent.com/quericy/one-key-ikev2-vpn/master/one-key-ikev2.sh
wget --no-check-certificate https://raw.githubusercontent.com/6tu/code/master/linux/quericy-one-key-ikev2.sh
chmod +x *.sh
./certs-init.sh
./makecert.sh
cp ~/certs/*_cert.crt ./server.cert.pem
cp ~/certs/*_csr_nopw.key ./server.pem
cp ~/certs/demoCA/cacert.pem ./ca.cert.pem
./quericy-one-key-ikev2.sh

echo "" && echo "======== install web tools ========" && echo ""
apt-get autoremove apache2
cd $basepath
mkdir soft && cd soft
wget --no-check-certificate https://raw.githubusercontent.com/6tu/code/master/linux/xampp/xampp-dir.sh
wget --content-disposition http://yisuo.asia/xampp.php?os=linux
wget http://soft.vpser.net/lnmp/lnmp1.4-full.tar.gz
rename "s/\?from_af=t//" *
rename "s/runrue/run/" *
chmod +x xampp*.run
chmod +x xampp-dir.sh
./xampp*.run
./xampp-dir.sh
mkdir -p /var/www/tz
wget -O /var/www/tz/vpstz.php https://raw.githubusercontent.com/6tu/code/master/php/vpstz/vpstz.php
chown -R daemon:daemon /var/www

cd $basepath/vpn
cat server.cert.pem ca.cert.pem > cert.pem
/bin/cp -rf $basepath/vpn/cert.pem /opt/lampp/etc/ssl.crt/server.crt
/bin/cp -rf $basepath/vpn/server.pem      /opt/lampp/etc/ssl.key/server.key
/opt/lampp/ctlscript.sh restart apache

mkdir /var/pub
ftppub="/var/pub"
groupadd ftp
chown ftp:ftp /var/pub
chmod 0777 /var/pub
useradd ftp -g ftp -m -d ${ftppub} -s /sbin/nologin

apt-get -y update
apt-get -y upgrade
apt-get autoremove
apt-get clean
apt-get autoclean

clear
echo "" && echo "======== reboot VPS ========" && echo ""
read -s -n1 -p "This command will reboot the system.  Continue?"
echo "Please enter 'yes' or 'no': $REPLY"
if [[ ! $REPLY =~ "yes" ]] ;then
	exit
fi

reboot

