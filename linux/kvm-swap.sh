dd if=/dev/zero of=/home/swap bs=1024 count=2048000
/sbin/mkswap -f /home/swap
/sbin/swapon -f /home/swap
vim /etc/fstab
