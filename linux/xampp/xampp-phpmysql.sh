# yum install -y pwgen
# mysqlpw=`pwgen -s -c -n -y 12 -1`
# mysqlpw=`date | md5sum | cut -b 1-10`

mysqlpw=`openssl rand -base64 8`
echo ${mysqlpw} > /opt/lampp/mysqlpw
/opt/lampp/ctlscript.sh restart mysql

/opt/lampp/bin/mysqladmin --user=root password ${mysqlpw}

sed -i "s/Require local/# Require local \n    Require all granted/g" /opt/lampp/etc/extra/httpd-xampp.conf
sed -i "s/\['auth_type'] = 'config';/\['auth_type'] = 'config';\n\n\$cfg['Servers'][\$i]['auth_type'] = 'cookie';\n#/g" /opt/lampp/phpmyadmin/config.inc.php

/opt/lampp/ctlscript.sh restart apache
