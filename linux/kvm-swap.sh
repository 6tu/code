
# bs*count/(1024*1024) = 512*count/(1024*1024) = 0.5*count/1024 = count/2048
# 512MB = count/2048 => count = 512 * 2048 = 1048576


swapoff /home/swap
dd if=/dev/zero of=/home/swap bs=512 count=262144
/sbin/mkswap -f /home/swap
/sbin/swapon -f /home/swap
swapon -s
echo "/home/swap swap swap defaults 0 0" >> /etc/fstab

#UUID=7799f635-1ec9-4235-929d-68fcc24e9272
