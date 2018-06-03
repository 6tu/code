#!/bin/bash
yum -y install  vixie-cron crontabs
mkdir -p /usr/local/cron/
cat > /usr/local/cron/sshdeny.sh << "EOF"
#!/bin/bash
DEFINE="3"
cat /var/log/secure|awk '/Failed/{print $(NF-3)}'|sort|uniq -c|awk '{print $2"="$1;}'>/tmp/sshDenyTemp.txt
for i in `cat /tmp/sshDenyTemp.txt`
do
IP=`echo $i |awk -F= '{print $1}'`
NUM=`echo $i|awk -F= '{print $2}'`
if [ $NUM -gt $DEFINE ];
then
grep $IP /etc/hosts.deny > /dev/null
if [ $? -gt 0 ];
then
echo "sshd:$IP" >> /etc/hosts.deny
fi
fi
done
echo > /var/log/secure
rm -rf /tmp/sshDenyTemp.txt
EOF
chmod +x /usr/local/cron/sshdeny.sh
echo '*/60 * * * * /usr/local/cron/sshdeny.sh > /dev/null 2>&1' >> /var/spool/cron/root
chmod 600 /var/spool/cron/root
echo "+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++"
echo "Deny for SSH Cront have added success!"
echo "The task work by 5/min"
echo "If you want to allow one, please delete it from /etc/hosts.deny"
echo "+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++"