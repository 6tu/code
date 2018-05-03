    
mv  apache            linux/
mv  centos            linux/
mv  debian            linux/
mv  iptables          linux/
mv  shell             linux/
mv  ubuntu            linux/
mv  system-security   linux/
mv  apache-windows    windows/
mv  win2016           windows/
mv  win7              windows/
mv  win8-1            windows/
mv  windows-embedded  windows/
mv  winxp             windows/
mv  software          windows/
mv  software          windows/
mv  ip                internet/
mv  free-resource     internet/
mv  domain            internet/
mv  dns               internet/
mv  certificate       internet/
mv  google-com        internet/
mv  mail              internet/
mv  link              internet/
mv  vpn               proxy/
mv  shadowsocks       proxy/
mv  symbian           mobile/
mv  android           mobile/
mv  android           mobile/
mv  pe                operation-maintenance/
mv  perl              code/
mv  php               code/
mv  python            code/
mv  openshift-saas    server/
mv  vps               server/
mv  docker            server/
    
mv  wget              windows/software/
mv  google-play-store mobile/android/

# 替换超链接

sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/apache`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/centos`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/debian`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/iptables`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/shell`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/ubuntu`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/system-security`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/apache-windows`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/win2016`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/win7`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/win8-1`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/windows-embedded`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/winxp`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/software`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/ip`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/free-resource`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/domain`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/dns`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/certificate`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/google-com`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/mail`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/link`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/proxy/vpn`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/proxy/shadowsocks`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/mobile/symbian`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/mobile/android`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/operation-maintenance/pe`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/code/perl`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/code/php`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/code/python`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/server/openshift-saas`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/server/vps`
sed -i "s/..\/..\/wp-includes/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/server/docker`

sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/apache`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/centos`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/debian`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/iptables`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/shell`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/ubuntu`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/system-security`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/apache-windows`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/win2016`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/win7`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/win8-1`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/windows-embedded`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/winxp`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/software`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/ip`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/free-resource`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/domain`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/dns`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/certificate`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/google-com`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/mail`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/link`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/proxy/vpn`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/proxy/shadowsocks`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/mobile/symbian`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/mobile/android`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/operation-maintenance/pe`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/code/perl`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/code/php`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/code/python`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/server/openshift-saas`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/server/vps`
sed -i "s/..\/..\/wp-content/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/server/docker`

sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/apache`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/centos`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/debian`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/iptables`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/shell`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/ubuntu`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/system-security`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/apache-windows`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/win2016`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/win7`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/win8-1`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/windows-embedded`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/winxp`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/software`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/ip`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/free-resource`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/domain`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/dns`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/certificate`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/google-com`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/mail`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/link`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/proxy/vpn`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/proxy/shadowsocks`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/mobile/symbian`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/mobile/android`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/operation-maintenance/pe`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/code/perl`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/code/php`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/code/python`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/server/openshift-saas`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/server/vps`
sed -i "s/..\/..\/2017/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/server/docker`

sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/apache`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/centos`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/debian`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/iptables`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/shell`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/ubuntu`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/system-security`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/apache-windows`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/win2016`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/win7`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/win8-1`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/windows-embedded`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/winxp`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/software`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/ip`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/free-resource`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/domain`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/dns`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/certificate`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/google-com`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/mail`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/link`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/proxy/vpn`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/proxy/shadowsocks`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/mobile/symbian`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/mobile/android`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/operation-maintenance/pe`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/code/perl`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/code/php`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/code/python`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/server/openshift-saas`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/server/vps`
sed -i "s/..\/..\/2016/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/server/docker`

sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/apache`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/centos`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/debian`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/iptables`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/shell`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/ubuntu`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/linux/system-security`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/apache-windows`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/win2011`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/win7`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/win8-1`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/windows-embedded`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/winxp`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/software`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/ip`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/free-resource`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/domain`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/dns`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/certificate`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/google-com`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/mail`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/internet/link`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/proxy/vpn`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/proxy/shadowsocks`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/mobile/symbian`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/mobile/android`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/operation-maintenance/pe`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/code/perl`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/code/php`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/code/python`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/server/openshift-saas`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/server/vps`
sed -i "s/..\/..\/2011/..\/..\/..\/2011/g" `grep -rl "index.html" /home/wwwroot/default/category/server/docker`

