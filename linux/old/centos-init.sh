#!/bin/bash

www="/root/www"
ftppub="/root/pub"
ip=`who am i | awk '{print $5}' | sed 's/(//g' | sed 's/)//g'`

# 脚本所在目录
basepath=$(cd `dirname $0`; pwd)
cd $basepath
cd /root/

if egrep "CentOS release 6" /etc/redhat-release > /dev/null
then
    cat /etc/redhat-release
else
    echo "This shell applies only to CentOS 6"
    exit
fi

echo "" && echo "======== system update ========" && echo ""
# 系统升级
yum update
yum install epel-release
yum update

clear
echo "" && echo "======== install chinese-support ========" && echo ""
# 安装中文环境
yum -y groupinstall chinese-support
touch /etc/sysconfig/i18n
echo LANG="zh_CN.UTF-8" > /etc/sysconfig/i18n
echo LANGUAGE="zh_CN.UTF-8:zh_CN.GB18030:zh_CN.GB2312:zh_CN" >> /etc/sysconfig/i18n
echo SUPPORTED="zh_CN.UTF-8:zh_CN.GB18030:zh_CN.GB2312:zh_CN:zh:en_US.UTF-8:en_US:en" >> /etc/sysconfig/i18n
echo SYSFONT="lat0-sun16" >> /etc/sysconfig/i18n
echo export LC_ALL="zh_CN.UTF-8" >> /etc/sysconfig/i18n

clear
echo "" && echo "======== install Development Tools ========" && echo ""
# 安装编译环境和相关的库
yum install -y wget curl git vim zip unzip
yum install -y libjpeg-devel libpng-devel libtiff-devel freetype-devel pam-devel gettext-devel pcre-devel
yum install -y libxml2 libxml2-devel libxslt libxslt-devel xmlto asciidoc
yum install -y zlib-devel bzip2-devel xz-devel
yum install -y openssl-devel ncurses-devel libpcap-devel
yum install -y libtool udns-devel libev-devel
yum install -y gcc flex bison autoconf automake
yum -y groupinstall "Development libraries" "Development tools"

clear
echo "" && echo "======== install autoconf latest version========" && echo ""
# 安装新版 autoconf
rpm -e --nodeps autoconf-2.63
mkdir /root/autoconf && cd /root/autoconf
wget http://ftp.gnu.org/gnu/autoconf/autoconf-2.69.tar.gz
tar -xzf autoconf-2.69.tar.gz 
cd autoconf-2.69
./configure 
make && make install

clear
echo "" && echo "======== install python ========" && echo ""
# 安装 Python2.7
mkdir /root/python && cd /root/python
git clone https://github.com/pypa/pip.git
git clone https://github.com/pypa/setuptools.git
wget http://pypi.python.org/packages/11/b6/abcb525026a4be042b486df43905d6893fb04f05aac21c32c638e939e447/pip-9.0.1.tar.gz
wget http://pypi.python.org/packages/a9/23/720c7558ba6ad3e0f5ad01e0d6ea2288b486da32f053c73e259f7c392042/setuptools-36.0.1.zip
wget http://pypi.python.org/packages/source/d/distribute/distribute-0.7.3.zip
wget http://pypi.python.org/packages/source/d/distribute/distribute-0.6.10.tar.gz
wget https://www.python.org/ftp/python/3.6.1/Python-3.6.1.tar.xz

wget https://www.python.org/ftp/python/2.7.13/Python-2.7.13.tgz
tar zxf Python-2.7.13.tgz
cd Python-2.7.13
./configure
make && make install

mv /usr/bin/python /usr/bin/python.old
rm -f /usr/bin/python-config
ln -s /usr/local/bin/python /usr/bin/python
ln -s /usr/local/bin/python-config /usr/bin/python-config
ln -s /usr/local/include/python2.7/ /usr/include/python2.7

cd ..
wget https://bootstrap.pypa.io/get-pip.py
python get-pip.py
cd ..
sed -i 's/#!\/usr\/bin\/python/#!\/usr\/\bin\/python.old/g' "/usr/bin/yum"

clear
echo "" && echo "======== install shadowsocks and IKEv2 ========" && echo ""
mkdir /root/ss
cd /root/ss

touch ss.json
echo "{"                                     >> ss.json
echo "    \"server\":\"::\","                >> ss.json
echo "    \"server_port\":11268,"            >> ss.json
echo "    \"local_address\": \"127.0.0.1\"," >> ss.json
echo "    \"local_port\":1080,"              >> ss.json
echo "    \"password\":\"12345678\","        >> ss.json
echo "    \"timeout\":300,"                  >> ss.json
echo "    \"method\":\"aes-256-cfb\","       >> ss.json
echo "    \"forbidden-ip\":{"                >> ss.json
echo "        \"110.54.128.0/17\","          >> ss.json
echo "        \"112.198.0.0/16\""            >> ss.json
echo "    }"                                 >> ss.json
echo "    \"log-file\": \"/dev/null\","      >> ss.json
echo "    \"fast_open\": false"              >> ss.json
echo "}"                                     >> ss.json

