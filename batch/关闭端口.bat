@echo off
color 1f 
title ������ʹ��һ������Σ�ն˿ںͷ��� by et
echo ������ʹ��һ������Σ�ն˿ںͷ���
echo "���ڰ����ر���ЩΣ�ն˿ڣ����Ե�"
echo �����ڿ���Windows����ǽ����
net start MpsSvc
echo �����ڰ�������Windows����ǽ��������
sc config MpsSvc start= auto
echo ���������÷���ǽ��
netsh advfirewall set allprofiles state on
echo "���ڰ������ζ˿�...."
echo. 
echo. 
echo. 
echo ��������135�˿� ���Ժ� 
netsh advfirewall firewall delete rule name = "Disable port 135 - TCP"
netsh advfirewall firewall add rule name = "Disable port 135 - TCP" dir = in action = block protocol = TCP localport = 135
echo. 
netsh advfirewall firewall delete rule name = "Disable port 135 - UDP"
netsh advfirewall firewall add rule name = "Disable port 135 - UDP" dir = in action = block protocol = UDP localport = 135
echo. 
echo ��������137�˿� ���Ժ� 
netsh advfirewall firewall delete rule name = "Disable port 137 - TCP"
netsh advfirewall firewall add rule name = "Disable port 137 - TCP" dir = in action = block protocol = TCP localport = 137
echo. 
netsh advfirewall firewall delete rule name = "Disable port 137 - UDP"
netsh advfirewall firewall add rule name = "Disable port 137 - UDP" dir = in action = block protocol = UDP localport = 137
echo. 
echo ��������138�˿� ���Ժ� 
netsh advfirewall firewall delete rule name = "Disable port 138 - TCP"
netsh advfirewall firewall add rule name = "Disable port 138 - TCP" dir = in action = block protocol = TCP localport = 138
echo. 
netsh advfirewall firewall delete rule name = "Disable port 138 - UDP"
netsh advfirewall firewall add rule name = "Disable port 138 - UDP" dir = in action = block protocol = UDP localport = 138
echo. 
echo ��������139�˿� ���Ժ� 
netsh advfirewall firewall delete rule name = "Disable port 139 - TCP"
netsh advfirewall firewall add rule name = "Disable port 139 - TCP" dir = in action = block protocol = TCP localport = 139
echo. 
netsh advfirewall firewall delete rule name = "Disable port 139 - UDP"
netsh advfirewall firewall add rule name = "Disable port 139 - UDP" dir = in action = block protocol = UDP localport = 139
echo. 
echo ���ڹر�445�˿� ���Ժ� 
netsh advfirewall firewall delete rule name = "Disable port 445 - TCP"
netsh advfirewall firewall add rule name = "Disable port 445 - TCP" dir = in action = block protocol = TCP localport = 445
echo. 
netsh advfirewall firewall delete rule name = "Disable port 445 - UDP"
netsh advfirewall firewall add rule name = "Disable port 445 - UDP" dir = in action = block protocol = UDP localport = 445
echo.

echo "Σ�ն˿��Ѿ���Windows����ǽ���γɹ�"

echo.
echo ----------------
echo �����ڹر�Workstation��LanmanWorkstation������
sc stop LanmanWorkstation
sc config LanmanWorkstation start= disabled

echo.
echo ----------------
echo �����ڹر�Server��LanmanServer������
sc stop LanmanServer
sc config LanmanServer start= disabled

echo.
echo ----------------
echo �����ڹر�TCP/IP NetBIOS Helper��lmhosts���������
sc stop lmhosts
sc config lmhosts start= disabled

echo.
echo ----------------
echo �����ڹر�Distributed Transaction Coordinator��MSDTC���������
sc stop MSDTC
sc config MSDTC start= disabled

echo.
echo ----------------
echo �����ڹر�NetBT����
sc stop NetBT
sc config NetBT start= disabled

echo.
echo ----------------
reg add "hklm\System\CurrentControlSet\Services\NetBT\Parameters" /v "SMBDeviceEnabled" /t reg_dword /d "0" /f
reg add "hklm\SOFTWARE\Microsoft\Ole" /v "EnableDCOM" /t reg_sz /d "N" /f
reg add "hklm\SOFTWARE\Microsoft\Rpc" /v "DCOM Protocols" /t reg_multi_sz /d "" /f

echo.
echo ----------------
echo "��ϲ����Σ�ն˿��Ѿ��ر�,�������������Ժ���netstat -an�鿴���ض˿�"

echo ��������˳� 
pause>nul