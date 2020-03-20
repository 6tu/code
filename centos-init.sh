#!/bin/bash

echo "" && echo "======== sshd white list and port ========" && echo ""
cip1=`who am i | awk '{print $5}' | sed 's/(//g' | sed 's/)//g'`
cip2=`echo $SSH_CLIENT| awk '{print $1}'`

echo "sshd:${cip1}" >> /etc/hosts.allow
echo "sshd:${cip2}" >> /etc/hosts.allow
echo "sshd:203.93.160.0/20" >> /etc/hosts.allow
echo "sshd:1.50.0.0/16" >> /etc/hosts.allow
echo "sshd:117.136.28.0/24" >> /etc/hosts.allow
echo "sshd:117.142.25.0/24" >> /etc/hosts.allow
echo "sshd:117.142.177.0/21" >> /etc/hosts.allow
echo "sshd:223.103.29.0/24" >> /etc/hosts.allow
echo "sshd:223.104.29.0/24" >> /etc/hosts.allow
echo "sshd:223.104.179.0/23" >> /etc/hosts.allow
echo "sshd:223.104.181.0/22" >> /etc/hosts.allow
echo "sshd:47.240.51.64" >> /etc/hosts.allow
echo "sshd:all" >> /etc/hosts.deny
chmod 666 /etc/hosts.*
sed -i 's/\(# *Port \)22/\22622/' /etc/ssh/sshd_config

echo "" && echo "======== iptables|firewall|selinux ========" && echo ""
iptables -F
iptables -X
iptables -Z
iptables -t nat -F
iptables -t nat -X
iptables -t nat -Z
iptables -t mangle -F
iptables -t mangle -X
iptables -t raw -F
iptables -t raw -X
systemctl stop iptables
systemctl stop firewalld
systemctl disable firewalld
# 一般不删除
# yum -y remove firewalld selinux*
sed -i 's/SELINUX=enforcing/SELINUX=disabled/' /etc/selinux/config

echo "" && echo "======== update file and net manages ========" && echo ""
yum -y update
yum -y install -y epel-release
yum -y install deltarpm yum-utils zip unzip vim dos2unix openssl openssl-devel ca-certificates gpgme-devel rng-tools dos2unix rename
# yum -y provides '*/applydeltarpm'
yum -y install wget curl whois telnet traceroute net-tools libpcap-devel ncurses-devel lsof udns-devel libev-devel
wget -O - https://get.acme.sh | sh
wget https://download-ib01.fedoraproject.org/pub/epel/6/x86_64/Packages/i/iftop-1.0-0.21.pre4.el6.x86_64.rpm
wget https://ftp.tu-chemnitz.de/pub/linux/dag/redhat/el7/en/x86_64/rpmforge/RPMS/nload-0.7.4-1.el7.rf.x86_64.rpm
rpm -ivh iftop-1.0-0.21.pre4.el6.x86_64.rpm
rpm -ivh nload-0.7.4-1.el7.rf.x86_64.rpm

echo "" && echo "======== change random and timezone ========" && echo ""
rngd -r /dev/urandom
ln -sf /usr/share/zoneinfo/Asia/Shanghai /etc/localtime

echo "" && echo "======== restart iptables and sshd ========" && echo ""
yum -y install iptables-services
systemctl enable iptables.service
service iptables save
systemctl restart iptables.service
systemctl restart sshd.service

echo "" && echo "======== clean all temp ========" && echo ""
yum -y update
yum autoremove
yum clean all
yum autoclean

iptables -F
iptables -I INPUT  -p UDP --sport 53   -j ACCEPT
iptables -I OUTPUT -p UDP --dport 53   -j ACCEPT
iptables -I INPUT  -p UDP --sport 500  -j ACCEPT
iptables -I OUTPUT -p UDP --dport 500  -j ACCEPT
iptables -I INPUT  -p UDP --sport 4500 -j ACCEPT
iptables -I OUTPUT -p UDP --dport 4500 -j ACCEPT
iptables -I OUTPUT -p TCP --sport 22622 -j ACCEPT
iptables -I INPUT  -p TCP --dport 20:1024 -j ACCEPT 
iptables -I OUTPUT -p TCP --sport 20:1024 -j ACCEPT
iptables -A INPUT  -p TCP -j DROP
iptables -A OUTPUT -p TCP -j DROP
iptables -A INPUT  -p UDP -j DROP
iptables -A OUTPUT -p UDP -j DROP
iptables -I INPUT  -m pkttype --pkt-type broadcast -j DROP

service iptables save
systemctl restart iptables.service
