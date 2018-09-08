# 自学基地
apt -y update
apt -y install aria2
yum -y update
yum -y install aria2
aria2c --dir=./ --max-connection-per-server=16 --max-concurrent-downloads=16 --split=16 --continue=true "http://gcs.popcn.net/mhdata/app.zip"

