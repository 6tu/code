
phppath=/usr/local/php
ssphppw=12345678
ssphpport=61080

echo "" && echo "======== 安装 swoole 扩展库 ========" && echo ""

mkdir ssphp-src && cd ssphp-src

# 
# 用 pecl 方便安装 PHP 扩展库
# yum install -y pcre-devel
# 用 phpize 安装下面的三个库文件
# http://pecl.php.net/package/
# https://pecl.php.net/package/propro
# https://pecl.php.net/package/raphf
# http://pecl.php.net/package/pecl_http
# 
# 把extension=http.so加到php.ini
# pecl install swoole
# 

# git clone https://github.com/swoole/swoole-src.git
wget https://github.com/swoole/swoole-src/archive/v1.10.3.zip
unzip v1.10.3.zip
cd swoole-src*

${phppath}/bin/phpize
./configure --with-php-config=${phppath}/bin/php-config
make && make install

# vim ${phppath}/etc/php.ini
# 1010 ;extension="interbase.so"
# 1040 date.timezone=Europe/Berlin
# sed -i 'N;1010a\extension="swoole.so"' ${phppath}/etc/php.ini

sed -i '/set the extension_dir directive./a\extension="swoole.so"' ${phppath}/etc/php.ini
cd ..

echo "" && echo "======== 安装 Composer 依赖管理工具 ========" && echo ""
wget https://getcomposer.org/installer
${phppath}/bin/php installer --install-dir=${phppath}/bin --filename=composer
cd ..
rm -rf ssphp-src

echo "" && echo "======== 安装 shadowsocks-php ========" && echo ""
git clone https://github.com/Iceux/shadowsocks-php.git

cd shadowsocks-php

cat > shadowsocks.json <<EOF
{
    "server":"::",
    "server_port":61080,
    "local_address": "127.0.0.1",
    "local_port":1080,
    "password":"password",
    "timeout":300,
    "method":"aes-256-cfb",
    "fast_open": false
}
EOF
mv composer.json composer.json.bak
mv composer.lock composer.lock.bak
# https://packagist.org/packages/liubinzh/shadowsocks-php
${phppath}/bin/php ${phppath}/bin/composer require liubinzh/shadowsocks-php dev-master
# ${phppath}/bin/php  start_server.php -d -c ./shadowsocks.json
# ${phppath}/bin/php  start_local.php -d -c ./shadowsocks.json

# https://github.com/ycgambo/shadowrocket/blob/master/doc/multi-server.md
# ${phppath}/bin/php ${phppath}/bin/composer require ycgambo/shadowrocket

cd ..
mv shadowsocks-php ssphp

git clone https://github.com/shadowsocksBak/shadowsocks-php.git
\cp -rf ./shadowsocks-php/* ./ssphp

rm -rf shadowsocks-php

sed -i "s/444/${ssphpport}/g" ./ssphp/server_ota.php
sed -i "s/yourpassword/${ssphppw}/g" ./ssphp/server_ota.php

${phppath}/bin/php ./ssphp/server_ota.php start



