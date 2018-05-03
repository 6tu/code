mkdir kernel && cd kernel
wget http://kernel.ubuntu.com/~kernel-ppa/mainline/v4.15.1/linux-headers-4.15.1-041501_4.15.1-041501.201802031831_all.deb
wget http://kernel.ubuntu.com/~kernel-ppa/mainline/v4.15.1/linux-headers-4.15.1-041501-generic_4.15.1-041501.201802031831_amd64.deb
wget http://kernel.ubuntu.com/~kernel-ppa/mainline/v4.15.1/linux-image-4.15.1-041501-generic_4.15.1-041501.201802031831_amd64.deb
dpkg -i linux-image-4.15.1-041501-generic_4.15.1-041501.201802031831_amd64.deb
dpkg -i linux-headers-4.15.1-041501_4.15.1-041501.201802031831_all.deb
dpkg -i linux-headers-4.15.1-041501-generic_4.15.1-041501.201802031831_amd64.deb
reboot
# 用 apt-get purge 删除，不出现deinstall的情况
# 删了旧内核后，用sudo update-grub更新一下启动引导

apt-get purge -y linux-headers-4.4.*
apt-get purge -y linux-image-4.4.*
apt-get purge -y linux-image-extra-4.4.*

# dpkg --get-selections| grep linux
# apt-get autoremove -y linux-headers-4.4.*
# apt-get autoremove -y linux-image-4.4.*
# apt-get autoremove -y linux-image-extra-4.4.*
# 
# dpkg -P linux-headers-4.4.*
# dpkg -P linux-image-4.4.0-112-generic

