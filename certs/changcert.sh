cd /etc/letsencrypt/archive
zip -r pub.6tu.me.zip pub.6tu.me
/bin/cp -rf pub.6tu.me.zip /root
rm -rf pub.6tu.me.zip

mv /usr/local/etc/ipsec.d/private/server.pem                  /usr/local/etc/ipsec.d/private/server.pem.bak
mv /usr/local/etc/ipsec.d/certs/server.cert.pem               /usr/local/etc/ipsec.d/certs/server.cert.pem.bak
mv /usr/local/etc/ipsec.d/cacerts/ca.cert.pem                 /usr/local/etc/ipsec.d/cacerts/ca.cert.pem.bak

/bin/cp -rf /etc/letsencrypt/archive/pub.6tu.me/privkey1.pem  /usr/local/etc/ipsec.d/private/server.pem
/bin/cp -rf /etc/letsencrypt/archive/pub.6tu.me/cert1.pem     /usr/local/etc/ipsec.d/certs/server.cert.pem
/bin/cp -rf /etc/letsencrypt/archive/pub.6tu.me/chain1.pem    /usr/local/etc/ipsec.d/cacerts/ca.cert.pem
