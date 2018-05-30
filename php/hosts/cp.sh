webroot=/var/www
sudo /bin/cp ${webroot}/hosts/hosts.allow /etc/hosts.allow
sudo /bin/chown root:root /etc/hosts.allow
sudo /bin/rm -rf ${webroot}/hosts/hosts.allow
