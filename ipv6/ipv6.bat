title    启用 IPv6
::color f0
mode con cols=42 lines=30
::==================本机IPv4 及MAC地址==============
@echo off
for /f "tokens=2 delims=:" %%i in ('ipconfig ^| findstr "IPv4"') do set localip=%%i
for /f "tokens=1,3 delims=," %%a in ('getmac /fo csv /v') do (
if %%a == "本地连接" set localmac=%%~b
)
@echo.
echo       本地连接的IP为%localip%
rem echo   本地连接的MAC为%localmac%

::==================外网 IPv4 地址==============
@echo off
set "URL=http://1suo.net"
>%temp%/download.vbs echo Set objDOM = WScript.GetObject(WScript.Arguments(0))
>>%temp%/download.vbs echo Do Until objDOM.ReadyState = "complete"
>>%temp%/download.vbs echo WScript.Sleep 100
>>%temp%/download.vbs echo Loop
>>%temp%/download.vbs echo WScript.Echo objDOM.DocumentElement.OuterText

for /f "tokens=1 delims=[]" %%a in ('cscript //nologo //e:vbscript %temp%/download.vbs "%URL%"') do (
    set "PublicIP=%%a"
)
@echo.
echo       局域网外网IP为 %PublicIP%
@echo.
::==================注册 IPv6 隧道==============
echo       注册 IPv6 地址
set "URL=http://tb.netassist.ua/autochangeip.php?l=zhongxiaolee@gmail.com&p=mj4HyBJWg9&ip=%PublicIP%"
for /f "tokens=1 delims=[]" %%a in ('cscript //nologo //e:vbscript %temp%/download.vbs "%URL%"') do (
    set "PublicIP=%%a"
)
netsh int ipv6 delete interface "IP6Tunnel"
netsh interface teredo set state disabled
netsh interface ipv6 add v6v4tunnel IP6Tunnel 10.31.2.2 62.205.132.12
netsh interface ipv6 add address IP6Tunnel 2a01:d0:ffff:5c0d::2
echo       添加 IPv6 网关
netsh interface ipv6 add route ::/0 IP6Tunnel 2a01:d0:ffff:5c0d::1

::==================关闭防火墙==============
@echo.
echo       正在关闭防火墙
@echo.
sc config MpsSvc start= demand
net stop MpsSvc
@echo.
pause

