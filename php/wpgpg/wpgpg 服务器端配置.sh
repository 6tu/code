#  wpgpg 服务器端配置

MASTERKEYID=wpgpg@ysuo.org
phpath=/opt/lampp
webroot=/var/www
plugins=${webroot}/wp-content/plugins
httpd="${phpath}/lampp reloadapache"
apacheuser=daemon



cd
# 安装 GnuPG 和 rng-tools
apt install -y libgpgme11-dev rng-tools man
yum install -y gpgme-devel rng-tools man

# 通过rng-tools自动补充熵池，加快GPG生产密钥对
rng -r /dev/urandom
rngd -r /dev/urandom

# 创建密钥对
gpg --gen-key

# 导出公钥和私钥：
gpg -a -o ~/.gnupg/pub.txt --export ${MASTERKEYID}
gpg -a -o ~/.gnupg/secret.txt --export-secret-keys ${MASTERKEYID}


# 安装php-gunpg
# 这条命令别运行 /opt/lampp/bin/pecl channel-update pecl.php.net
# /opt/lampp/bin/pecl install gnupg

test -d ${phpath}/src || mkdir -p ${phpath}/src
cd ${phpath}/src
wget http://pecl.php.net/get/gnupg-1.4.0.tgz
tar zxvf gnupg-1.4.0.tgz
cd gnupg-1.4.0
${phpath}/bin/phpize
./configure --with-php-config=${phpath}/bin/php-config
make
make test
make install
echo extension="gnupg.so" >> ${phpath}/etc/php.ini

${httpd}

# 安装 openpgp-php
test -d ${plugins} || mkdir -p ${plugins}
cd ${plugins}
rm -rf wpgpg
git clone https://github.com/trogau/wpgpg.git
cd wpgpg
# https://github.com/dryphp/openpgp.php
wget https://github.com/singpolyma/openpgp-php/archive/0.3.0.zip
unzip 0.3.0.zip
mv openpgp-php-0.3.0 openpgp-php
cd openpgp-php
## 做软链接
ln -s ${phpath}/bin/php /usr/local/bin/php
${phpath}/bin/composer install

cp -rf ~/.gnupg ${webroot}

find ${webroot} -type f -exec chmod 644 {} \;
find ${webroot} -type d -exec chmod 755 {} \;
chown -R ${apacheuser}:${apacheuser} ${webroot}

# 设置 wpgpg

#打开 phpmyadmin 的方法
#/opt/lampp/phpmyadmin/config.inc.php
#/opt/lampp/etc/extra/httpd-xampp.conf

# PGP_ENCRYPT_MODE == 'gpg' 时指定 $current_user->user_email 为密钥 email
#
# PGP_ENCRYPT_MODE == 'openpgp-php' 时 $public_key = file_get_contents('/var/www/.gnupg/pub.txt');
# 或者 给 wp_usermeta表中的meta_key增加 gpgpublickey 键，值设定为 ASCII 编码的公钥
# 使得下面的这句能查询到 gpgpublickey
# $public_key = get_user_meta($current_user->ID, 'gpgpublickey', true);

# include('upgpgkey.php');

# ob_start(); 后面增加 utf2html($str) 函数 ， 然后 $output = utf2html($output); 以增加中文支持

# header("Content-type: text/html; charset=UTF-8");

# UTF8转成HTML实体
# function utf2html($str)
# {
#     $ret = "";
#     $max = strlen($str);
#     $last = 0;
#     for ($i = 0;$i < $max;$i++){
#         $c = $str{$i};
#         $c1 = ord($c);
#         if ($c1 >> 5 == 6){
#             $ret .= substr($str, $last, $i - $last);
#             $c1 &= 31; # remove the 3 bit two bytes prefix
#             $c2 = ord($str{++$i});
#             $c2 &= 63;
#             $c2 |= (($c1 & 3) << 6);
#             $c1 >>= 2;
#             $ret .= "&#" . ($c1 * 0x100 + $c2) . ";";
#             $last = $i + 1;
#             }
#         elseif ($c1 >> 4 == 14){
#             $ret .= substr($str, $last, $i - $last);
#             $c2 = ord($str{++$i});
#             $c3 = ord($str{++$i});
#             $c1 &= 15;
#             $c2 &= 63;
#             $c3 &= 63;
#             $c3 |= (($c2 & 3) << 6);
#             $c2 >>= 2;
#             $c2 |= (($c1 & 15) << 4);
#             $c1 >>= 4;
#             $ret .= '&#' . (($c1 * 0x10000) + ($c2 * 0x100) + $c3) . ';';
#             $last = $i + 1;
#             }
#         }
#     $str = $ret . substr($str, $last, $i);
#     return $str;
#     }

