#!/bin/bash
### BEGIN INIT INFO
# Provides:             strongswan
# Required-Start:       $local_fs $network
# Required-Stop:        $local_fs
# Default-Start:        2 3 4 5
# Default-Stop:         0 1 6
# Short-Description:    strongswan server
# Description:          strongswan server
### END INIT INFO

# update-rc.d lampp defaults

domain=fr.6tu.me

if [ ! -f "/usr/bin/yum" ]; then
  apt -y update
  apt -y install software-properties-common
  add-apt-repository ppa:certbot/certbot
  apt -y install certbot python3-pyasn1
else
  yum -y update
  yum -y install certbot
fi

certspath=/etc/letsencrypt/archive
test -d ${certspath} || mkdir -p ${certspath}

ipsecpath=/usr/local/etc/ipsec.d
test -d ${certspath} || ipsecpath=/etc/strongswan/ipsec.d

certbot certonly --non-interactive --agree-tos --rsa-key-size 4096 --email info@6tu.me --webroot -w /var/www -d ${domain}

echo 'rsa-key-size = 4096
renew-hook = /opt/lampp/lampp restartapache
' > /etc/letsencrypt/cli.ini
certbot renew --dry-run

/bin/cp -rf ${certspath}/${domain}/fullchain1.pem   /opt/lampp/etc/ssl.crt/server.crt
/bin/cp -rf ${certspath}/${domain}/privkey1.pem     /opt/lampp/etc/ssl.key/server.key

/opt/lampp/lampp restartapache

echo 'rsa-key-size = 4096
renew-hook = /usr/local/sbin/ipsec restart
' > /etc/letsencrypt/cli.ini
certbot renew --dry-run

/bin/cp -rf ${certspath}/${domain}/chain1.pem   ${ipsecpath}/cacerts/chain.pem
/bin/cp -rf ${certspath}/${domain}/cert1.pem    ${ipsecpath}/certs/cert.pem
/bin/cp -rf ${certspath}/${domain}/privkey1.pem ${ipsecpath}/private/privkey.pem

/usr/local/sbin/ipsec restart

