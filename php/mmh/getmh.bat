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
ping -n 1 119.29.23.116 >%mhdir%\ping.txt
for /f %%i in ('find "���� 119.29.23.116 �Ļظ�" %mhdir%\ping.txt') do set sf=%%i
if %sf%==����       echo ��������������!
if %sf%==---------- (
    echo �����������쳣!����������������Ƿ���ȷ!
    pause>nul
    del /q %mhdir%\ping.txt
    exit
)

@echo.
@echo ��վ����һ��������ʱ���ٳ�����
tzutil /s "UTC-08"
for /f "tokens=1-3 delims=/- " %%1 in ("%date%")do set/a y=%%1,m=1%%2-100,d=1%%3-100&call set date=%%y%%-%%m%%-%%d%%
rem ����ǰ����0  for /f "tokens=1-3 delims=/- " %%1 in ("%date%")do set date=%%1-%%2-%%3
tzutil /s "%ctz%"
echo. && set /p date=������������ļ�������(Ĭ��%date%):

set fn=%date%-t.zip
set b64fn=p7m_%fn%.b64
set emlfn=%b64fn%.eml
set zipfn=%b64fn%.zip

cd /d %mhdir%
echo. && echo. ............ �������� %fn%
ssl\wget -qO %mhdir%\mmh.html http://ysuo.org/mmh/getmh.php?name=%date%-t.zip

for /f %%i in ('find "��ȡ������Ч" %mhdir%\mmh.html') do set mmhupdate=%%i
if %mmhupdate%==��ȡ������Ч����ǰ���������� (
    echo. && echo Զ���ļ���ȡ����!��������д���ļ����ڴ���
    pause>nul
    del /q %mhdir%\ping.txt
    del /q %mhdir%\mmh.html
    exit
)

cls
echo. && echo. && echo  Զ���ļ�������ϣ�2 ���Ӻ������ļ�
echo. && ping -n 1 127.0>nul
ssl\wget --show-progress -q http://oold3s5tj.bkt.clouddn.com/mhdata/p7m_%date%-t.zip.b64.zip

echo. && echo ............ ���ڻ�ԭ�ļ�����
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
