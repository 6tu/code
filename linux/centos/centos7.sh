#!/bin/bash

# CentOS 7

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

# 系统升级
echo "" && echo "======== system update ========" && echo ""
yum update
# 提供额外的软件包
yum install epel-release
yum update

# 安装中文环境和时区
echo "" && echo "======== install chinese-support ========" && echo ""
# 当前语言包 locale
# 拥有语言包 locale -a
yum install -y kde-l10n-Chinese
# yum reinstall -y glibc-common

echo LANG="zh_CN.UTF-8">/etc/locale.conf
source /etc/locale.conf
# localectl  set-locale LANG=zh_CN.UTF8
# 设置时区
ln -sf /usr/share/zoneinfo/Asia/Shanghai /etc/localtime

# 安装编译环境和依赖库
echo "" && echo "======== install Development Tools ========" && echo ""
yum install -y wget curl git vim zip unzip screen nohup dos2unix
yum install -y whois net-tools redhat-lsb ca-certificates
yum install -y zlib-devel bzip2-devel xz-devel libcurl-devel
yum install -y openssl-devel ncurses-devel libpcap-devel
# yum install -y gcc flex bison autoconf automake
# yum reinstall -y glibc-common
yum -y groupinstall "Development libraries" "Development tools"
yum install -y libtool libev-devel
yum install -y libxml2 libxml2-devel libxslt libxslt-devel xmlto asciidoc
yum install -y libjpeg-devel libpng-devel libtiff-devel freetype-devel pam-devel gettext-devel pcre-devel
yum install -y gpgme-devel rng-tools man
rngd -r /dev/urandom


# 安装 Python2.7
echo "" && echo "======== install python ========" && echo ""
mkdir ${basepath}/python && cd ${basepath}/python
git clone https://github.com/pypa/pip.git
git clone https://github.com/pypa/setuptools.git
wget https://bootstrap.pypa.io/get-pip.py
wget https://www.python.org/ftp/python/3.6.1/Python-3.6.1.tar.xz
tar Jxvf Python-3.6.1.tar.xz

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
yum remove apache2 httpd
cd $basepath
mkdir soft && cd soft

wget https://raw.githubusercontent.com/6tu/code/master/linux/xampp/lampp.sh
chmod +x lampp.sh
./lampp.sh
echo "/opt/lampp/lampp startapache" >> /etc/rc.local
echo "/opt/lampp/lampp startmysql" >> /etc/rc.local

yum -y update
yum autoremove
yum clean all
yum autoclean

clear


