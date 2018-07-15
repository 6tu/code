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
#/usr/sbin/update-locale LANG=zh_CN.utf8

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
