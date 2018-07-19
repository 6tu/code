【译】IPSEC.CONF(5) － IPsec配置  https://segmentfault.com/a/1190000000646294

yum -y install wget screen curl python
screen -S oneinstack      #如果网路出现中断，可以执行命令`screen -r oneinstack`重新连接安装窗口
wget -N --no-check-certificate https://raw.githubusercontent.com/91yun/91yuntest/master/test.sh
bash test.sh -i "io,bandwidth,download,traceroute,backtraceroute,allping,gotoping"

iptables -nL --line-number

wget https://download.strongswan.org/strongswan.tar.gz
tar -xzvf strongswan.tar.gz
