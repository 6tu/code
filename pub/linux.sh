#!/usr/bin/env bash
PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin
export PATH

#=================================================
#	System Required: Debian/Ubuntu/CentOS
#	Description: System Initialization
#	Version: 0.0.1
#	Author: sdy
#	Blog: https://shideyun.com
#=================================================

#检查系统
check_sys(){
	if [[ -f /etc/redhat-release ]]; then
		release="centos"
	elif cat /etc/issue | grep -q -E -i "debian"; then
		release="debian"
	elif cat /etc/issue | grep -q -E -i "ubuntu"; then
		release="ubuntu"
	elif cat /etc/issue | grep -q -E -i "centos|red hat|redhat"; then
		release="centos"
	elif cat /proc/version | grep -q -E -i "debian"; then
		release="debian"
	elif cat /proc/version | grep -q -E -i "ubuntu"; then
		release="ubuntu"
	elif cat /proc/version | grep -q -E -i "centos|red hat|redhat"; then
		release="centos"
    fi
	#bit=`uname -m`
}

# 选择系统类型
echo "please choose the type of your VPS(Xen、KVM: 1  ,  OpenVZ: 2):"
read -p "your choice(1 or 2):" os_choice
if [ "$os_choice" = "1" ]; then
    os="1"
    os_str="Xen、KVM"
    else
        if [ "$os_choice" = "2" ]; then
            os="2"
            os_str="OpenVZ"
            else
            echo "wrong choice!"
            exit 1
        fi
fi

# 网卡名称
netname=ifconfig | grep  "Link" | awk '{print $1}'


sed -i 's/\(# *Port \)22/\22622/' /etc/ssh/sshd_config

echo "" && echo "======== sshd white list ========" && echo ""
cip=`who am i | awk '{print $5}' | sed 's/(//g' | sed 's/)//g'`
echo "sshd:$cip" >> /etc/hosts.allow
echo "sshd:89.36.215.108" >> /etc/hosts.allow
echo "sshd:all" >> /etc/hosts.deny

# 清空iptables规则
iptables -t nat -F
iptables -t nat -X
iptables -t nat -Z
iptables -t nat -P PREROUTING ACCEPT
iptables -t nat -P POSTROUTING ACCEPT
iptables -t nat -P OUTPUT ACCEPT
iptables -t mangle -F
iptables -t mangle -X
iptables -t mangle -P PREROUTING ACCEPT
iptables -t mangle -P INPUT ACCEPT
iptables -t mangle -P FORWARD ACCEPT
iptables -t mangle -P OUTPUT ACCEPT
iptables -t mangle -P POSTROUTING ACCEPT
iptables -F
iptables -X
iptables -P FORWARD ACCEPT
iptables -P INPUT ACCEPT
iptables -P OUTPUT ACCEPT
iptables -t raw -F
iptables -t raw -X
iptables -t raw -P PREROUTING ACCEPT
iptables -t raw -P OUTPUT ACCEPT

iptables -A FORWARD -s 10.31.2.0/24  -j ACCEPT
iptables -t nat -A POSTROUTING -s 10.31.2.0/24 -o ${netname} -j MASQUERADE

if [ ! -f "/usr/bin/yum" ]; then
  apt -y update
  apt -y upgrade
  apt install -y wget curl libcurl3-dev vim dos2unix virt-what psmisc lsof gawk ca-certificates 
  apt install -y tar zip unzip bzip2 zlib1g-dev libbz2-dev
  apt install -y openssl libssl-dev libcurl4-openssl-dev libsasl2-dev
  apt install -y iptables-persistent
  iptables-save > /etc/sysconfig/iptables
  netfilter-persistent save
  netfilter-persistent reload
else
  yum -y update
  yum install epel-release -y
  yum install -y wget curl traceroute net-tools dos2unix zip unzip openssl psmisc virt-what lsof
  yum install -y iptables-services
  systemctl stop firewalld
  systemctl mask firewalld
  systemctl disable firewalld
  systemctl enable iptables.service
  service iptables save
  systemctl restart iptables.service
  systemctl restart sshd.service
  sed -i 's/SELINUX=enforcing/SELINUX=disabled/' /etc/selinux/config
  # service iptables save
fi
echo 3 > /proc/sys/vm/drop_caches

# 相关脚本

cd ~
wget -q https://raw.githubusercontent.com/6tu/code/master/linux/centos/denyssh.sh
wget https://raw.githubusercontent.com/6tu/code/master/linux/xampp/lampp.sh
wget https://raw.githubusercontent.com/teddysun/shadowsocks_install/master/shadowsocks-all.sh
wget https://raw.githubusercontent.com/6tu/code/master/linux/vpn/ikev2vpn.sh
wget https://github.com/Neilpang/acme.sh/blob/master/dnsapi/dns_he.sh

chmod +x *.sh && dos2unix *.sh

# ------ 安装 SSH 防暴力破解脚本 ------ 
clear && echo "" && echo "======== install SSH Prevent attacks========" && echo ""
bash denyssh.sh


# VPS外网IP写入DNS
$domain=test.6tu.me
Get_ip(){
	ip=$(wget -qO- -t1 -T2 ipinfo.io/ip)
	if [[ -z "${ip}" ]]; then
		ip=$(wget -qO- -t1 -T2 api.ip.sb/ip)
		if [[ -z "${ip}" ]]; then
			ip=$(wget -qO- -t1 -T2 members.3322.org/dyndns/getip)
			if [[ -z "${ip}" ]]; then
				ip="VPS_IP"
			fi
		fi
	fi
}

Get_ip
if [[ -z "$ip" ]]; then
	echo -e "${Error} 检测外网IP失败 !"
	read -e -p "请手动输入你的服务器外网IP:" ip
	[[ -z "${ip}" ]] && echo "取消..." && over
fi
echo -e 'cn = "'${ip}'"

bash dns_he.sh ${domain} ${ip}




# 选择安装XAMPP版本








# 用acme.sh申请证书
export HE_Username=""
export HE_Password=""

wget -O -  https://get.acme.sh | sh
source ~/.bashrc

~/.acme.sh/acme.sh --issue --dns dns_he -d disk.6tu.me

/root/.acme.sh/acme.sh --installcert -d shideyun.com \
        --key-file /opt/lampp/etc/ssl.key/shideyun.com.key \
        --fullchain-file /opt/lampp/etc/ssl.crt/shideyun.com.crt \
        --reloadcmd "/opt/lampp/lampp reloadapache"
/opt/lampp/lampp reloadapache

ipsecpath=/usr/local/etc/ipsec.d
test -d ${certspath} || ipsecpath=/etc/strongswan/ipsec.d
/bin/cp -rf /root/.acme.sh/${domain}/chain1.pem   ${ipsecpath}/cacerts/chain.pem
/bin/cp -rf /root/.acme.sh/${domain}/cert1.pem    ${ipsecpath}/certs/cert.pem
/bin/cp -rf /root/.acme.sh/${domain}/privkey1.pem ${ipsecpath}/private/privkey.pem
ipsec restart


find /var/log -name '*.gz'    -exec rm -rf {} \;
find /var/log -name '*.1'     -exec rm -rf {} \;
find /var/log -name '*-2*'    -exec rm -rf {} \;
find /var/log -type f         -exec ls {} \;
touch > /var/log/messages
touch > /var/log/btmp
touch > /var/log/wtmp
touch > /opt/lampp/logs/access_log
touch > /opt/lampp/logs/error_log
touch > /opt/lampp/logs/php_error_log
touch > /opt/lampp/logs/ssl_request_log


echo 3 > /proc/sys/vm/drop_caches
echo > ~/.bash_history
history -c




