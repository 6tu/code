
time=`date +%Y%m%d%H%M%S`
echo ${time}
mv /opt/lampp/etc/ssl.crt/server.crt /opt/lampp/etc/ssl.crt/server.crt.${time}
vim /opt/lampp/etc/ssl.crt/server.crt

mv /opt/lampp/etc/ssl.key/server.key /opt/lampp/etc/ssl.key/server.key.${time}
vim /opt/lampp/etc/ssl.key/server.key
