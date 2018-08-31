
安装 gplaycli 

apt-get -y install wget screen git
git clone https://github.com/teddysun/lamp.git
cd lamp
chmod +x *.sh
screen -S lamp
./lamp.sh

apt-get -y install python3-dev python3-pip libffi-dev libssl-dev
git clone https://github.com/matlink/gplaycli
cd gplaycli
python3 setup.py install

visudo
# 加入任意一行，冒号后面不能有空格
#apache        ALL=NOPASSWD:/var/www/getapk.php
apache        ALL=NOPASSWD:/usr/local/bin/gplaycli

# https://github.com/matlink/gplaycli/releases
# /root/.config/gplaycli/gplaycli.conf
# /var/www/gplaycli.conf
# rm ~/.cache/gplaycli/token #Malformed request
# 
# pip uninstall gplaycli
# rm -rf /etc/gplaycli /etc/cron.daily/gplaycli




