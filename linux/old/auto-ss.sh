#!/bin/sh
#
# shadowsocks start/restart/stop shadowsocks
#
# chkconfig: 2345 85 15
# description: start shadowsocks/ssserver at boot time

ssconfig=/root/ss/ss.json
start(){
        ssserver -c $ssconfig --user nobody -d start
}
stop(){
        ssserver -c $ssconfig --user nobody -d stop
}
restart(){
        ssserver -c $ssconfig --user nobody -d restart
}

case "$1" in
start)
        start
        ;;
stop)
        stop
        ;;
restart)
        restart
        ;;
*)
        echo "Usage: $0 {start|restart|stop}"
        exit 1
        ;;
esac
