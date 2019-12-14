#!/bin/bash

# 设置变量
time1=20180228
srcpath=~/vpn
certspath=~/certs
mkcert=~/makecert.sh
cert_cn=vpn.yourshell.info

cakeypw=${certspath}/demoCA/private/cakeypw
ipsecpath=/usr/local/etc/ipsec.d
oldcerts=${srcpath}/oldcerts
time2=`date +%Y%m%d%H%M%S`

# 建立目录
test -d ${oldcerts} || mkdir -p ${oldcerts}

# 制作服务器和客户端的证书
clear
cd ${certspath}
echo && echo make server ${cert_cn} certificate
bash ${mkcert}
mv ${certspath}/"user_csr_nopw.key"           ${certspath}/"server.pem"
mv ${certspath}/"user_cert.crt"               ${certspath}/"server_cert.pem"

echo && echo make VPN Client certificate
bash ${mkcert}
mv ${certspath}/user_csr_nopw.key             ${certspath}/client.pem
mv ${certspath}/user_cert.crt                 ${certspath}/client.cert.pem

echo && echo "configure the pkcs12 cert password(Can be empty):"
openssl pkcs12 -export -inkey client.pem -in client.cert.pem -name "client" -passout pass:${cakeypw} -caname "${cert_cn}"  -out client.cert.p12
cd ~
read -p "Press any key to continue." var

# 查看当前 IPSEC 所使用的证书
echo && echo Old certificate information
openssl x509 -in ${ipsecpath}/certs/server.cert.pem -noout -subject
openssl x509 -in ${ipsecpath}/cacerts/ca.cert.pem -noout -subject

# 备份、复制证书
mv ${srcpath}/server.pem                      ${oldcerts}/${time1}-server.pem
mv ${srcpath}/server.cert.pem                 ${oldcerts}/${time1}-server.cert.pem
mv ${srcpath}/ca.cert.pem                     ${oldcerts}/${time1}-ca.cert.pem
                                              
mv ${ipsecpath}/private/server.pem            ${oldcerts}/${time2}-server.pem
mv ${ipsecpath}/certs/server.cert.pem         ${oldcerts}/${time2}-server.cert.pem
mv ${ipsecpath}/cacerts/ca.cert.pem           ${oldcerts}/${time2}-ca.cert.pem

/bin/cp -rf ${certspath}/client.pem           ${srcpath}/client.pem
/bin/cp -rf ${certspath}/client.cert.pem      ${srcpath}/client.cert.pem
/bin/cp -rf ${certspath}/client.cert.p12      ${srcpath}/client.cert.p12
/bin/cp -rf ${certspath}/server.pem           ${srcpath}/server.pem
/bin/cp -rf ${certspath}/server_cert.pem      ${srcpath}/server.cert.pem
/bin/cp -rf ${certspath}/demoCA/cacert.pem    ${srcpath}/ca.cert.pem

# 删除${srcpath}中证书
rm -rf ${certspath}/*.pem ${certspath}/*.key ${certspath}/*.crt ~/cer${certspath}ts/*.p12

# 查看替换后的 IPSEC 所使用的证书

/bin/cp -rf ${srcpath}/server.pem             ${ipsecpath}/private/server.pem
/bin/cp -rf ${srcpath}/server.cert.pem        ${ipsecpath}/certs/server.cert.pem
/bin/cp -rf ${srcpath}/ca.cert.pem            ${ipsecpath}/cacerts/ca.cert.pem

echo && echo Current certificate information
openssl x509 -in ${ipsecpath}/certs/server.cert.pem -noout -subject
openssl x509 -in ${ipsecpath}/cacerts/ca.cert.pem -noout -subject
