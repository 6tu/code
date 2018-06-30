#!/bin/bash


# 这里需要设定本地网站根目录
wwwroot="/var/www"

# 脚本所在目录
basepath=$(cd `dirname $0`; pwd)
cd $basepath

# 检测是否输入网址以及输入的网址是否正确,同时提取网址的 host
if [ ! -n "$1" ] ;then
    clear
    echo ""
    echo ""
    echo "    没有输入网址，输入为空"
    echo ""
    echo ""
    echo "    当前指令的格式是 $basepath/$0 URL DOCUMENT_ROOT"
    echo ""
    exit
fi

url=$1
str="://"
echo $url
if [[ $url =~ $str ]] ;then
    echo ""
    echo ""
    echo "    输入的网址为 $url"
    host=$(echo $url | awk -F'[|/]' '{print $3}')
    echo ""
else
    echo ""
    echo ""
    echo "    输入网址格式错误，设定为 http:// 或者 https:// 开头"
    echo ""
    exit
fi

# 检测本地网站的根目录是否存在，不存在则建立
if [ ! -n "$2" ] ;then
    clear
    echo ""
    echo ""
    echo "    没有没有指定本地网站根目录，目录被设定为默认值 $wwwroot"
    echo ""
else
    wwwroot="$2"
fi

tmp_html=$wwwroot/tmphtml
if [ ! -d "$tmp_html" ]; then
    mkdir -p $tmp_html
fi


# 检测 $basepath/$host 是否存在，存在则删除
if [ -d "$basepath/$host" ] ;then
    rm -rf $basepath/$host
fi


# 获取网站的HTML镜像
echo ""
echo ""
echo "==============  服务器端务必更新 index.html 文件  ================"
echo ""
echo ""

# wget -q -P "/opt/www" "$url"
# sed -i "s/http:\/\/ys138.win\///g" `grep -rl "index.html" /opt/www`
# sed -i "s/https:\/\/ys138.win\///g" `grep -rl "index.html" /opt/www`

echo ""
echo ""
echo "======================== 抓取站点: $url  ========================="
echo ""
echo ""

wget -r -p -np -k --no-check-certificate --restrict-file-names=nocontrol --reject-regex='[\?]' -X /soft/,/wp-json/,feed/ "$url"

clear
echo "======================= $url 下载成功  =========================="
echo ""
echo ""
echo "================== 更换 *.js 和 *.css 文件名   =================="
echo ""
echo ""

find $basepath/$host -type f -name "*.css?*" |
while read name; do
echo $name
newname=$(echo $name | awk -F'[|?]' '{print $1}')
echo  $newname
mv $name $newname
done

find $basepath/$host -type f -name "*.js?*" |
while read name; do
echo $name
newname=$(echo $name | awk -F'[|?]' '{print $1}')
echo  $newname
mv $name $newname
done

echo ""
echo ""
echo "============  删除所有的index.html?* 文件和feed 目录 ============="
echo ""
echo ""

find $basepath/$host/ -name "index.html?*" -exec rm -rf {} \;
find $basepath/$host/ -name "feed" -exec rm -rf {} \;

echo ""
echo ""
echo "========   替换所有  index.html 文件中的 index.php?p   =========="
echo ""
echo ""

sed -i 's/index.html?p=/index.php?p=/g' `grep -rl "index.html" $basepath/$host`

echo ""
echo ""
echo "=====================  更换 URL 为相对链接 ======================"
echo ""
echo ""

sed -i 's/href=\"http:\/\/$host\/201/href="..\/..\/201/g' `grep -rl "index.html" $basepath/$host/page`
sed -i 's/href=\"http:\/\/$host\//href=\"..\/..\//g' `grep -rl "index.html" $basepath/$host/page`
sed -i "s/href='http:\/\/$host\//href='..\/..\//g" `grep -rl "index.html" $basepath/$host/page`
sed -i "s/src='http:\/\/$host\//src='..\/..\//g" `grep -rl "index.html" $basepath/$host/page`
sed -i 's/href=\"https:\/\/$host\/201/href="..\/..\/201/g' `grep -rl "index.html" $basepath/$host/page`
sed -i 's/href=\"https:\/\/$host\//href=\"..\/..\//g' `grep -rl "index.html" $basepath/$host/page`
sed -i "s/href='https:\/\/$host\//href='..\/..\//g" `grep -rl "index.html" $basepath/$host/page`
sed -i "s/src='https:\/\/$host\//src='..\/..\//g" `grep -rl "index.html" $basepath/$host/page`

