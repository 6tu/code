# Linux下使用ethtool满速网卡速率
# https://zhujiwiki.com/12907.html

yum -y install ethtool net-tools 
apt-get -y install ethtool net-tools

ethtool venet0
ethtool eth0

ethtool -s eth0 speed 100 duplex full autoneg off
ethtool -s venet0 speed 100 duplex full autoneg off
