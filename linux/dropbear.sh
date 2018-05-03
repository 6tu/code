
dropbear

# 编译安装
yum -y install zlib-devel

wget http://matt.ucc.asn.au/dropbear/dropbear-2018.76.tar.bz2
tar xf dropbear-2018.76.tar.bz2
cd dropbear-2018.76
./configure prefix=/usr/local/dropbear --sysconfdir=/etc/dropbear --disable-pam
make
make install
make PROGRAMS="dropbear dbclient dropbearkey dropbearconvert scp"
make PROGRAMS="dropbear="dropbear dbclient dropbearkey dropbearconvert scp" install

# 配置
touch > /var/log/dropbear                        # 建立日志文件
mkdir /etc/dropbear                              # 密钥文件夹
/usr/local/bin/dropbearkey -t dss -f /etc/dropbear/dropbear_dss_host_key
/usr/local/bin/dropbearkey -t rsa -s 4096 -f /etc/dropbear/dropbear_rsa_host_key
/usr/local/bin/dropbearkey -t ecdsa -f /etc/dropbear/dropbear_ecdsa_host_key

vim /etc/profile.d/dropbear.sh
cat /etc/profile.d/dropbear.sh                   # 环境变量
export PATH=/usr/local/dropbear/bin:/usr/local/dropbear/sbin/:$PATH
./etc/profile.d/dropbear.sh

vim /etc/fail2ban/filter.d/dropbear.conf         # Fail2Ban 过滤规则
passwd                                           # 设置密码

sed -i 's/22/2222/g' /etc/ssh/sshd_config        # 修改端口
sed -i 's/22/2222/g' /etc/default/dropbear       # 修改端口

vim /etc/rc.local                                # 开机启动
/usr/local/sbin/dropbear

# 启动

# cat run
# #!/bin/sh
# exec 2>&1
# exec dropbear -d ./dropbear_dss_host_key -r ./dropbear_rsa_host_key -F -E -p 22

/usr/local/sbin/dropbear -p 2222
/etc/init.d/dropbear   restart


/etc/init.d/dropbear disable
/etc/init.d/dropbear stop

## 使用 xinetd 接管 dropbear 服务

将 /sbin/nologin 追加到 /etc/shells 文件

https://www.cnblogs.com/lsdb/p/8488722.html
http://blog.claudineipereira.com/vps-configuracao-basica/
https://lvii.github.io/server/2013/05/28/dropbear-ssh-nologin-and-xinetd/

vim /etc/xinetd.d/dropbear                       # 登录 IP
/etc/xinetd.d/dropbear restart

service xinetd restart


openssh

apt-get -y update
apt-get -y upgrade
apt-get -y install openssh-server
# apt-get -y install openssh-sftp-server
vim /etc/ssh/sshd_config

/etc/init.d/sshd enable
/etc/init.d/sshd start

service sshd restart