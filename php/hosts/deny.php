<?php
echo '<pre>';
system('cat /etc/hosts.deny');
if (!file_exists('./hosts.deny.bak')) system('/bin/cp -rf /etc/hosts.deny ./hosts.deny.bak');
system("sed -i 's/^sshd:all*//g' /etc/hosts.deny"); # 把 sshd:all 开头的行替换为空行
#system('echo "sshd:all">>/etc/hosts.deny');

$deny = file_get_contents('hosts.deny.bak');
$deny = str_replace("'","\'",$deny);
$deny = str_replace("\n","\">> /etc/hosts.deny');\nsystem('echo -e \"",$deny);
$deny = 'system(\'echo -e "' . $deny . '">> /etc/hosts.deny\');';
$deny = "<?php\n\n" . $deny . "\n\n";
file_put_contents('udeny.php', $deny);

/*
system('echo -e "#"> /etc/hosts.deny');
system('echo -e "# hosts.deny	This file contains access rules which are used to">> /etc/hosts.deny');
system('echo -e "#		deny connections to network services that either use">> /etc/hosts.deny');
system('echo -e "#		the tcp_wrappers library or that have been">> /etc/hosts.deny');
system('echo -e "#		started through a tcp_wrappers-enabled xinetd.">> /etc/hosts.deny');
system('echo -e "#">> /etc/hosts.deny');
system('echo -e "#		The rules in this file can also be set up in">> /etc/hosts.deny');
system('echo -e "#		/etc/hosts.allow with a \'deny\' option instead.">> /etc/hosts.deny');
system('echo -e "#">> /etc/hosts.deny');
system('echo -e "#		See \'man 5 hosts_options\' and \'man 5 hosts_access\'">> /etc/hosts.deny');
system('echo -e "#		for information on rule syntax.">> /etc/hosts.deny');
system('echo -e "#		See \'man tcpd\' for information on tcp_wrappers">> /etc/hosts.deny');
system('echo -e "#">> /etc/hosts.deny');
system('echo -e "sshd:all">> /etc/hosts.deny');
*/
