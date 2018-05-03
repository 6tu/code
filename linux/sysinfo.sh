echo off
#Ubuntu 查看版本和内核

echo -e "系统版本  \c" > sys.log
cat /etc/issue >> sys.log
cat /etc/redhat-release >> sys.log
echo -e "系统名称  \c" >> sys.log
uname -n >> sys.log
echo -e "内核名称  \c" >> sys.log
uname -s >> sys.log
echo -e "内核版本  \c" >> sys.log
uname -r >> sys.log

#查看 CPU 详情

echo -e "\ncpu型号      \c" >> sys.log
cat /proc/cpuinfo | grep name | cut -f2 -d: | uniq -c >> sys.log
echo -e "cpu物理颗数  \c" >> sys.log
cat /proc/cpuinfo | grep physical | uniq -c >> sys.log
echo -e "cpu运行模式  \c" >> sys.log
getconf LONG_BIT >> sys.log
echo -e "cpu支持64b   \c" >> sys.log
cat /proc/cpuinfo | grep flags | grep ' lm ' | wc -l >> sys.log

#cpu信息概要
#lscpu
#cat /proc/cpuinfo

#查看硬盘配额
echo -e "\n硬盘配额" >> sys.log
df -lh >> sys.log

#查看内存配额
echo -e "\n内存配额" >> sys.log
free -lm >> sys.log

