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

apt -y update
apt -y install software-properties-common
add-apt-repository ppa:certbot/certbot
apt -y install certbot python3-pyasn1

domain=pub.6tu.me

mkdir -p /etc/letsencrypt

rm -rf /etc/letsencrypt/live/${domain}*
rm -rf /etc/letsencrypt/archive/${domain}*
rm -rf /etc/letsencrypt/renewal/${domain}*
rm -rf /usr/local/etc/ipsec.d/cacerts/chain.pem
rm -rf /usr/local/etc/ipsec.d/certs/cert.pem
rm -rf /usr/local/etc/ipsec.d/private/privkey.pem

echo 'rsa-key-size = 4096
renew-hook = /usr/local/sbin/ipsec restart
' > /etc/letsencrypt/cli.ini

certbot certonly --non-interactive --agree-tos --rsa-key-size 4096 --email info@6tu.me --webroot -w /var/www -d ${domain}

certbot renew --dry-run

cp /etc/letsencrypt/archive/${domain}/chain1.pem   /usr/local/etc/ipsec.d/cacerts/chain.pem
cp /etc/letsencrypt/archive/${domain}/cert1.pem    /usr/local/etc/ipsec.d/certs/cert.pem
cp /etc/letsencrypt/archive/${domain}/privkey1.pem /usr/local/etc/ipsec.d/private/privkey.pem

/usr/local/sbin/ipsec restart

