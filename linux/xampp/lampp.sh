#!/usr/bin/env bash

cd
basepath=$(cd `dirname $0`; pwd)
webroot=/var/www
github="--no-check-certificate https://raw.githubusercontent.com"
freemem=`free -m|awk 'NR==3 {print $NF}'`
time=`date +%Y%m%d%H%M%S`

echo "" && echo "======== install web Server ========" && echo ""
echo 运行环境: 64位操作系统，内存不小于1G && echo ""
apt-get -y autoremove apache2
apt-get -y autoremove nginx
apt-get install -y gcc rename

cd $basepath
mkdir soft && cd $basepath/soft

# wget http://soft.vpser.net/lnmp/lnmp1.4-full.tar.gz
wget --content-disposition http://yisuo.asia/xampp.php?os=linux
# rename "s/\?from_af=t//" *
# rename "s/runrue/run/" *
find . -name "*.run?from_af=true" | sed 's/\.run?from_af=true$//g' | xargs -I{} mv {}.run?from_af=true {}.run
chmod +x xampp*
./xampp*.run
sed -i "s/if egrep "9 "/if egrep "Red "/g" /opt/lampp/lampp

# 增加多用户模块 mpm-itk
# http://mpm-itk.sesse.net

sed -i '1s/sh/bash/' /opt/lampp/build/libtool
mkdir /opt/lampp/src
cd /opt/lampp/src
wget http://mpm-itk.sesse.net/mpm-itk-2.4.7-04.tar.gz
tar xvf mpm-itk-2.4.7-04.tar.gz
cd mpm-itk-2.4.7-04
./configure --with-apxs=/opt/lampp/bin/apxs
make && make install
# vim /opt/lampp/etc/httpd.conf
sed '158 LoadModule mpm_itk_module modules/mpm_itk.so' -i /opt/lampp/etc/httpd.conf

# 修改证书
mv /opt/lampp/etc/ssl.crt/server.crt /opt/lampp/etc/ssl.crt/server.crt.${time}
mv /opt/lampp/etc/ssl.key/server.key /opt/lampp/etc/ssl.key/server.key.${time}

if [ ! -f "$basepath/vpn/server.cert.pem"  ];then
    cd $basepath
    mkdir vpn && cd $basepath/vpn
    wget ${github}/6tu/code/master/certs/certs-init.sh
    wget ${github}/6tu/code/master/certs/makecert.sh
    chmod +x *.sh
    ./certs-init.sh
    ./makecert.sh
    /bin/cp -rf ~/certs/*_cert.crt ./server.cert.pem
    /bin/cp -rf ~/certs/*_csr_nopw.key ./server.pem
    /bin/cp -rf ~/certs/demoCA/cacert.pem ./ca.cert.pem
    cat server.cert.pem ca.cert.pem > cert.pem
    /bin/cp -rf $basepath/vpn/cert.pem   /opt/lampp/etc/ssl.crt/server.crt
    /bin/cp -rf $basepath/vpn/server.pem /opt/lampp/etc/ssl.key/server.key
else
    cd $basepath/vpn
    cat server.cert.pem ca.cert.pem > cert.pem
    /bin/cp -rf $basepath/vpn/cert.pem   /opt/lampp/etc/ssl.crt/server.crt
    /bin/cp -rf $basepath/vpn/server.pem /opt/lampp/etc/ssl.key/server.key
fi

# 修改WEB目录
sed -i "s/\/opt\/lampp\/htdocs/\/var\/www/g" /opt/lampp/etc/httpd.conf
sed -i "s/\/opt\/lampp\/cgi-bin/\/var\/cgi-bin/g" /opt/lampp/etc/httpd.conf
sed -i "s/\/opt\/lampp\/htdocs/\/var\/www/g" /opt/lampp/etc/extra/httpd-ssl.conf
sed -i "s/\/opt\/lampp\/cgi-bin/\/var\/cgi-bin/g" /opt/lampp/etc/extra/httpd-ssl.conf

mkdir -p ${webroot}/tz
cd ${webroot}
/bin/cp -rf /opt/lampp/cgi-bin /var
mv ${webroot}/index.html ${webroot}/index.html.bak
echo 'hello world!'>${webroot}/index.html
wget -O ${webroot}/tz/vpstz.php ${github}/6tu/code/master/php/vpstz/vpstz.php
wget -O ${webroot}/tz/jquery.js ${github}/6tu/code/master/php/vpstz/yahei/jquery.js
wget -O ${webroot}/tz/p.php     ${github}/6tu/code/master/php/vpstz/yahei/p.php
wget --no-check-certificate https://github.com/6tu/code/raw/master/php/kodexplorer3.46.zip
wget https://github.com/kalcaddle/KODExplorer/archive/master.zip
unzip master.zip
#chmod -Rf 777 ./*
git clone https://github.com/kalcaddle/KODExplorer.git
chmod -Rf 777 ./KODExplorer/*
mv KODExplorer files

chmod -R 0755 ${webroot}
chmod -R 0755 /var/cgi-bin
chown -R daemon:daemon ${webroot}
chown -R daemon:daemon /var/cgi-bin

# 设定 mysql 密码和 phpmyadmin
mysqlpw=`openssl rand -base64 8`
echo ${mysqlpw} > /opt/lampp/mysqlpw
/opt/lampp/ctlscript.sh restart mysql
/opt/lampp/bin/mysqladmin --user=root password ${mysqlpw}

sed -i "s/Require local/# Require local \n    Require all granted/g" /opt/lampp/etc/extra/httpd-xampp.conf
sed -i "s/\['auth_type'] = 'config';/\['auth_type'] = 'config';\n\n\$cfg['Servers'][\$i]['auth_type'] = 'cookie';\n#/g" /opt/lampp/phpmyadmin/config.inc.php

/opt/lampp/ctlscript.sh restart apache

# 增加 FTP 分组
groupadd ftp
useradd -g ftp -d /dev/null -s /usr/sbin/nologin ftp
mkdir /var/pub
ftppub="/var/pub"
chown ftp:ftp /var/pub
chmod 0777 /var/pub
useradd ftp -g ftp -m -d ${ftppub} -s /sbin/nologin

# 32位系统或者内存小于1G时不能安装lampp，采用apt-get安装Apache+PHP5
function check(){
  count=`ps -ef |grep $1 |grep -v "grep" |wc -l`
  echo $count
  if [ 0 == $count ];then
    echo Apache and PHP5 is installing
    apt-get install -y apache2 php5 libapache2-mod-php5
  else
    echo httpd has been started
  fi
}

check httpd



