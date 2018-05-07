cd ~
test -d ~/webbackup || mkdir -p ~/webbackup
/bin/cp -rf /home/* ~/webbackup
/bin/cp -rf /var/www ~/webbackup
/bin/cp -rf /opt/lampp/etc ~/webbackup
/bin/cp -rf /opt/lampp/mysqlpw ~/webbackup
zip -r webbackup.zip webbackup
