
mkdir /root/ss
cd /root/ss

touch ss.json
echo "{"                                     >> ss.json
echo "    \"server\":\"::\","                >> ss.json
echo "    \"server_port\":11268,"            >> ss.json
echo "    \"local_address\": \"127.0.0.1\"," >> ss.json
echo "    \"local_port\":1080,"              >> ss.json
echo "    \"password\":\"12345678\","        >> ss.json
echo "    \"timeout\":300,"                  >> ss.json
echo "    \"method\":\"aes-256-cfb\","       >> ss.json
echo "    \"log-file\": \"/dev/null\","      >> ss.json
echo "    \"fast_open\": false"              >> ss.json
echo "}"                                     >> ss.json

touch autoss.sh && chmod +x autoss.sh
echo "#!/bin/sh"                                                                         >> autoss.sh
echo "#"                                                                                 >> autoss.sh
echo "# shadowsocks start/restart/stop shadowsocks"                                      >> autoss.sh
echo "#"                                                                                 >> autoss.sh
echo "# chkconfig: 2345 85 15"                                                           >> autoss.sh
echo "# description: start shadowsocks/ssserver at boot time"                            >> autoss.sh
echo ""                                                                                  >> autoss.sh
echo "ssconfig=/root/ss/ss.json"                                                         >> autoss.sh
echo "start(){"                                                                          >> autoss.sh
echo "        ssserver -q -c \$ssconfig --log-file /dev/null --user nobody -d start"     >> autoss.sh
echo "}"                                                                                 >> autoss.sh
echo "stop(){"                                                                           >> autoss.sh
echo "        ssserver -d stop"                                                          >> autoss.sh
echo "}"                                                                                 >> autoss.sh
echo "restart(){"                                                                        >> autoss.sh
echo "        ssserver -q -c \$ssconfig --log-file /dev/null --user nobody -d restart"   >> autoss.sh
echo "}"                                                                                 >> autoss.sh
echo ""                                                                                  >> autoss.sh
echo "case \"\$1\" in"                                                                   >> autoss.sh
echo "start)"                                                                            >> autoss.sh
echo "        start"                                                                     >> autoss.sh
echo "        ;;"                                                                        >> autoss.sh
echo "stop)"                                                                             >> autoss.sh
echo "        stop"                                                                      >> autoss.sh
echo "        ;;"                                                                        >> autoss.sh
echo "restart)"                                                                          >> autoss.sh
echo "        restart"                                                                   >> autoss.sh
echo "        ;;"                                                                        >> autoss.sh
echo "*)"                                                                                >> autoss.sh
echo "        echo \"Usage: \$0 {start|restart|stop}\""                                  >> autoss.sh
echo "        exit 1"                                                                    >> autoss.sh
echo "        ;;"                                                                        >> autoss.sh
echo "esac"                                                                              >> autoss.sh

apt-get install python-pip git -y
pip install --upgrade pip
pip install git+https://github.com/shadowsocks/shadowsocks.git@master
./autoss.sh start

