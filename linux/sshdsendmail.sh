apt-get -y install sendmail sendmail-cf mailutils

cat >> ~/.bashrc <<EOF

IP=\$(echo \$SSH_CLIENT | awk '{ print \$1}')
IPB=\$(echo \$SSH_CONNECTION | cut -d " " -f 1)
HOSTNAME=\$(hostname)
NOW=\$(date +"%e %b %Y, %a %r")
echo 'Someone from '\$IPA '.'\$IPB'.' \$IPC ' logged into '\$HOSTNAME' on '\$NOW'.' | mail -s 'SSH Login Notification' zhongxiaolee@gmail.com

EOF

