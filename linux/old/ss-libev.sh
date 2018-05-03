# UBUNTU14 安装 shadowsocks-libev
# https://awy.me/2017/05/64m-nei-cun-vps-li-yong-shou-zha-yi-an-zhuang-shadowsocks-libev/

# 更新内核
apt update
apt install linux-generic-lts-wily
# apt-get install debian-keyring debian-archive-keyring
# apt-key update
apt-get -y vim git
apt-get -y --no-install-recommends install gettext build-essential autoconf automake libtool openssl libssl-dev zlib1g-dev xmlto asciidoc libpcre3-dev libudns-dev libev-dev git ca-certificates unzip

cd
mkdir ss
cd ss
# yum install c-ares c-ares-devel
# apt-get install libc-ares2 libc-ares-dev
git clone https://github.com/c-ares/c-ares.git
cd c-ares
./buildconf
./configure
make
make ahost adig acountry (optional)
make install
cd ..

# 务必使用最新版本 https://github.com/jedisct1/libsodium.git  
wget --no-check-certificate https://github.com/jedisct1/libsodium/releases/download/1.0.12/libsodium-1.0.12.tar.gz
tar xf libsodium-1.0.12.tar.gz && cd libsodium-1.0.12
./configure && make && make install
ldconfig
cd ..

# 务必使用最新版本 https://github.com/ARMmbed/mbedtls.git
wget --no-check-certificate https://tls.mbed.org/download/mbedtls-2.4.2-gpl.tgz
tar xf mbedtls-2.4.2-gpl.tgz && cd mbedtls-2.4.2
make SHARED=1 CFLAGS=-fPIC
make DESTDIR=/usr install
ldconfig
cd ..

wget --no-check-certificate https://github.com/shadowsocks/shadowsocks-libev/releases/download/v3.0.6/shadowsocks-libev-3.0.6.tar.gz
tar zxf shadowsocks-libev-3.0.6.tar.gz && cd shadowsocks-libev-3.0.6
./configure
make && make install
cd ..
touch ss.json
echo "{"                                     >> ss.json
echo "    \"server\":\"::0\","                >> ss.json
echo "    \"server_port\":11268,"            >> ss.json
echo "    \"local_address\": \"127.0.0.1\"," >> ss.json
echo "    \"local_port\":1080,"              >> ss.json
echo "    \"password\":\"12345678\","        >> ss.json
echo "    \"timeout\":300,"                  >> ss.json
echo "    \"method\":\"aes-256-cfb\","       >> ss.json
echo "    \"log-file\": \"/dev/null\","      >> ss.json
echo "    \"fast_open\": false"              >> ss.json
echo "}"                                     >> ss.json

touch ss.sh && chmod +x autoss.sh
echo "#!/bin/sh"                                                                         >> autoss.sh
echo "#"                                                                                 >> autoss.sh
echo "ss-server -u -c /root/ss/ss.json -f /tmp/ss-server.pid"                            >> autoss.sh

