#!/bin/bash

# https://raw.githubusercontent.com/yourshell/yisuo-script/master/shell/kvm-swap.sh

# 脚本工作目录
basepath=$(cd `dirname $0`; pwd)
basepath=~/
cd ${basepath}

# 许可登录 SSHD 服务 IP
echo "" && echo "======== sshd white list ========" && echo ""
localip1=`who am i | awk '{print $5}' | sed 's/(//g' | sed 's/)//g'`
localip2=`echo $SSH_CLIENT| awk '{print $1}'`

echo "sshd:${localip1}" >> /etc/hosts.allow
echo "sshd:${localip2}" >> /etc/hosts.allow
echo "sshd:13.113.193.*" >> /etc/hosts.allow
echo "sshd:211.94.*.*" >> /etc/hosts.allow
echo "sshd:89.36.215.108" >> /etc/hosts.allow
echo "sshd:104.*.*.*" >> /etc/hosts.allow
echo "sshd:all" >> /etc/hosts.deny
service xinetd restart

# 判断系统
if egrep "CentOS release 6" /etc/redhat-release > /dev/null
then
    cat /etc/redhat-release
else
    echo "This shell applies only to CentOS 6"
    exit
fi

# 系统升级
echo "" && echo "======== system update ========" && echo ""
yum update
# yum install epel-release
yum update

# 安装中文环境
echo "" && echo "======== install chinese-support ========" && echo ""
yum -y groupinstall chinese-support
touch /etc/sysconfig/i18n
echo LANG="zh_CN.UTF-8" > /etc/sysconfig/i18n
echo LANGUAGE="zh_CN.UTF-8:zh_CN.GB18030:zh_CN.GB2312:zh_CN" >> /etc/sysconfig/i18n
echo SUPPORTED="zh_CN.UTF-8:zh_CN.GB18030:zh_CN.GB2312:zh_CN:zh:en_US.UTF-8:en_US:en" >> /etc/sysconfig/i18n
echo SYSFONT="lat0-sun16" >> /etc/sysconfig/i18n
echo export LC_ALL="zh_CN.UTF-8" >> /etc/sysconfig/i18n

# 安装编译环境和依赖库
echo "" && echo "======== install Development Tools ========" && echo ""
yum install -y wget curl git vim zip unzip screen nohup
yum install -y libjpeg-devel libpng-devel libtiff-devel freetype-devel pam-devel gettext-devel pcre-devel
yum install -y libxml2 libxml2-devel libxslt libxslt-devel xmlto asciidoc
yum install -y zlib-devel bzip2-devel xz-devel
yum install -y openssl-devel ncurses-devel libpcap-devel
yum install -y libtool udns-devel libev-devel
yum install -y gcc flex bison autoconf automake
yum -y groupinstall "Development libraries" "Development tools"

# 安装新版 autoconf
echo "" && echo "======== install autoconf latest version========" && echo ""
rpm -e --nodeps autoconf-2.63
mkdir ${basepath}/autoconf && cd ${basepath}/autoconf
wget http://ftp.gnu.org/gnu/autoconf/autoconf-2.69.tar.gz
tar -xzf autoconf-2.69.tar.gz 
cd autoconf-2.69
./configure 
make && make install

# 安装 Python2.7
echo "" && echo "======== install python ========" && echo ""
mkdir ${basepath}/python && cd ${basepath}/python
git clone https://github.com/pypa/pip.git
git clone https://github.com/pypa/setuptools.git
wget https://bootstrap.pypa.io/get-pip.py
wget http://pypi.python.org/packages/11/b6/abcb525026a4be042b486df43905d6893fb04f05aac21c32c638e939e447/pip-9.0.1.tar.gz
wget http://pypi.python.org/packages/a9/23/720c7558ba6ad3e0f5ad01e0d6ea2288b486da32f053c73e259f7c392042/setuptools-36.0.1.zip
wget http://pypi.python.org/packages/source/d/distribute/distribute-0.7.3.zip
wget http://pypi.python.org/packages/source/d/distribute/distribute-0.6.10.tar.gz
wget https://www.python.org/ftp/python/3.6.1/Python-3.6.1.tar.xz
wget https://www.python.org/ftp/python/2.7.13/Python-2.7.13.tgz
tar zxf Python-2.7.13.tgz
# cd Python-2.7.13
# ./configure
# make && make install
# ../python get-pip.py
# 
# mv /usr/bin/python /usr/bin/python.old
# rm -f /usr/bin/python-config
# ln -s /usr/local/bin/python /usr/bin/python
# ln -s /usr/local/bin/python-config /usr/bin/python-config
# ln -s /usr/local/include/python2.7/ /usr/include/python2.7
# sed -i 's/#!\/usr\/bin\/python/#!\/usr\/\bin\/python.old/g' "/usr/bin/yum"

