
https://forums.fedoraforum.org/showthread.php?154342-Using-Linux-iptables-to-set-up-an-internet-gateway-firewall-router-for-office

sudo ufw disable
iptables -P INPUT ACCEPT
iptables -P FORWARD ACCEPT
iptables -F
iptables -Z
iptables -t nat --delete-chain 

iptables -A FORWARD --match policy --pol ipsec --dir in  --proto esp -s 192.168.43.2/24 -j ACCEPT
iptables -A FORWARD --match policy --pol ipsec --dir out --proto esp -d 192.168.43.2/24 -j ACCEPT
iptables -t nat -A POSTROUTING -s 192.168.43.2/24 -o eth0 -m policy --pol ipsec --dir out -j ACCEPT
iptables -t nat -A POSTROUTING -s 192.168.43.2/24 -o eth0 -j MASQUERADE
iptables -t mangle -A FORWARD --match policy --pol ipsec --dir in -s 192.168.43.2/24 -o eth0 -p tcp -m tcp --tcp-flags SYN,RST SYN -m tcpmss --mss 1361:1536 -j TCPMSS --set-mss 1360

阻止从互联网到您的局域网的所有内容，但允许客户端访问广域网。
# Default policy DROP
ip6tables -P FORWARD DROP
# Allow established connections
ip6tables -I FORWARD -m state --state RELATED,ESTABLISHED -j ACCEPT
# Accept packets FROM LAN to everywhere
ip6tables -I FORWARD -i eth1 -j ACCEPT

https://hveem.no/using-dnsmasq-for-dhcpv6

/etc/dnsmasq.conf

# enable IPv6 Route Advertisements
enable-ra

#  The ::1 part refers to the ifid in dhcp6c.conf. 
dhcp-range=tag:eth0,::1,constructor:eth1, ra-names, 12h

# ra-names enables a mode which gives DNS names to dual-stack hosts which do SLAAC  for  IPv6.
# Add your local-only LAN domain
local=/lan.mydomain.no/

#  have your simple hosts expanded to domain
expand-hosts

# set your domain for expand-hosts
domain=lan.mydomain.no

# provide a IPv4 dhcp range too
dhcp-range=lan,172.16.36.64,172.16.36.127,12h

# set authoritative mode
dhcp-authoritative
