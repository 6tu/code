# https://csr.chinassl.net/generator-csr.html
domain=mmh.6tu.me
time=`date +%Y%m%d%H%M%S`
echo ${time}
cd ~
test -d ~/ssl || mkdir -p ~/ssl
test -d ~/ssl/acme || mkdir -p ~/ssl/acme
cd ~/ssl
wget https://raw.githubusercontent.com/6tu/code/master/certs/certs-init.sh
wget https://raw.githubusercontent.com/6tu/code/master/certs/makecert.sh
chmod +x *.sh
./certs-init.sh
./makecert.sh
/bin/cp -rf ~/certs/*_cert.crt /opt/lampp/etc/ssl.crt/${domain}.crt
/bin/cp -rf  ~/certs/*_csr_nopw.key /opt/lampp/etc/ssl.key/${domain}.key
/bin/cp -rf  ~/certs/demoCA/cacert.pem ./ca.pem
/bin/cp -rf /opt/lampp/etc/ssl.crt/${domain}.crt ./${domain}.crt.${time}
/bin/cp -rf /opt/lampp/etc/ssl.key/${domain}.key ./${domain}.key.${time}

curl  https://get.acme.sh | sh
#acme.sh=~/.acme.sh/acme.sh
#source ~/.bashrc
#~/.acme.sh/acme.sh --issue --dns dns_google --dnssleep 900 -d ${domain}
~/.acme.sh/acme.sh  --issue  -d ${domain} --webroot /home/${domain}/
/bin/cp -rf ~/.acme.sh/${domain} ~/ssl/acme
/bin/cp -rf ~/.acme.sh/${domain}/fullchain.cer /opt/lampp/etc/ssl.crt/${domain}.crt
/bin/cp -rf ~/.acme.sh/${domain}/${domain}.key /opt/lampp/etc/ssl.key/${domain}.key
