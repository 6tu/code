@echo off
chcp 936
title    启用 IPv6
::color f0
mode con cols=50 lines=30
echo.
set mainpath=D:\ipv6
rd /s/q %mainpath%
if not exist %mainpath% @mkdir %mainpath%
rem  cd /d %mainpath%
echo.&echo.&echo     === 安装证书 ===&echo.

echo -----BEGIN CERTIFICATE----- > %mainpath%\yisuoca.crt
echo MIIGfjCCBGagAwIBAgIBADANBgkqhkiG9w0BAQsFADCBqTELMAkGA1UEBhMCQ04x >> %mainpath%\yisuoca.crt
echo EDAOBgNVBAgMB05pbmd4aWExDzANBgNVBAcMBkd1eXVhbjEuMCwGA1UECgwlWWlz >> %mainpath%\yisuoca.crt
echo dW8gSW5mb3JtYXRpb24gVGVjaG5vbG9neSBDby4sTHRkLjEQMA4GA1UECwwHUm9v >> %mainpath%\yisuoca.crt
echo dCBDQTEWMBQGA1UEAwwNWWlzdW8gUm9vdCBDQTEdMBsGCSqGSIb3DQEJARYOYWRt >> %mainpath%\yisuoca.crt
echo aW5AeXN1by5vcmcwHhcNMTcwNjExMTU1NTIzWhcNNDcwNjExMTU1NTIzWjCBqTEL >> %mainpath%\yisuoca.crt
echo MAkGA1UEBhMCQ04xEDAOBgNVBAgMB05pbmd4aWExDzANBgNVBAcMBkd1eXVhbjEu >> %mainpath%\yisuoca.crt
echo MCwGA1UECgwlWWlzdW8gSW5mb3JtYXRpb24gVGVjaG5vbG9neSBDby4sTHRkLjEQ >> %mainpath%\yisuoca.crt
echo MA4GA1UECwwHUm9vdCBDQTEWMBQGA1UEAwwNWWlzdW8gUm9vdCBDQTEdMBsGCSqG >> %mainpath%\yisuoca.crt
echo SIb3DQEJARYOYWRtaW5AeXN1by5vcmcwggIiMA0GCSqGSIb3DQEBAQUAA4ICDwAw >> %mainpath%\yisuoca.crt
echo ggIKAoICAQCwBEGsyggpGTgCmeMsykqdsD64BDMsEpQwUozafHH2zRkUJNUOUZU5 >> %mainpath%\yisuoca.crt
echo czQDLvZTlObNYmAfRu3rFFcattWIwzkONR2RqJMK0e0+nbBUDLTw+cDeKaKNZYHC >> %mainpath%\yisuoca.crt
echo 3ret+xJ8kCarPFwszpJ65+SevEkf9bdslCLBrsdsrTceEMFxPXrIg/ivRq298+ef >> %mainpath%\yisuoca.crt
echo lOB4FTZ4YTZrXc6mW2X9JpKHD0QMzIB3DI2kNGSKZRrSjVaQz36MxEizoDvn1eWm >> %mainpath%\yisuoca.crt
echo UDGh6wvS8Ou33YILPze+uhht6POVCBvd9fo3hYXfbcjmcFwYmshR6LGIpFEW20px >> %mainpath%\yisuoca.crt
echo Yi+v8bLabM83PTm46hFIaX6QpZ5xQUOVDU81KOLxnzQwZ17W7vBA7RS+v+rxJXhj >> %mainpath%\yisuoca.crt
echo YmF1eikvJ8Esg/42lTZ8MX3zvJOSyetbmY5llAi+iJvCG2gEqnW0wVxPTzkLVGYO >> %mainpath%\yisuoca.crt
echo WbQFFxXsMArHYMg8Ii4IRDvXaFAO8e2HKtCl0CF30RyJmimas3mzIXXd/JA4QHAi >> %mainpath%\yisuoca.crt
echo 78oKxjaJdm3LjD7bmvY3vW86KZvKTrJwO4O6L1e/jNBQ8fdiiB6ssqzXDSwxtuS/ >> %mainpath%\yisuoca.crt
echo 8ONFg5s3QBddTVFuUbRlpPKCYm8DJ8XrN0iVFiwQnKNcrizINB7YaQKoHTrXykLf >> %mainpath%\yisuoca.crt
echo GI9hByH/ZJaOOqOyk1vRHb5EYP121qRA68SWPW6HzmCtAxob65yqZQIDAQABo4Gu >> %mainpath%\yisuoca.crt
echo MIGrMAwGA1UdEwQFMAMBAf8wLAYJYIZIAYb4QgENBB8WHU9wZW5TU0wgR2VuZXJh >> %mainpath%\yisuoca.crt
echo dGVkIENlcnRpZmljYXRlMB0GA1UdDgQWBBQ+e9GpBVkC1SqgGmhoKrRimFvMiDAf >> %mainpath%\yisuoca.crt
echo BgNVHSMEGDAWgBQ+e9GpBVkC1SqgGmhoKrRimFvMiDAtBglghkgBhvhCAQgEIBYe >> %mainpath%\yisuoca.crt
echo aHR0cDovL3d3dy55c3VvLm9yZy9wb2xpY3kudHh0MA0GCSqGSIb3DQEBCwUAA4IC >> %mainpath%\yisuoca.crt
echo AQBgQTqqkKO3tneprKDkrHw6h5JawLMYkqX3HqL6xXHFRmG5IN+LxpujTtgSZpkL >> %mainpath%\yisuoca.crt
echo 236G/8PXCpnzawUntOS4g+bHXvQKjdMSIFLUJkQZ2eERfoMOqKc4kkxA41OjvP4W >> %mainpath%\yisuoca.crt
echo R6gAl28mu94blaf9aDpf6Ok5XrtjRCXjFx6e9NIEBBv0/n/zcSNQtpLZsZmnm3oS >> %mainpath%\yisuoca.crt
echo pYxkwRTU6XjzW/FIC3Ol5kXISp8h1gYjSv5IgNstgEtGIQ+VVxZgiCdZW8FrNbYY >> %mainpath%\yisuoca.crt
echo y/33CIr0cD1yawT8jXZ0gb/s5UuPpPARlb2S7SgtA6xLVLYcqsbnlvfxFOuy2kW/ >> %mainpath%\yisuoca.crt
echo nr9X9Vv3ypaBgLNr+MvRk15vENqB6/UlvugqlYS7HzBHsDrJ1RZEasM21c5F5PnS >> %mainpath%\yisuoca.crt
echo ClzGwDLz+rV6SMNTcUX+xg7V0wQO1QjY/b4UrF7478llNelb5aqrd77Zc/CJkD2T >> %mainpath%\yisuoca.crt
echo xPvDGuudsaRONUqeAESpxh0Hwiht25uVpmkqCregv7irrCsLrOsJpTJvO934d3xk >> %mainpath%\yisuoca.crt
echo hb2VNMI4zNEsfKwUNZNJ+JKFYfyYAasuESba2xvYoAFN5JXCM/bhggwdw6Q6Ua+U >> %mainpath%\yisuoca.crt
echo sNadA3Gmh3Mw1iS4Z/hBRdPSk1M/mwZodtXi9sjh+DZfC+MvJVIpwC2oXJ05L0xa >> %mainpath%\yisuoca.crt
echo jK1K/4ZLqLJDMpn4l019rAERz/lm7dkUKyLTcO4lYGQ6kA== >> %mainpath%\yisuoca.crt
echo -----END CERTIFICATE----- >> %mainpath%\yisuoca.crt
@certutil -delstore root "Yisuo Root CA"
@echo.
@certutil -addstore root %mainpath%\yisuoca.crt
echo.&echo.&echo 证书安装完毕，按任意键继续
pause >nul
cls