# 安装 shadowsocks
echo "" && echo "======== 安装 shadowsocks-all ========" && echo ""
cd $basepath
mkdir ss && cd ss
wget --no-check-certificate https://raw.githubusercontent.com/teddysun/shadowsocks_install/master/shadowsocks-all.sh
chmod +x shadowsocks-all.sh
./shadowsocks-all.sh 2>&1 | tee shadowsocks-all.log
#vim /etc/shadowsocks-libev/config.json
echo ''> port.log
sed -n '/port = /p' shadowsocks-all.log > sstmp.log
sed -n '/password =/p' shadowsocks-all.log >> sstmp.log
# grep 'port =' shadowsocks-all.log > sstmp.log
port=`sed -n '1p' sstmp.log`
shadowsocksport=`echo $port | awk '{split($0,arr," ");  print arr[3]}'`
password=`sed -n '2p' sstmp.log`
shadowsockspassword=`echo $password | awk '{split($0,arr," ");  print arr[3]}'`
sed -i "s/${shadowsocksport}/11269/g" /etc/shadowsocks-libev/config.json
sed -i "s/teddysun.com/123456789com/g" /etc/shadowsocks-libev/config.json
service shadowsocks restart

# 制作 证书 和安装 VPN
echo "" && echo "======== make Certs and install IKEv2 VPN ========" && echo ""
cd $basepath
mkdir vpn && cd vpn
wget --no-check-certificate https://raw.githubusercontent.com/6tu/code/master/certs/certs-init.sh
wget --no-check-certificate https://raw.githubusercontent.com/6tu/code/master/certs/makecert.sh
#wget --no-check-certificate https://raw.githubusercontent.com/quericy/one-key-ikev2-vpn/master/one-key-ikev2.sh
wget --no-check-certificate https://raw.githubusercontent.com/6tu/code/master/linux/quericy-one-key-ikev2.sh


chmod +x *.sh
./certs-init.sh
./makecert.sh
cp ~/certs/*_cert.crt ./server.cert.pem
cp ~/certs/*_csr_nopw.key ./server.pem
cp ~/certs/demoCA/cacert.pem ./ca.cert.pem
./quericy-one-key-ikev2.sh

# 安装 web 服务器
echo "" && echo "======== install web tools ========" && echo ""
apt-get autoremove apache2
cd $basepath
mkdir soft && cd soft

wget http://www.ispconfig.org/downloads/ISPConfig-3.1.5.tar.gz
git clone https://github.com/yourshell/ispconfig_setup.git
# https://github.com/dclardy64/
## 
wget http://soft.vpser.net/lnmp/lnmp1.4-full.tar.gz
wget --no-check-certificate https://raw.githubusercontent.com/6tu/code/master/linux/xampp-dir.sh
wget --content-disposition http://yisuo.asia/xampp.php?os=linux
# rename "s/\?from_af=t//" *
# rename "s/runrue/run/" *
find . -name "*.run?from_af=true" | sed 's/\.run?from_af=true$//g' | xargs -I{} mv {}.run?from_af=true {}.run
chmod +x xampp*.run
chmod +x xampp-dir.sh
./xampp*.run
./xampp-dir.sh
sed -i "s/if egrep "9 "/if egrep "Red "/g" /opt/lampp/lampp
/opt/lampp/ctlscript.sh restart apache
#
curl -O http://vestacp.com/pub/vst-install.sh
# bash vst-install.sh
# cd ispconfig3_install/install
# php -q update.php

mkdir /var/pub
ftppub="/var/pub"
groupadd ftp
chown ftp:ftp /var/pub
chmod 0777 /var/pub
useradd ftp -g ftp -m -d ${ftppub} -s /sbin/nologin

# wsoole2不兼容php5,只能用在php7
cd ${basepath}
git clone https://github.com/teddysun/lamp.git
cd lamp && chmod +x *.sh
screen -S lamp
./lamp.sh

yum -y update
yum autoremove
yum clean all
yum autoclean

clear
echo "" && echo "======== reboot VPS ========" && echo ""
read -s -n1 -p "This command will reboot the system.  Continue?"
echo "Please enter 'yes' or 'no': $REPLY"
if [[ ! $REPLY =~ "yes" ]] ;then
	exit
fi

reboot









