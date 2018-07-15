read -p "Hostname for VPN (e.g. vpn.example.com): " domain

read -p "username (default: Europe/London): " username
username=${username:-'daemon'}

cat >> /opt/lampp/etc/vhosts/${domain}.conf <<EOF

##--- ${domain} ---##
<VirtualHost ${domain}:80>
    ServerAdmin webmaster@${domain}
    php_admin_value open_basedir /var/www:/home/${domain}:/opt/lampp/phpmyadmin:/opt/lampp/temp:/tmp:/var/tmp:/proc
    ServerName ${domain}
    ServerAlias www.${domain}
    DocumentRoot "/home/${domain}"
    <Directory /home/${domain}>
        SetOutputFilter DEFLATE
        Options Indexes FollowSymLinks
        IndexOptions Charset=UTF-8
        AllowOverride All
        Order Deny,Allow
        Require all granted
        DirectoryIndex index.php index.html index.htm
    </Directory>
    ErrorLog /opt/lampp/logs/${domain}-error.log
    CustomLog /opt/lampp/logs/${domain}-access.log common
    <IfModule mpm_prefork_module>
        AssignUserId ${username} daemon
    </IfModule>
</VirtualHost>


##----ssl ${domain} ---##
<VirtualHost ${domain}:443>
    ServerAdmin webmaster@${domain}
    php_admin_value open_basedir /var/www:/home/${domain}:/opt/lampp/phpmyadmin:/opt/lampp/temp:/tmp:/var/tmp:/proc
    ServerName ${domain}
    ServerAlias www.${domain}
    DocumentRoot "/home/${domain}"
    <Directory /home/${domain}>
        SetOutputFilter DEFLATE
        Options Indexes FollowSymLinks
        IndexOptions Charset=UTF-8
        AllowOverride All
        Order Deny,Allow
        Require all granted
        DirectoryIndex index.php index.html index.htm
    </Directory>

    SSLEngine on
    SSLCipherSuite ALL:!ADH:!EXPORT56:RC4+RSA:+HIGH:+MEDIUM:+LOW:+SSLv2:+EXP:+eNULL
    SSLCertificateFile "/opt/lampp/etc/ssl.crt/${domain}.crt"
    SSLCertificateKeyFile "/opt/lampp/etc/ssl.key/${domain}.key"
    <FilesMatch "/.(cgi|shtml|phtml|php)$">
        SSLOptions +StdEnvVars
    </FilesMatch>
    BrowserMatch ".*MSIE.*" nokeepalive ssl-unclean-shutdown downgrade-1.0 force-response-1.0
    Header always set Strict-Transport-Security "max-age=31536000; preload"
    Header always edit Set-Cookie ^(.*)$ ;HttpOnly;Secure
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options SAMEORIGIN
    ErrorLog /opt/lampp/logs/${domain}-ssl_error.log
    CustomLog "/opt/lampp/logs/${domain}-ssl_request_log"  common
    <IfModule mpm_prefork_module>
        AssignUserId ${username} daemon
    </IfModule>
</VirtualHost>
EOF

useradd -g daemon -d /dev/null -s /usr/sbin/nologin ${username}
mkdir /home/${domain}
echo "hello world! welcome to ${domain}" > /home/${domain}/index.html
chown -R ${username}:daemon /home/${domain}
chmod -R +x /home/${domain}
#/opt/lampp/ctlscript.sh restart apache