sed -i "s/http:\/\/$host\//..\/..\/..\/..\//g" `grep -rl "index.html" $basepath/$host/2011`
sed -i "s/http:\/\/$host\//..\/..\/..\/..\//g" `grep -rl "index.html" $basepath/$host/2016`
sed -i "s/http:\/\/$host\//..\/..\/..\/..\//g" `grep -rl "index.html" $basepath/$host/2017`
sed -i "s/http:\/\/$host\//..\/..\//g" `grep -rl "index.html" $basepath/$host/page`
sed -i "s/http:\/\/$host\///g" "$basepath/$host/index.html"
sed -i "s/https:\/\/$host\//..\/..\/..\/..\//g" `grep -rl "index.html" $basepath/$host/2011`
sed -i "s/https:\/\/$host\//..\/..\/..\/..\//g" `grep -rl "index.html" $basepath/$host/2016`
sed -i "s/https:\/\/$host\//..\/..\/..\/..\//g" `grep -rl "index.html" $basepath/$host/2017`
sed -i "s/https:\/\/$host\//..\/..\//g" `grep -rl "index.html" $basepath/$host/page`
sed -i "s/https:\/\/$host\///g" "$basepath/$host/index.html"

#sed -i "s/id=\"searchform\" action=\"..\/..\/\">/id=\"searchform\" action=\"https:\/\/$host\/\">/g" `grep -rl "index.html" $basepath/$host`
#sed -i "s/$host/ysuo.org/g" `grep -rl "index.html" $basepath/$host`
#sed -i "s/<a href=\"http:\/\/ysuo.org\">/<a href=\"http:\/\/$host\">/g" `grep -rl "index.html" $basepath/$host`

echo ""
echo ""
echo "=========================  打包 $host  =========================="
echo ""
echo ""

if [ -f $basepath/$host.zip ]; then
	rm $basepath/*.bak
	mv -f $basepath/$host.zip $basepath/$host.zip.bak
fi

zip -r -q $host.zip $host


read -s -n1 -p "是否同意复制HTML镜像到 $tmp_html/ ，如果同意请输入 yes ,按其它键则终止脚本"
echo "请输入: $REPLY"
if [[ ! $REPLY =~ "y" ]] ;then
	exit
fi


# 下载WP的XML文件，提取ID对应的URL保存到文件   复制文件到web目录，并精简网页
mkdir temp
php -f ./getwpxml.php
# wget -c -r -np -k --no-check-certificate -X /wp-includes/,/wp-content/themes/,/wp-json/ / -i $wwwroot/wpdb/new.txt
# wget -c -r -np -k --no-check-certificate -X /wp-includes/,/wp-content/themes/,/wp-json/ / -i $wwwroot/wpdb/page.txt

clear
echo ""
echo ""
echo "==== 复制相关文件到$tmp_html/,修改其属性为777,并精简网页  ======="
echo ""
echo ""
rm -rf $tmp_html/*
cp -r  $basepath/getid.php    $tmp_html/index.php
cp -r  $basepath/id-url-*.log $tmp_html/url.txt
cp -r  $basepath/samp.php     $tmp_html/samp.php
cp -r  $basepath/$host/*      $tmp_html/
chmod -R 0777                 $tmp_html

cd $tmp_html
php -f $tmp_html/samp.php $tmp_html

cd $wwwroot
find -type d|xargs chmod 755
find -type f|xargs chmod 644
chown -R www:www $tmp_html

clear
echo ""
echo ""
echo "======================= 所有指令执行完毕 ======================="
echo ""
echo ""


