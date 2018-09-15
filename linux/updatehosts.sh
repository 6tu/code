apt install -y wget dos2unix
yum install -y wget dos2unix
cd
wget -O denyssh.sh https://raw.githubusercontent.com/6tu/code/master/linux/centos/denyssh.sh
dos2unix denyssh.sh
bash denyssh.sh
rm -rf denyssh.sh

echo "sshd:89.36.215.108" >> /etc/hosts.allow
echo "sshd:162.212.157.193" >> /etc/hosts.allow
echo "sshd:1.50.*.*" >> /etc/hosts.allow
echo "sshd:211.94.*.*" >> /etc/hosts.allow
echo "sshd:36.22.86.185" >> /etc/hosts.allow

echo "sshd:all" >> /etc/hosts.deny
service sshd restart
chmod 0666 /etc/hosts.*

