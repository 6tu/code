#!/bin/bash
PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin
export PATH

# crontab -e
#分 时 日 月 周
# centos
#echo '*/10 * * * * /var/www/mmh/mmh-cron.sh > /dev/null 2>&1' >> /var/spool/cron/root
#crontab /var/spool/cron/root

# ubuntu
#echo '*/10 * * * * /var/www/mmh/mmh-cron.sh > /dev/null 2>&1' >> /var/spool/cron/crontabs/root
#crontab /var/spool/cron/crontabs/root

mmhpath=/var/www/mmh
test -d $mmhpath || mkdir -p $mmhpath

# --spider 不下载任何文件。
/usr/bin/wget --no-check-certificate -O $mmhpath/getmh.log https://ysuo.org/mmh/getmh.php
rm -rf $mmhpath/getmh.log

