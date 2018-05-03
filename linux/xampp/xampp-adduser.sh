
# 把第一行 换成 #!/bin/bash
# vim /opt/lampp/build/libtool
sed -i '1s/sh/bash/' /opt/lampp/build/libtool

#apt-get install -y gcc

# http://mpm-itk.sesse.net
mkdir /opt/lampp/src
cd /opt/lampp/src
wget http://mpm-itk.sesse.net/mpm-itk-2.4.7-04.tar.gz
tar xvf mpm-itk-2.4.7-04.tar.gz
cd mpm-itk-2.4.7-04
./configure --with-apxs=/opt/lampp/bin/apxs
make && make install


vim /opt/lampp/etc/httpd.conf
LoadModule mpm_itk_module modules/mpm_itk.so