touch autoss.sh && chmod +x autoss.sh
echo "#!/bin/sh"                                                                         >> autoss.sh
echo "#"                                                                                 >> autoss.sh
echo "# shadowsocks start/restart/stop shadowsocks"                                      >> autoss.sh
echo "#"                                                                                 >> autoss.sh
echo "# chkconfig: 2345 85 15"                                                           >> autoss.sh
echo "# description: start shadowsocks/ssserver at boot time"                            >> autoss.sh
echo ""                                                                                  >> autoss.sh
echo "ssconfig=/root/ss/ss.json"                                                         >> autoss.sh
echo "start(){"                                                                          >> autoss.sh
echo "        ssserver -q -c \$ssconfig --log-file /dev/null --user nobody -d start"     >> autoss.sh
echo "}"                                                                                 >> autoss.sh
echo "stop(){"                                                                           >> autoss.sh
echo "        ssserver -d stop"                                                          >> autoss.sh
echo "}"                                                                                 >> autoss.sh
echo "restart(){"                                                                        >> autoss.sh
echo "        ssserver -q -c \$ssconfig --log-file /dev/null --user nobody -d restart"   >> autoss.sh
echo "}"                                                                                 >> autoss.sh
echo ""                                                                                  >> autoss.sh
echo "case \"\$1\" in"                                                                   >> autoss.sh
echo "start)"                                                                            >> autoss.sh
echo "        start"                                                                     >> autoss.sh
echo "        ;;"                                                                        >> autoss.sh
echo "stop)"                                                                             >> autoss.sh
echo "        stop"                                                                      >> autoss.sh
echo "        ;;"                                                                        >> autoss.sh
echo "restart)"                                                                          >> autoss.sh
echo "        restart"                                                                   >> autoss.sh
echo "        ;;"                                                                        >> autoss.sh
echo "*)"                                                                                >> autoss.sh
echo "        echo \"Usage: \$0 {start|restart|stop}\""                                  >> autoss.sh
echo "        exit 1"                                                                    >> autoss.sh
echo "        ;;"                                                                        >> autoss.sh
echo "esac"                                                                              >> autoss.sh

yum install -y mbedtls-devel libsodium-devel libsodium
# 安装 shadowsocks-libev
git clone https://github.com/shadowsocks/shadowsocks-libev.git
cd shadowsocks-libev
git submodule update --init --recursive

# 安装 Libsodium
export LIBSODIUM_VER=1.0.13
wget https://download.libsodium.org/libsodium/releases/libsodium-$LIBSODIUM_VER.tar.gz
tar xvf libsodium-$LIBSODIUM_VER.tar.gz
pushd libsodium-$LIBSODIUM_VER
./configure --prefix=/usr && make
make install
popd
ldconfig

# 安装 MbedTLS
export MBEDTLS_VER=2.5.1
wget https://tls.mbed.org/download/mbedtls-$MBEDTLS_VER-gpl.tgz
tar xvf mbedtls-$MBEDTLS_VER-gpl.tgz
pushd mbedtls-$MBEDTLS_VER
make SHARED=1 CFLAGS=-fPIC
make DESTDIR=/usr install
popd
ldconfig

# Start building
./autogen.sh && ./configure && make
make install

cd /root/ss
cp shadowsocks-libev/rpm/SOURCES/etc/init.d/shadowsocks-libev ./ss-libev.sh
chmod +x ss-libev.sh

# 安装 python 版
# pip install git+https://github.com/shadowsocks/shadowsocks.git@master
# ./ss-libev.sh start
# ./autoss.sh start

mkdir /root/ikev2 && cd /root/ikev2
yum -y install strongswan strongswan-libipsec

clear
echo "" && echo "======== install web tools ========" && echo ""
mkdir /root/web && cd /root/web
wget https://www.apachefriends.org/xampp-files/7.1.6/xampp-linux-x64-7.1.6-0-installer.run
wget http://soft.vpser.net/lnmp/lnmp1.4-full.tar.gz
wget http://www.ispconfig.org/downloads/ISPConfig-3.1.5.tar.gz
git clone https://github.com/yourshell/ispconfig_setup.git
git clone https://github.com/dclardy64/ISPConfig-3-Debian-Installer.git
chmod +x xampp-linux-x64-7.1.6-0-installer.run
#tar xvfz ISPConfig-3.1.5.tar.gz
#cd ispconfig3_install/install
#php -q update.php

cd /root

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
yum -y update
reboot

