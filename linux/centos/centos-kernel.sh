
yum upgrade

rpm --import https://www.elrepo.org/RPM-GPG-KEY-elrepo.org
rpm -Uvh http://www.elrepo.org/elrepo-release-6-8.el6.elrepo.noarch.rpm
#yum remove kernel-headers kernel-tools kernel-tools-libs
yum --enablerepo=elrepo-kernel install -y kernel-ml kernel-ml-devel kernel-ml-headers kernel-ml-tools kernel-ml-tools-libs kernel-ml-tools-libs-devel

awk -F\’ ‘$1==”menuentry ” {print $2}’ /etc/grub.conf
vim /etc/grub.conf # 默认值为 0

#grub2-set-default 0
#grub2-mkconfig -o /boot/grub2/grub.cfg
#grub2-editenv list

# 查看系统中全部的内核RPM包:
rpm -qa | grep kernel

# 删除旧内核的RPM包
yum remove kernel-2.6.18-194.el5
