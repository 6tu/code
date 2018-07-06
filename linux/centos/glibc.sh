#! /bin/sh

# update glibc to 2.17 for CentOS 6

# https://gist.github.com/harv/f86690fcad94f655906ee9e37c85b174
# https://cbs.centos.org/koji/buildinfo?buildID=7893
# https://rpmfind.net/linux/dag/
# https://getfedora.org/keys/
# https://getfedora.org/keys/obsolete.html


Download the latest elrepo-release rpm from
http://elrepo.org/linux/elrepo/el6/x86_64/RPMS/
# rpm -Uvh elrepo-release*rpm
# yum install elrepo-release

rpm --import https://www.elrepo.org/RPM-GPG-KEY-elrepo.org
rpm -Uvh http://www.elrepo.org/elrepo-release-6-8.el6.elrepo.noarch.rpm
yum install elrepo-release

wget http://copr-be.cloud.fedoraproject.org/results/mosquito/myrepo-el6/epel-6-x86_64/glibc-2.17-55.fc20/glibc-2.17-55.el6.x86_64.rpm
wget http://copr-be.cloud.fedoraproject.org/results/mosquito/myrepo-el6/epel-6-x86_64/glibc-2.17-55.fc20/glibc-common-2.17-55.el6.x86_64.rpm
wget http://copr-be.cloud.fedoraproject.org/results/mosquito/myrepo-el6/epel-6-x86_64/glibc-2.17-55.fc20/glibc-devel-2.17-55.el6.x86_64.rpm
wget http://copr-be.cloud.fedoraproject.org/results/mosquito/myrepo-el6/epel-6-x86_64/glibc-2.17-55.fc20/glibc-headers-2.17-55.el6.x86_64.rpm

rpm --import http://copr-be.cloud.fedoraproject.org/results/mosquito/myrepo-el6/pubkey.gpg
rpm -Uvh glibc-devel-2.17-55.el6.x86_64.rpm \
         glibc-2.17-55.el6.x86_64.rpm \
         glibc-common-2.17-55.el6.x86_64.rpm \
         glibc-headers-2.17-55.el6.x86_64.rpm \
         --force --nodeps

# wget https://rpmfind.net/linux/mageia/distrib/5/x86_64/media/core/updates/glibc-2.20-26.mga5.x86_64.rpm
# wget https://rpmfind.net/linux/mageia/distrib/5/x86_64/media/core/updates/glibc-devel-2.20-26.mga5.x86_64.rpm
# 
# rpm --import https://rpmfind.net/linux/dag/RPM-GPG-KEY-RepoForge-Test-Key-1
# rpm -Uvh glibc-devel-2.20-26.mga5.x86_64.rpm \
#          glibc-2.20-26.mga5.x86_64.rpm \
#          --force --nodeps
# 
# wget https://archive.fedoraproject.org/pub/archive/fedora/linux/updates/21/x86_64/g/glibc-2.20-8.fc21.x86_64.rpm
# wget https://archive.fedoraproject.org/pub/archive/fedora/linux/updates/21/x86_64/g/glibc-common-2.20-8.fc21.x86_64.rpm
# wget https://archive.fedoraproject.org/pub/archive/fedora/linux/updates/21/x86_64/g/glibc-devel-2.20-8.fc21.x86_64.rpm
# wget https://archive.fedoraproject.org/pub/archive/fedora/linux/updates/21/x86_64/g/glibc-headers-2.20-8.fc21.x86_64.rpm
# wget https://archive.fedoraproject.org/pub/archive/fedora/linux/updates/21/x86_64/g/glibc-static-2.20-8.fc21.x86_64.rpm
# wget https://archive.fedoraproject.org/pub/archive/fedora/linux/updates/21/x86_64/g/glibc-utils-2.20-8.fc21.x86_64.rpm
# 
# rpm --import https://getfedora.org/static/95A43F54.txt
# rpm -Uvh glibc-2.20-8.fc21.x86_64.rpm \
#          glibc-common-2.20-8.fc21.x86_64.rpm \
#          glibc-devel-2.20-8.fc21.x86_64.rpm \
#          glibc-headers-2.20-8.fc21.x86_64.rpm \
#          --force --nodeps

wget http://cbs.centos.org/kojifiles/packages/glibc/2.22.90/21.el7/x86_64/glibc-2.22.90-21.el7.x86_64.rpm
wget http://cbs.centos.org/kojifiles/packages/glibc/2.22.90/21.el7/x86_64/glibc-common-2.22.90-21.el7.x86_64.rpm
wget http://cbs.centos.org/kojifiles/packages/glibc/2.22.90/21.el7/x86_64/glibc-devel-2.22.90-21.el7.x86_64.rpm
wget http://cbs.centos.org/kojifiles/packages/glibc/2.22.90/21.el7/x86_64/glibc-headers-2.22.90-21.el7.x86_64.rpm
rpm -Uvh glibc-2.22.90-21.el7.x86_64.rpm \
         glibc-common-2.22.90-21.el7.x86_64.rpm \
         glibc-devel-2.22.90-21.el7.x86_64.rpm \
         glibc-headers-2.22.90-21.el7.x86_64.rpm \
         --force --nodeps

