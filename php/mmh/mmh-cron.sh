#!/bin/bash
PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin
export PATH


/usr/bin/wget -q --spider --no-check-certificate https://www.liuyun.org/mmh/getmh.php

# --spider 不下载任何文件
# mmhpath=/home/www/mmh
# mmhurl=https://www.liuyun.org/mmh/getmh.php
# 
# /usr/bin/wget --no-check-certificate -O $mmhpath/getmh.log $mmhurl
# rm -rf $mmhpath/getmh.log

# 编辑定时器 crontab -e
# 移除Cron作业 crontab -r
# cat /etc/crontab
# 分 时 日 月 周

# /opt/lampp/bin/php -r 'echo file_get_contents(http://domain/cronit.php);'
# /opt/lampp/bin/php /home/www/cronit.php
# 
# wget -q --spider http://domain/cronit.php
# wget -O /dev/null http://domain/cronit.php
# wget -O- http://domain/cronit.php >> /dev/null


# 以下命令用于添加定时器，添加后注释掉
# 
# shellpath=/home/www/mmh
#
# test -d $shellpath || mkdir -p $shellpath
# if [ ! -f "/usr/bin/yum" ]; then
#   echo Ubuntu
#   aptyum=apt-get
#   apt -y install cron
#   service cron start
#   echo "* */1 * * * $shellpath/mmh-cron.sh > /dev/null 2>&1" >> /var/spool/cron/crontabs/root
#   crontab /var/spool/cron/crontabs/root
# else
#   echo CentOS
#   aptyum=yum
#   yum -y install crontabs vixie-cron
#   /bin/systemctl start crond.service
#   /sbin/service crond start
#   echo "* */1 * * * $shellpath/mmh-cron.sh > /dev/null 2>&1" >> /var/spool/cron/root
#   crontab /var/spool/cron/root
# fi

