@echo off
title    添加 IPv6 路由
::color f0
mode con cols=38 lines=20
echo.&echo.&echo  === 添加 IPv6 路由 ===&echo.&echo.

set /p host="    VPNname:"

set mainpath=%systemroot%\temp
if not exist %mainpath% @mkdir %mainpath%
netsh interface ipv6 show address %host% > %mainpath%\ipv6.addr
setlocal enabledelayedexpansion
for /f "delims=" %%i in (%mainpath%\ipv6.addr) do (
        set /a n+=1
        if !n!==1 set var=%%i
)
echo %var% > %mainpath%\ipv6.addr
for /f "tokens=1,2 delims= " %%i in (%mainpath%\ipv6.addr) do set ipv6=%%j
route add ::/0 %ipv6%
del /q %mainpath%\ipv6.addr

echo.&echo.
echo     设置完毕，打开 http://ipv6-test.com 
echo     测试是否支持IPv6
echo.
echo     按任意键退出
pause>nul

