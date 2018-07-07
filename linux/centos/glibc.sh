
#! /bin/sh
 
# update glibc to 2.23 for CentOS 6

cd ~
test -d glibc || mkdir -p glibc
cd glibc
wget http://cbs.centos.org/kojifiles/packages/glibc/2.22.90/21.el7/x86_64/glibc-2.22.90-21.el7.x86_64.rpm
wget http://cbs.centos.org/kojifiles/packages/glibc/2.22.90/21.el7/x86_64/glibc-common-2.22.90-21.el7.x86_64.rpm
wget http://cbs.centos.org/kojifiles/packages/glibc/2.22.90/21.el7/x86_64/glibc-devel-2.22.90-21.el7.x86_64.rpm
wget http://cbs.centos.org/kojifiles/packages/glibc/2.22.90/21.el7/x86_64/glibc-headers-2.22.90-21.el7.x86_64.rpm
rpm -Uvh glibc-2.22.90-21.el7.x86_64.rpm \
         glibc-common-2.22.90-21.el7.x86_64.rpm \
         glibc-devel-2.22.90-21.el7.x86_64.rpm \
         glibc-headers-2.22.90-21.el7.x86_64.rpm \
         --force --nodeps
 
