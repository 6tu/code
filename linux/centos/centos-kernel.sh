
yum upgrade

rpm --import https://www.elrepo.org/RPM-GPG-KEY-elrepo.org
rpm -Uvh http://www.elrepo.org/elrepo-release-6-8.el6.elrepo.noarch.rpm
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
#yum remove kernel-headers kernel-tools kernel-tools-libs

yum install -y grub grub2
grub2-mkconfig -o /boot/grub2/grub.cfg
相当于UBUNTU中的 update-grub2命令

rpm --import https://www.elrepo.org/RPM-GPG-KEY-elrepo.org
rpm -Uvh http://www.elrepo.org/elrepo-release-7.0-3.el7.elrepo.noarch.rpm
yum --enablerepo=elrepo-kernel install -y kernel-ml kernel-ml-devel kernel-ml-headers

# wget http://elrepo.reloumirrors.net/kernel/el7/x86_64/RPMS/kernel-ml-4.17.4-1.el7.elrepo.x86_64.rpm
# wget http://elrepo.reloumirrors.net/kernel/el7/x86_64/RPMS/kernel-ml-devel-4.17.4-1.el7.elrepo.x86_64.rpm
# wget http://elrepo.reloumirrors.net/kernel/el7/x86_64/RPMS/kernel-ml-headers-4.17.4-1.el7.elrepo.x86_64.rpm
# rpm -ivh kernel-ml-4.17.4-1.el7.elrepo.x86_64.rpm
# rpm -ivh kernel-ml-devel-4.17.4-1.el7.elrepo.x86_64.rpm
# rpm -ivh --force /mnt/cd/Packages/kernel-3.10.0-862.e17.x86_64.rpm

# 查看系统可用内核
# cat /boot/grub2/grub.cfg |grep menuentry
#修改开机时默认使用的内核
# grub2-set-default 'CentOS Linux (3.10.0-327.el7.x86_64) 7 (Core)'
# 或者  grub2-set-default 0



https://teddysun.com/489.html
