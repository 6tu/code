@echo off
color 3b
cls
@title 下载文件
set mhdir=%~dp0
cd /d %mhdir%
for /f "tokens=1 delims==" %%a in ('tzutil /g') do @set ctz=%%a

@echo.
@echo                测试互联网是否正常连接
@echo  当前时间 %date%,%time%,%ctz%
@echo ======================================================
@echo.
ping 119.29.23.116 >%mhdir%\a.txt
for /f %%i in ('find "来自 119.29.23.116 的回复" %mhdir%\a.txt') do set sf=%%i
if %sf%==来自       echo 互联网(外网)连接正常!
if %sf%==----------  echo 互联网(外网)连接异常!请检查你的网络设置是否正确!
@echo.
@echo 变更时区为美西时间，更新内容一般在美西时间临晨发布
tzutil /s "UTC-08"
for /f "tokens=1-3 delims=/- " %%1 in ("%date%")do set/a y=%%1,m=1%%2-100,d=1%%3-100&call set date=%%y%%-%%m%%-%%d%%
rem 日期前面有0  for /f "tokens=1-3 delims=/- " %%1 in ("%date%")do set date=%%1-%%2-%%3
tzutil /s "%ctz%"
echo. && set /p date=请输入文件发布日期(%date%):
echo. && echo  按回车键将下载 %date% 的文件，是否继续？
echo. && pause>nul

set fn=%date%-t.zip
set b64fn=p7m_%date%-t.zip.b64
set emlfn=p7m_%date%-t.zip.b64.eml
set zipfn=p7m_%date%-t.zip.b64.zip

cd /d %mhdir%
del %mhdir%\getmh.php*
del %mhdir%\%zipfn%*
ssl\wget http://ysuo.org/mmh/getmh.php?name=%date%-t.zip >> mh.log
cls
echo.
echo  远端文件更新完毕，3 秒钟后跳转到下载链接
echo.
ping -n 3 127.0>nul
ssl\wget http://ysuo.org/mmh/mhdata/p7m_%date%-t.zip.b64.zip
ping -n 1 127.0>nul
ssl\7z.exe -o%mhdir% e %mhdir%%zipfn% -y
ssl\openssl smime  -decrypt -in %mhdir%\%emlfn% -inkey %mhdir%\cert\mh.key  -out %mhdir%\mhdata\%b64fn%
ssl\base64 -d %mhdir%\mhdata\%b64fn% %mhdir%\mhdata\%fn%
ssl\7z.exe -o%mhdir%\mhdata\%date%-t e %mhdir%\mhdata\%fn% -y

del /s/q %mhdir%\a.txt
del %mhdir%\getmh.php*
del %mhdir%%emlfn%
::del %mhdir%%zipfn%
del %mhdir%\mhdata\%b64fn%
del %mhdir%\mhdata\%fn%

cd /d %mhdir%\mhdata\%date%-t
::start .
ping -n 2 127.0>nul
start /max %mhdir%\mhdata\%date%-t\%date%-t.html

::echo %date:~,4%-%date:~5,2%-%date:~8,2%
::set yy=%date:~0,4%  
::set mm=%date:~5,2%  
::set dd=%date:~8,2% 
::set Thh=%TIME:~0,2%  
::set Thh=%Thh: =0%  
::set Tmm=%TIME:~3,2%  
::set Tss=%TIME:~6,2%  
