#!/bin/bash

# vim index.php   $uri .= $_SERVER["REQUEST_URI"];
clear
www=/root/www
cd $www
echo "" && echo "============= Delete Language packs =============" && echo ""
rm -rf $www/help
rm -rf $www/htdocs
cp -r /opt/lampp/htdocs $www/
cd $www/htdocs/dashboard
cp -r zh_cn/* ./
rm -rf fr es de hu jp it pl pt_br ru tr ro ur zh_tw zh_cn

echo "" && echo "============= Change path =============" && echo ""
find $www/htdocs -name "*.html" -exec sed -i 's/href="\/\//href="https:\/\//g' {} \;
find $www/htdocs -name "*.html" -exec sed -i 's/src="\/\//src="https:\/\//g' {} \;
# 基础修改为相对路径
find $www/htdocs -name "*.html" -exec sed -i 's/href="\//href="..\//g' {} \;
find $www/htdocs -name "*.html" -exec sed -i 's/src="\//src="..\//g' {} \;
find $www/htdocs/dashboard/docs -name "*.html" -exec sed -i 's/..\/dashboard\//..\/..\/dashboard\//g' {} \;
# 对特殊路径做修改
find $www/htdocs/dashboard/docs -name "*.html" -exec sed -i 's/href="..\/applications.html/href="..\/..\/applications.html/g' {} \;
find $www/htdocs/ -name "*.html" -exec sed -i 's/..\/..\/phpmyadmin\//\/phpmyadmin\//g' {} \;
find $www/htdocs/ -name "*.html" -exec sed -i 's/..\/phpmyadmin\//\/phpmyadmin\//g' {} \;
find $www/htdocs -name "*.html" -exec sed -i 's/\/zh_cn\//\//g' {} \;
# 去掉 dashboard 目录，进一步做相对路径
find $www/htdocs -name "*.html" -exec sed -i 's/..\/dashboard\///g' {} \;
# 恢复 //
find $www/htdocs -name "*.html" -exec sed -i 's/href="https:\/\//href="\/\//g' {} \;
find $www/htdocs -name "*.html" -exec sed -i 's/src="https:\/\//src="\/\//g' {} \;

# 修改 CSS 中图片路径
find $www/htdocs -name "*.css" -exec sed -i 's/\/dashboard\/images/..\/images/g' {} \;

echo "" && echo "============= Copy dashboard/* to /help =============" && echo ""
mkdir $www/help
cp -r $www/htdocs/applications.html $www/help
cp -r $www/htdocs/bitnami.css $www/help
cp -r $www/htdocs/dashboard/* $www/help

find $www/help -name "*.html" -exec sed -i 's/href="..\/applications.html/href="applications.html/g' {} \;
find $www/help/docs -name "*.html" -exec sed -i 's/href="..\/..\/applications.html/href="..\/applications.html/g' {} \;

rm -rf $www/htdocs
