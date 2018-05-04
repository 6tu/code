swapoff /home/swap
dd if=/dev/zero of=/home/swap bs=1024 count=256000
/sbin/mkswap -f /home/swap

/sbin/swapon -f /home/swap
vim /etc/fstab
/tmp/swap swap swap defaults 0 0

swapon -s

UUID=7799f635-1ec9-4235-929d-68fcc24e9272
