#!/usr/bin/env bash

domain=pub.6tu.me

apt install -y wget curl dos2unix zip unzip
cd ~
test -d ~/config || mkdir ~/config
cp /etc/network/interfaces ~/config/interfaces
cp /etc/ssh/sshd_config ~/config/
userdel ubuntu && rm -rf /home/ubuntu

# ------ 建立VPN服务器 ------ 
clear && echo "" && echo "======== install VPN server ========" && echo ""
test -d ~/vpn || mkdir ~/vpn && cd ~/vpn
wget -q https://raw.githubusercontent.com/jawj/IKEv2-setup/master/setup.sh
chmod +x setup.sh && dos2unix setup.sh
bash setup.sh

cp /etc/ssh/sshd_config ~/config/sshd_config2

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

# ------ 安装 shadowsocks ------ 
clear && echo "" && echo "======== install shadowsocks========" && echo ""
test -d ~/ss || mkdir -p ~/ss
cd ~/ss
wget -q https://raw.githubusercontent.com/teddysun/shadowsocks_install/master/shadowsocks-all.sh
chmod +x shadowsocks-all.sh && dos2unix shadowsocks-all.sh
./shadowsocks-all.sh 2>&1 | tee shadowsocks-all.log

# ------ 安装 SSH 防暴力破解脚本 ------ 
clear && echo "" && echo "======== install SSH Prevent attacks========" && echo ""
cd ~
wget -q https://raw.githubusercontent.com/6tu/code/master/linux/centos/denyssh.sh
chmod +x denyssh.sh && dos2unix denyssh.sh
bash denyssh.sh

# ------ 安装中文语言包 ------ 
clear && echo "" && echo "======== install chinese languages ========" && echo ""
apt install -y language-pack-zh-hans language-pack-zh-hant
/usr/sbin/update-locale LANG=zh_CN.UTF-8
/usr/sbin/update-locale LANG=zh_CN.utf8

# ------ 清理缓存、临时文件 ------ 
clear && echo "" && echo "======== install chinese languages ========" && echo ""
apt autoclean
apt autoremove
apt clean
apt clean all
echo > /var/log/btmp
echo > /var/log/wtmp
touch >.bash_history
history -c

echo goodbye
reboot