sed -i "s/..\/..\/..\/wp-includes/..\/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/mobile/android/google-play-store`
sed -i "s/..\/..\/..\/wp-includes/..\/..\/..\/..\/wp-includes/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/software/wget`

sed -i "s/..\/..\/..\/wp-content/..\/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/mobile/android/google-play-store`
sed -i "s/..\/..\/..\/wp-content/..\/..\/..\/..\/wp-content/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/software/wget`

sed -i "s/..\/..\/..\/2017/..\/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/mobile/android/google-play-store`
sed -i "s/..\/..\/..\/2017/..\/..\/..\/..\/2017/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/software/wget`

sed -i "s/..\/..\/..\/2016/..\/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/mobile/android/google-play-store`
sed -i "s/..\/..\/..\/2016/..\/..\/..\/..\/2016/g" `grep -rl "index.html" /home/wwwroot/default/category/windows/software/wget`

sed -i "s/href=\"..\/index.html/href=\"\/index.html/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/index.html/href=\"\/index.html/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/..\/index.html/href=\"\/index.html/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/..\/..\/index.html/href=\"\/index.html/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/..\/..\/..\/index.html/href=\"\/index.html/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/..\/..\/..\/..\/index.html/href=\"\/index.html/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/..\/..\/..\/..\/..\/index.html/href=\"\/index.html/g" `grep -rl "index.html" /home/wwwroot/default/category`

sed -i "s/href=\"..\/app/href=\"\/app/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/app/href=\"\/app/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/..\/app/href=\"\/app/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/..\/..\/app/href=\"\/app/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/..\/..\/..\/app/href=\"\/app/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/..\/..\/..\/..\/app/href=\"\/app/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/..\/..\/..\/..\/..\/app/href=\"\/app/g" `grep -rl "index.html" /home/wwwroot/default/category`

sed -i "s/href=\"..\/ss/href=\"\/ss/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/ss/href=\"\/ss/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/..\/ss/href=\"\/ss/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/..\/..\/ss/href=\"\/ss/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/..\/..\/..\/ss/href=\"\/ss/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/..\/..\/..\/..\/ss/href=\"\/ss/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/..\/..\/..\/..\/..\/ss/href=\"\/ss/g" `grep -rl "index.html" /home/wwwroot/default/category`

sed -i "s/href=\"..\/ikev2-vpn/href=\"\/ikev2-vpn/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/ikev2-vpn/href=\"\/ikev2-vpn/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/..\/ikev2-vpn/href=\"\/ikev2-vpn/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/..\/..\/ikev2-vpn/href=\"\/ikev2-vpn/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/..\/..\/..\/ikev2-vpn/href=\"\/ikev2-vpn/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/..\/..\/..\/..\/ikev2-vpn/href=\"\/ikev2-vpn/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/..\/..\/..\/..\/..\/ikev2-vpn/href=\"\/ikev2-vpn/g" `grep -rl "index.html" /home/wwwroot/default/category`

sed -i "s/href=\"..\/non-repeat/href=\"\/non-repeat/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/non-repeat/href=\"\/non-repeat/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/..\/non-repeat/href=\"\/non-repeat/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/..\/..\/non-repeat/href=\"\/non-repeat/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/..\/..\/..\/non-repeat/href=\"\/non-repeat/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/..\/..\/..\/..\/non-repeat/href=\"\/non-repeat/g" `grep -rl "index.html" /home/wwwroot/default/category`
sed -i "s/href=\"..\/..\/..\/..\/..\/..\/..\/non-repeat/href=\"\/non-repeat/g" `grep -rl "index.html" /home/wwwroot/default/category`











