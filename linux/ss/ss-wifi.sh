#!/bin/bash

#=========== 用 ShadowSocks 搭建局域网翻墙网关 =====================
#   测试系统: Ubuntu 16.04 LTS-lxde-ARM
#   Shadowsocks-libev 安装脚本源于 秋水逸冰:  https://teddysun.com/358.html
#   ss-tproxy 一键部署脚本源于 Otokaze:  https://www.zfl9.com/ss-redir.html
#=========== 该脚本纯属小白誊写                =====================

 
sudo su

echo "" && echo "======== # dpkg returned an error code (1) 错误========" && echo ""
cd /var/lib/dpkg
mv info info.bak
mkdir info

cd
mkdir /root/.dnsforwarder/config
mkdir ss2wifi
cd ss2wifi

echo "" && echo "======== 安装 依赖库 ========" && echo ""
apt -y update
# apt -y install linux-generic-lts-wily
apt -y install tar zip unzip zlib1g-dev libbz2-dev libpcre3 libpcre3-dev
apt -y install openssl libssl-dev libcurl4-openssl-dev
apt -y install build-essential pkg-config
apt -y install wget curl git vim psmisc lsof
apt -y install ipset iptables-persistent

echo "" && echo "======== 安装 中文支持 ========" && echo ""
apt-get install language-pack-zh-hans language-pack-zh-hant
apt-get install ttf-wqy-zenhei
apt-get install ttf-wqy-* xfonts-wqy fonts-wqy-*
touch /etc/default/locale
echo LANG=zh_CN.UTF-8 >     /etc/default/locale
echo LANGUAGE=zh_CN.UTF-8 >> /etc/default/locale

echo "" && echo "======== 安装 shadowsocks-libev ========" && echo ""
wget --no-check-certificate https://raw.githubusercontent.com/teddysun/shadowsocks_install/master/shadowsocks-libev-debian.sh
chmod +x shadowsocks-libev-debian.sh
./shadowsocks-libev-debian.sh 2>&1 | tee shadowsocks-libev-debian.log
cd ..

echo "" && echo "======== 安装 chinadns ========" && echo ""
wget https://github.com/shadowsocks/ChinaDNS/releases/download/1.3.2/chinadns-1.3.2.tar.gz
tar xf chinadns-1.3.2.tar.gz
cd chinadns-1.3.2/
./configure
make && make install
mkdir /etc/chinadns/
cp -af chnroute.txt /etc/chinadns/
cp -af iplist.txt /etc/chinadns/
cd ..

echo "" && echo "======== 安装 dnsforwarder ========" && echo ""
git clone https://github.com/holmium/dnsforwarder.git
cd dnsforwarder/
./configure
make && make install
dnsforwarder -p
cp -af default.config ~/.dnsforwarder/config
cd ..

echo "" && echo "======== 删除 iptables 规则 ========" && echo ""
cat << EOF > ./delrule.sh
iptables -t mangle -F
iptables -t mangle -X SS-UDP &> /dev/null
iptables -t nat -F
iptables -t nat -X SS-TCP &> /dev/null
ipset -F chnip &> /dev/null
# iptables-save > /etc/iptables.tproxy
EOF

chmod +x ./delrule.sh

echo "" && echo "======== 配置 ss-tproxy ========" && echo ""
git clone https://github.com/zfl9/ss-tproxy.git
cd ss-tproxy/
cp -af ss-tproxy /usr/local/bin/
cp -af ss-tproxy.conf /etc/
vim /etc/ss-tproxy.conf



