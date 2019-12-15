@echo off
color 1f 
title 您正在使用一键屏蔽危险端口和服务 by et
echo 您正在使用一键屏蔽危险端口和服务
echo "正在帮您关闭这些危险端口，请稍等"
echo “正在开启Windows防火墙服务”
net start MpsSvc
echo ”正在帮您开启Windows防火墙自启动“
sc config MpsSvc start= auto
echo ”正在启用防火墙“
netsh advfirewall set allprofiles state on
echo "正在帮您屏蔽端口...."
echo. 
echo. 
echo. 
echo 正在屏蔽135端口 请稍候… 
netsh advfirewall firewall delete rule name = "Disable port 135 - TCP"
netsh advfirewall firewall add rule name = "Disable port 135 - TCP" dir = in action = block protocol = TCP localport = 135
echo. 
netsh advfirewall firewall delete rule name = "Disable port 135 - UDP"
netsh advfirewall firewall add rule name = "Disable port 135 - UDP" dir = in action = block protocol = UDP localport = 135
echo. 
echo 正在屏蔽137端口 请稍候… 
netsh advfirewall firewall delete rule name = "Disable port 137 - TCP"
netsh advfirewall firewall add rule name = "Disable port 137 - TCP" dir = in action = block protocol = TCP localport = 137
echo. 
netsh advfirewall firewall delete rule name = "Disable port 137 - UDP"
netsh advfirewall firewall add rule name = "Disable port 137 - UDP" dir = in action = block protocol = UDP localport = 137
echo. 
echo 正在屏蔽138端口 请稍候… 
netsh advfirewall firewall delete rule name = "Disable port 138 - TCP"
netsh advfirewall firewall add rule name = "Disable port 138 - TCP" dir = in action = block protocol = TCP localport = 138
echo. 
netsh advfirewall firewall delete rule name = "Disable port 138 - UDP"
netsh advfirewall firewall add rule name = "Disable port 138 - UDP" dir = in action = block protocol = UDP localport = 138
echo. 
echo 正在屏蔽139端口 请稍候… 
netsh advfirewall firewall delete rule name = "Disable port 139 - TCP"
netsh advfirewall firewall add rule name = "Disable port 139 - TCP" dir = in action = block protocol = TCP localport = 139
echo. 
netsh advfirewall firewall delete rule name = "Disable port 139 - UDP"
netsh advfirewall firewall add rule name = "Disable port 139 - UDP" dir = in action = block protocol = UDP localport = 139
echo. 
echo 正在关闭445端口 请稍候… 
netsh advfirewall firewall delete rule name = "Disable port 445 - TCP"
netsh advfirewall firewall add rule name = "Disable port 445 - TCP" dir = in action = block protocol = TCP localport = 445
echo. 
netsh advfirewall firewall delete rule name = "Disable port 445 - UDP"
netsh advfirewall firewall add rule name = "Disable port 445 - UDP" dir = in action = block protocol = UDP localport = 445
echo.

echo "危险端口已经用Windows防火墙屏蔽成功"

echo.
echo ----------------
echo “正在关闭Workstation（LanmanWorkstation）服务”
sc stop LanmanWorkstation
sc config LanmanWorkstation start= disabled

echo.
echo ----------------
echo “正在关闭Server（LanmanServer）服务”
sc stop LanmanServer
sc config LanmanServer start= disabled

echo.
echo ----------------
echo “正在关闭TCP/IP NetBIOS Helper（lmhosts）共享服务”
sc stop lmhosts
sc config lmhosts start= disabled

echo.
echo ----------------
echo “正在关闭Distributed Transaction Coordinator（MSDTC）共享服务”
sc stop MSDTC
sc config MSDTC start= disabled

echo.
echo ----------------
echo “正在关闭NetBT服务”
sc stop NetBT
sc config NetBT start= disabled

echo.
echo ----------------
reg add "hklm\System\CurrentControlSet\Services\NetBT\Parameters" /v "SMBDeviceEnabled" /t reg_dword /d "0" /f
reg add "hklm\SOFTWARE\Microsoft\Ole" /v "EnableDCOM" /t reg_sz /d "N" /f
reg add "hklm\SOFTWARE\Microsoft\Rpc" /v "DCOM Protocols" /t reg_multi_sz /d "" /f

echo.
echo ----------------
echo "恭喜您，危险端口已经关闭,请重新启动电脑后用netstat -an查看本地端口"

echo 按任意键退出 
pause>nul