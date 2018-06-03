
# 添加 PHP 扩展

MASTERKEYID=wpgpg@ysuo.org
phpath=/opt/lampp
webroot=/var/www
plugins=${webroot}/wp-content/plugins
httpd="${phpath}/lampp reloadapache"
apacheuser=daemon

# 安装 GnuPG 和 rng-tools
echo "" && echo "======== 安装 gnupg 扩展========" && echo ""
apt install -y libgpgme11-dev rng-tools man
yum install -y gpgme-devel rng-tools man
rng -r /dev/urandom
rngd -r /dev/urandom
test -d ${phpath}/src || mkdir -p ${phpath}/src
cd ${phpath}/src
wget http://pecl.php.net/get/gnupg-1.4.0.tgz
tar zxvf gnupg-1.4.0.tgz
cd gnupg-1.4.0
${phpath}/bin/phpize
./configure --with-php-config=${phpath}/bin/php-config
make && make test && make install
echo extension="gnupg.so" >> ${phpath}/etc/php.ini

# 增加 mcrypt
echo "" && echo "======== 安装 mcrypt 扩展========" && echo ""
yum install -y libmcrypt libmcrypt-devel mcrypt mhash
apt install -y libmcrypt libmcrypt-dev mcrypt mhash
test -d ${phpath}/src || mkdir -p ${phpath}/src
cd ${phpath}/src
wget https://pecl.php.net/get/mcrypt-1.0.1.tgz
tar zxvf mcrypt-1.0.1.tgz
cd mcrypt-1.0.1
${phpath}/bin/phpize
./configure --with-php-config=${phpath}/bin/php-config
make && make install
echo extension="mcrypt.so" >> ${phpath}/etc/php.ini

# 安装 composer
echo "" && echo "======== 安装 composer 工具包 ========" && echo ""
cd ${phpath}/bin
wget https://getcomposer.org/installer
chmod +x ${phpath}/bin/installer
${phpath}/bin/php ${phpath}/bin/installer
${phpath}/bin/php ${phpath}/bin/
ln -s ${phpath}/bin/php /usr/local/bin/php

# 在代码包中执行下面的命令
# ${phpath}/bin/composer install
