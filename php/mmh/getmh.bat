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
ping -n 1 119.29.23.116 >%mhdir%\ping.txt
for /f %%i in ('find "来自 119.29.23.116 的回复" %mhdir%\ping.txt') do set sf=%%i
if %sf%==来自       echo 互联网连接正常!
if %sf%==---------- (
    echo 互联网连接异常!请检查你的网络设置是否正确!
    pause>nul
    del /q %mhdir%\ping.txt
    exit
)

@echo.
@echo 网站内容一般在美东时间临晨更新
tzutil /s "UTC-08"
for /f "tokens=1-3 delims=/- " %%1 in ("%date%")do set/a y=%%1,m=1%%2-100,d=1%%3-100&call set date=%%y%%-%%m%%-%%d%%
rem 日期前面有0  for /f "tokens=1-3 delims=/- " %%1 in ("%date%")do set date=%%1-%%2-%%3
tzutil /s "%ctz%"
echo. && set /p date=请输入待下载文件的日期(默认%date%):

set fn=%date%-t.zip
set b64fn=p7m_%fn%.b64
set emlfn=%b64fn%.eml
set zipfn=%b64fn%.zip

cd /d %mhdir%
echo. && echo. ............ 正在下载 %fn%
ssl\wget -qO %mhdir%\mmh.html http://ysuo.org/mmh/getmh.php?name=%date%-t.zip

for /f %%i in ('find "获取数据无效" %mhdir%\mmh.html') do set mmhupdate=%%i
if %mmhupdate%==获取数据无效，当前数据类型是 (
    echo. && echo 远端文件获取错误!可能是填写的文件日期错误
    pause>nul
    del /q %mhdir%\ping.txt
    del /q %mhdir%\mmh.html
    exit
)

cls
echo. && echo. && echo  远端文件更新完毕，2 秒钟后下载文件
echo. && ping -n 1 127.0>nul
ssl\wget --show-progress -q http://oold3s5tj.bkt.clouddn.com/mhdata/p7m_%date%-t.zip.b64.zip

echo. && echo ............ 正在还原文件内容
ssl\7z.exe -o%mhdir% e %mhdir%%zipfn% -y >nul
ssl\openssl smime  -decrypt -in %mhdir%\%emlfn% -inkey %mhdir%\cert\mh.key  -out %mhdir%\mhdata\%b64fn%
ssl\base64 -d %mhdir%\mhdata\%b64fn% %mhdir%\mhdata\%fn%
ssl\7z.exe -o%mhdir%\mhdata\%date%-t x  %mhdir%\mhdata\%fn% -y >nul

ping -n 1 127.0>nul
del /q %mhdir%\ping.txt
del /q %mhdir%\mmh.html
del /q %mhdir%%b64fn%*
del /q %mhdir%\mhdata\%b64fn%
::del /q %mhdir%\mhdata\%fn%*


cd /d %mhdir%\mhdata\%date%-t
::start .
start /max "C:\Program Files (x86)\Google\Chrome\Application\chrome.exe" %mhdir%\mhdata\%date%-t\%date%-t.html

::echo %date:~,4%-%date:~5,2%-%date:~8,2%
::set yy=%date:~0,4%  
::set mm=%date:~5,2%  
::set dd=%date:~8,2% 
::set Thh=%TIME:~0,2%  
::set Thh=%Thh: =0%  
::set Tmm=%TIME:~3,2%  
::set Tss=%TIME:~6,2%  
