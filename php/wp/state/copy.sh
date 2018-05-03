#!/bin/bash

wwwroot="/home/wwwroot/default"

rm -rf $wwwroot/201*
rm -rf $wwwroot/page
rm -rf $wwwroot/category
rm -rf $wwwroot/index.html

cp -r $wwwroot/tmphtml/201* $wwwroot/
cp -r $wwwroot/tmphtml/page $wwwroot/
cp -r $wwwroot/tmphtml/category $wwwroot/
cp -r $wwwroot/tmphtml/index.html $wwwroot/
cp -r $wwwroot/index.html $wwwroot/index_cn.html

chown -R www:www $wwwroot/201*
chown -R www:www $wwwroot/page
chown -R www:www $wwwroot/category
chown www:www $wwwroot/index.html

rm -rf $wwwroot/tmphtml/*

sed -i "s/index.php?p=306/non-repeat\/index.html/g" `grep -rl "index.html" $wwwroot`
sed -i "s/index.php?p=9/ikev2-vpn\/index.html/g" `grep -rl "index.html" $wwwroot`
sed -i "s/href=\"..\/..\/..\/..\/ip\/index.html/href=\"https:\/\/ys138.win\/ip\//g" `grep -rl "index.html" $wwwroot`
sed -i "s/href=\"..\/..\/ip\/index.html/href=\"https:\/\/ys138.win\/ip\//g" `grep -rl "index.html" $wwwroot`

sed -i "s/href='wp-content\/themes\/dmeng2.0\/css/href='https:\/\/yisuo.b0.upaiyun.com\/wp-content\/themes\/dmeng2.0\/css/g" "$wwwroot/index_cn.html"
sed -i "s/src='wp-includes\/js\/jquery/src='https:\/\/yisuo.b0.upaiyun.com\/wp-includes\/js\/jquery/g" "$wwwroot/index_cn.html"
sed -i "s/src='wp-content\/themes\/dmeng2.0\/js/src='https:\/\/yisuo.b0.upaiyun.com\/wp-content\/themes\/dmeng2.0\/js/g" "$wwwroot/index_cn.html"

cd /root/
rm -rf /root/2017
wget -r -p -np -k https://ys138.win/wp-content/uploads/2017/07/
mv ys138.win/wp-content/uploads/2017/ /root/
rm -rf /root/ys138.win
rm -rf /root/2017/index.html*
cp -r 2017 $wwwroot/wp-content/uploads/
