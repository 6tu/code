@echo off
color 3b
cls
@title �����ļ�
set mhdir=%~dp0
cd /d %mhdir%
for /f "tokens=1 delims==" %%a in ('tzutil /g') do @set ctz=%%a

@echo.
@echo                ���Ի������Ƿ���������
@echo  ��ǰʱ�� %date%,%time%,%ctz%
@echo ======================================================
@echo.
ping 119.29.23.116 >%mhdir%\a.txt
for /f %%i in ('find "���� 119.29.23.116 �Ļظ�" %mhdir%\a.txt') do set sf=%%i
if %sf%==����       echo ������(����)��������!
if %sf%==----------  echo ������(����)�����쳣!����������������Ƿ���ȷ!
@echo.
@echo ���ʱ��Ϊ����ʱ�䣬��������һ��������ʱ���ٳ�����
tzutil /s "UTC-08"
for /f "tokens=1-3 delims=/- " %%1 in ("%date%")do set/a y=%%1,m=1%%2-100,d=1%%3-100&call set date=%%y%%-%%m%%-%%d%%
rem ����ǰ����0  for /f "tokens=1-3 delims=/- " %%1 in ("%date%")do set date=%%1-%%2-%%3
tzutil /s "%ctz%"
echo. && set /p date=�������ļ���������(%date%):
echo. && echo  ���س��������� %date% ���ļ����Ƿ������
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
echo  Զ���ļ�������ϣ�3 ���Ӻ���ת����������
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
