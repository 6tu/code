# wget -o log --content-disposition URL
# xamppname=`cat log | sed -ne "s/Saving to: \`\(.*\)'/\1/p"`
# basename ${xamppname}
# mv ${xamppname} `basename ${xamppname}`

basepath='~/'

echo "" && echo "======== install web tools ========" && echo ""
apt-get autoremove apache2
cd $basepath
mkdir soft && cd soft
wget --no-check-certificate https://raw.githubusercontent.com/6tu/code/master/linux/xampp/xampp-dir.sh
wget --content-disposition http://yisuo.asia/xampp.php?os=linux
wget http://soft.vpser.net/lnmp/lnmp1.4-full.tar.gz
rename "s/\?from_af=t//" *
rename "s/runrue/run/" *
chmod +x xampp*.run
chmod +x xampp-dir.sh
./xampp*.run
./xampp-dir.sh
/opt/lampp/ctlscript.sh restart apache

mkdir /var/pub
ftppub="/var/pub"
groupadd ftp
chown ftp:ftp /var/pub
chmod 0777 /var/pub
useradd ftp -g ftp -m -d ${ftppub} -s /sbin/nologin

apt-get -y update
apt-get -y upgrade
apt-get autoremove
apt-get clean
apt-get autoclean

clear
