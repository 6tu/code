
yum upgrade

rpm --import https://www.elrepo.org/RPM-GPG-KEY-elrepo.org
rpm -Uvh http://www.elrepo.org/elrepo-release-6-8.el6.elrepo.noarch.rpm
yum --enablerepo=elrepo-kernel install -y kernel-ml
vim /etc/grub.conf # 默认值为 0

# 查看系统中全部的内核RPM包:
rpm -qa | grep kernel

# 删除旧内核的RPM包
yum remove kernel-2.6.18-194.el5
