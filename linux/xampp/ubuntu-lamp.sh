
# ------ 从源安装WEB服务器 ------ 
clear && echo "" && echo "======== install web server ========" && echo ""
apt install -y openssl libssl-dev libcurl4-openssl-dev
apt install -y apache2 apache2-ssl-dev libapache2-mod-perl2 libapache2-mod-perl2-dev libapache2-mod-php
apt install -y php php-gd php-snmp php-xmlrpc php-xml php-mbstring php-curl
# 加载模块
a2enmod ssl
# 建立证书
curl https://get.acme.sh | sh
source ~/.bashrc
acme.sh --issue -d ${domain} -w /var/www/html
/bin/cp -rf ~/.acme.sh/${domain}/fullchain.cer /etc/ssl/certs/server.crt 
/bin/cp -rf ~/.acme.sh/${domain}/${domain}.key /etc/ssl/private/server.key

cd /etc/apache2/sites-enabled
ln -s /etc/apache2/sites-available/default-ssl.conf /etc/apache2/sites-enabled/default-ssl.conf
nano /etc/apache2/sites-enabled/default-ssl.conf

a2enmod ssl
a2ensite default-ssl
systemctl restart apache2

iptables -I INPUT 3 -p tcp --dport 80 -j ACCEPT
iptables -I INPUT 3 -p tcp --dport 443 -j ACCEPT
netfilter-persistent save
netfilter-persistent reload

# 下载文件
test -d /var/www/html/tz || mkdir -p /var/www/html/tz
echo '<?php phpinfo();' > /var/www/html/tz/phpinfo.php
wget -q -O /var/www/html/tz/vpstz.php    https://raw.githubusercontent.com/6tu/code/master/php/vpstz/vpstz.php
wget -q -O /var/www/html/tz/jquery.js    https://raw.githubusercontent.com/6tu/code/master/php/vpstz/yahei/jquery.js
wget -q -O /var/www/html/tz/p.php        https://raw.githubusercontent.com/6tu/code/master/php/vpstz/yahei/p.php
wget -q -O /var/www/html/4.25.zip        https://github.com/kalcaddle/KodExplorer/archive/4.25.zip
cd /var/www/html
unzip 4.25.zip
mv KodExplorer-4.25 kod
#chmod -Rf 777 ./kod/*

find /var/www/html -type f -exec chmod 644 {} \;
find /var/www/html -type d -exec chmod 755 {} \;
chown -R www-data:www-data /var/www/html
