####自学基地

CentOS6 安装 aria2
--------------------
先安装 RepoForge Repos  http://repoforge.org/use/  
wget http://repository.it4i.cz/mirrors/repoforge/redhat/el6/en/x86_64/rpmforge/RPMS/rpmforge-release-0.5.3-1.el6.rf.x86_64.rpm  
rpm -ivh rpmforge-release-0.5.3-1.el6.rf.x86_64.rpm  
yum / apt -y install aria2  
aria2c --dir=保存目录 --max-connection-per-server=16 --max-concurrent-downloads=16 --split=16 --continue=true "网址"  

httpd.conf 全站SSL
--------------------
RewriteEngine on  
RewriteCond %{HTTPS} !=on  
RewriteRule ^(.*) https://%{SERVER_NAME}$1 [L,R]  


