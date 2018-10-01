#!/system/bin/sh

#判断CPU是否是arm的
arm=arm
cpu=`getprop ro.product.cpu.abi`
if [[ $cpu == *$arm* ]];then
    echo $cpu
else
    echo " This shell applies only to arm "
    exit
fi

#检测网络的联通性
pingres=`ping -c 1 119.29.23.116 | sed -n '/64 bytes from/p'`
if [[ -z $pingres ]];
then
    echo 'network error'
    exit
fi
clear

mmhpath=/sdcard/mmh
mmhdata=$mmhpath/mhdata
databin=/data/local/bin
#复制执行文件到指定目录，并设置属性0755

#su
#mount -o remount /data
#mount -o remount /system
#cd $mmhpath/bin/
#cp unzip openssl wget base64 /system/bin/
#cd /system/bin/
#chmod 0755 unzip openssl wget base64

test -d $databin         || mkdir -p $databin
test -f $databin/unzip   || cp -rf $mmhpath/bin/unzip   $databin/
test -f $databin/openssl || cp -rf $mmhpath/bin/openssl $databin/
test -f $databin/wget    || cp -rf $mmhpath/bin/wget    $databin/
test -f $databin/base64  || cp -rf $mmhpath/bin/base64  $databin/
cd $databin
chmod 0755 unzip openssl wget base64
chmod -R 0755 $databin

#月份前面有 0, `date +%Y%m%d`
#月份前面不带 0, `date +'%Y-%-m-%-d'`
#从网络中获取日期，而不是更改本地的日期
echo && echo " 一般在北京时间14:00左右更新内容"
xdate=`$databin/wget --no-check-certificate -qO- https://ysuo.org/mmh/getmh.php?date=date`
echo
read -t 10 -p " 请输入文件的发布日期，默认为:" -i $xdate  xdate
read -t 5  -p " 是否下载 $xdate 的文件？     " -i "Yes" REPLY

if [ $REPLY != "Yes" ];then
	echo " 已取消下载文件 "
    exit 1
fi

#设置文件名
fn=$xdate-t.zip
b64fn=p7m_$fn.b64
emlfn=$b64fn.eml
zipfn=$b64fn.zip

cd $mmhpath
test -d $mmhdata/$xdate-t || mkdir -p $mmhdata/$xdate-t
$databin/wget --no-check-certificate -qO $mmhpath/mmh.html https://ysuo.org/mmh/getmh.php?name=$fn

grep "获取数据无效" $mmhpath/mmh.html > /dev/null
if [ $? -eq 0 ]; then

    echo && echo "远端文件获取错误!可能是填写的文件日期错误"
    echo && ping -c 2 127.0.0.1 > /dev/null 2>&1
	rm  $mmhpath/mmh.html
	exit
else
    #clear
    echo && echo "远端文件更新完毕，2 秒钟后跳转到下载链接"
    ping -c 1 127.0.0.1 > /dev/null 2>&1
    $databin/wget -q -P $mmhpath/ http://oold3s5tj.bkt.clouddn.com/mhdata/$zipfn
fi

echo && echo ............ 正在还原文件内容
echo
$databin/unzip -o -d $mmhpath/ $zipfn
$databin/openssl smime -decrypt -in $mmhpath/$emlfn -inkey $mmhpath/cert/mh.key -out $mmhpath/$b64fn
$databin/base64 -d $mmhpath/$b64fn >  $mmhdata/$fn
#$databin/openssl base64 -d -in $mmhpath/$b64fn -out $mmhdata/$fn
$databin/unzip -o -d $mmhdata/$xdate-t $mmhdata/$fn > /dev/null 2>&1

rm -rf $mmhpath/mmh.html
rm -rf $mmhpath/ping.log
rm -rf $mmhpath/$b64fn
rm -rf $mmhpath/$emlfn*
rm -rf $mmhpath/$zipfn*
#rm -rf $mmhdata/$fn

#adb shell am start -n {包(package)名}/{包(package)名}.{活动(activity)名称} file:/// 或者 http://
#{包(package)名}.{活动(activity)名称}可以从AndroidManifest.xml的文件中得到
am start -n com.android.chrome/com.google.android.apps.chrome.Main file:///$mmhdata/$xdate-t/$xdate-t.html

