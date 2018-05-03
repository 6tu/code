# 从外部设定密码用 passout
# 认定已存在密码用 passin

certspath=~/certs
mkdir -p ${certspath}/conf
mkdir -p ${certspath}/demoCA
cd ${certspath}/demoCA
mkdir certs crl newcerts private
touch serial
echo 00000000 > serial
touch index.txt
touch index.txt.attr
touch crlnumber
cd ${certspath}
wget -q -P ${certspath}/demoCA/private/ --no-check-certificate https://raw.githubusercontent.com/yourshell/yisuo-script/master/certs/cakey_nopw.pem
wget -q -P ${certspath}/demoCA/ --no-check-certificate https://raw.githubusercontent.com/yourshell/yisuo-script/master/certs/cacert.pem
wget -q -P ${certspath}/conf/ --no-check-certificate https://raw.githubusercontent.com/yourshell/yisuo-script/master/certs/openssl.conf
wget -q -P ${certspath}/conf/ --no-check-certificate https://raw.githubusercontent.com/yourshell/yisuo-script/master/certs/openssl-ike.conf

cakeypw=`openssl rand -base64 8`
#cakeypw==`head  /dev/urandom  |  tr -dc A-Za-z0-9  | head -c 12`
echo ${cakeypw} > ${certspath}/demoCA/private/cakeypw

openssl rsa -aes256 -passout pass:${cakeypw} -in ${certspath}/demoCA/private/cakey_nopw.pem -out ${certspath}/demoCA/private/cakey.pem

