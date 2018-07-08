#!/bin/bash

# https://raw.githubusercontent.com/yourshell/yisuo-script/master/shell/kvm-swap.sh

# 脚本工作目录
basepath=$(cd `dirname $0`; pwd)
basepath=~/
cd ${basepath}

# 判断系统
if egrep "CentOS release 6" /etc/redhat-release > /dev/null
then
    cat /etc/redhat-release
else
    echo "This shell applies only to CentOS 6"
    exit
fi

# 系统升级
echo "" && echo "======== system update ========" && echo ""
# 提供额外的软件包
yum install -y epel-release
yum -y update
# yum makecache
yum install -y wget dos2unix yum-utils

# 建立所需目录
test -d $basepath/shell || mkdir -p $basepath/shell
test -d $basepath/ss    || mkdir -p $basepath/ss
test -d $basepath/vpn   || mkdir -p $basepath/vpn
test -d $basepath/glibc || mkdir -p $basepath/glibc
test -d $basepath/soft  || mkdir -p $basepath/soft

# 下载所需 shell 文件
cd $basepath/shell
wget -q --no-check-certificate https://raw.githubusercontent.com/6tu/code/master/linux/centos/dependencies.sh
wget -q --no-check-certificate https://raw.githubusercontent.com/teddysun/shadowsocks_install/master/shadowsocks-all.sh
wget -q --no-check-certificate https://raw.githubusercontent.com/6tu/code/master/linux/vpn/ikev2vpn.sh
wget -q --no-check-certificate https://raw.githubusercontent.com/6tu/code/master/linux/centos/glibc.sh
wget -q --no-check-certificate https://raw.githubusercontent.com/6tu/code/master/linux/xampp/lampp.sh
wget -q --no-check-certificate https://raw.githubusercontent.com/6tu/code/master/linux/centos/denyssh.sh
chmod +x *.sh && dos2unix *.sh

# 安装编译环境和依赖库
bash ./dependencies.sh

# 安装 shadowsocks
clear && echo "" && echo "======== install shadowsocks========" && echo ""
/bin/cp -rf $basepath/shell/shadowsocks-all.sh $basepath/ss/shadowsocks-all.sh
cd $basepath/ss
./shadowsocks-all.sh 2>&1 | tee shadowsocks-all.log

# 制作 证书 和安装 VPN
clear && echo "" && echo "======== install strongswan========" && echo ""
/bin/cp -rf $basepath/shell/ikev2vpn.sh $basepath/vpn/ikev2vpn.sh
cd $basepath/vpn && bash ./ikev2vpn.sh

# 升级 glibc
clear && echo "" && echo "======== update glibc========" && echo ""
/bin/cp -rf $basepath/shell/glibc.sh $basepath/glibc/glibc.sh
cd $basepath/glibc && bash ./glibc.sh

# 安装 web 服务器
clear && echo "" && echo "======== install xampp========" && echo ""
yum remove apache2 httpd
/bin/cp -rf $basepath/shell/lampp.sh $basepath/soft/lampp.sh
cd $basepath/soft && bash ./lampp.sh

# 更新内核
clear && echo "" && echo "======== update kernel to latest ========" && echo ""
rpm --import https://www.elrepo.org/RPM-GPG-KEY-elrepo.org
rpm -Uvh http://www.elrepo.org/elrepo-release-6-8.el6.elrepo.noarch.rpm
yum --enablerepo=elrepo-kernel install -y kernel-ml kernel-ml-devel kernel-ml-headers

# 安装中文环境
clear && echo "" && echo "======== install chinese-support ========" && echo ""
yum -y groupinstall chinese-support
touch /etc/sysconfig/i18n
echo LANG="zh_CN.UTF-8" > /etc/sysconfig/i18n
echo LANGUAGE="zh_CN.UTF-8:zh_CN.GB18030:zh_CN.GB2312:zh_CN" >> /etc/sysconfig/i18n
echo SUPPORTED="zh_CN.UTF-8:zh_CN.GB18030:zh_CN.GB2312:zh_CN:zh:en_US.UTF-8:en_US:en" >> /etc/sysconfig/i18n
echo SYSFONT="lat0-sun16" >> /etc/sysconfig/i18n
echo export LC_ALL="zh_CN.UTF-8" >> /etc/sysconfig/i18n

# 许可登录 SSHD 服务 IP
clear && echo "" && echo "======== set sshd firewall========" && echo ""
bash $basepath/shell/denyssh.sh
allowip1=`who am i | awk '{print $5}' | sed 's/(//g' | sed 's/)//g'`
allowip2=`echo $SSH_CLIENT| awk '{print $1}'`

echo "sshd:89.36.215.108" >> /etc/hosts.allow
echo "sshd:45.56.85.55" >> /etc/hosts.allow
echo "sshd:13.113.193.*" >> /etc/hosts.allow
echo "sshd:${allowip1}" >> /etc/hosts.allow
echo "sshd:${allowip2}" >> /etc/hosts.allow
echo "sshd:211.94.*.*" >> /etc/hosts.allow
echo "sshd:all" >> /etc/hosts.deny
service sshd restart

# 清理垃圾
clear && echo "" && echo "======== set sshd firewall========" && echo ""
yum -y update
yum autoremove
yum clean all
yum autoclean

clear && echo "" && echo "======== reboot VPS ========" && echo ""
read -s -n1 -p "This command will reboot the system.  Continue?"
echo "Please enter 'yes' or 'no': $REPLY"
if [[ ! $REPLY =~ "yes" ]] ;then
	exit
fi

reboot