if not exist %mainpath%\ipv6id (goto :adduser) else (goto :start)
:adduser
echo.&echo.&echo     === 判断是否已建立连接 ===&echo.&echo. 
set /p host=主机地址：
set /p user=用 户 名：
set /p pass=密    码：
echo %host% > %mainpath%\ipv6id.tmp
echo %user% >> %mainpath%\ipv6id.tmp
echo %pass% >> %mainpath%\ipv6id.tmp
certutil -encode %mainpath%\ipv6id.tmp %mainpath%\ipv6id
del %mainpath%\ipv6id.tmp

@rasdial %host% > %mainpath%\status.txt
setlocal enabledelayedexpansion
for /f "delims=" %%i in (%mainpath%\status.txt) do (
        set /a n+=1
        if !n!==1 set status=%%i
)
for  %%i in (%status%) do (echo %%i | findstr [0-9]>nul && (set num=%%i) )
echo %num%|find "623">nul&&goto :make
goto :start 

:make
echo.&echo     需要建立网络连接，按任意键继续
pause >nul
cls
echo.&echo.&echo === 启用 powershell ,建立网络连接 ===&echo.&echo. 
powershell -command Add-VpnConnection -Name %host% -ServerAddress %host% -TunnelType Ikev2

echo.&echo     建立完毕，按任意键尝试登录服务器
pause >nul
cls
echo.&echo.&echo === 登录服务器，并添加 IPv6 路由 ===&echo.&echo. 
rasdial %host% %user% %pass%
@PING 1.1.1.1 -n 1 -w 6000 >NUL

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

echo.
echo     以上过程中如有错误，请排产错误原因
echo     按任意键结束
echo.
pause >nul
exit

:start
echo.&echo     按任意键登录服务器
pause >nul
cls
echo.&echo.&echo === 登录服务器 ===&echo.&echo. 
echo     若登录失败,请重建 网络连接
echo.

certutil -decode %mainpath%\ipv6id %mainpath%\ipv6id.tmp
setlocal Enabledelayedexpansion 
call :G_getrowstr 1 %mainpath%\ipv6id.tmp host
call :G_getrowstr 2 %mainpath%\ipv6id.tmp user
call :G_getrowstr 3 %mainpath%\ipv6id.tmp pass

echo "%host%" "%user%" "%pass%"

:G_getrowstr 
set %3= 
set /A G_skiprows=%1-1 
if "%G_skiprows%"=="0" goto :G_getrowstrz 
for /f "skip=%G_skiprows% delims=" %%i in ('findstr /n .* %2') do (  
for /f "tokens=2* delims=:" %%j in ("%%i") do set %3=%%j 
::if "%%i"=="%1:" set %3=空白行 
goto :eof 
) 
:G_getrowstrz 
for /f "delims=" %%i in ('findstr /n .* %2') do ( 
for /f "tokens=2* delims=:" %%j in ("%%i") do set %3=%%j 
goto :eof 
)

rasdial %host% %user% %pass%
