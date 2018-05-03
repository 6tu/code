
domain=opm.yourshell.info
username=opm

cat >> /opt/lampp/etc/extra/httpd-vhosts.conf <<EOF

##---- localhost ---##
<VirtualHost ${ip}:80>
    ServerAdmin webmaster@ysuo.org
    DocumentRoot "/var/www"
    ServerName localhost
    ServerAlias localhost
    ErrorLog /opt/lampp/logs/error.log
    CustomLog /opt/lampp/logs/access.log common
    <IfModule mpm_prefork_module>
        AssignUserId daemon daemon
    </IfModule>
</VirtualHost>

<VirtualHost ${ip}>
    DocumentRoot "/var/www"
    ServerName localhost
    ServerAlias localhost
    SSLEngine on
    SSLCipherSuite ALL:!ADH:!EXPORT56:RC4+RSA:+HIGH:+MEDIUM:+LOW:+SSLv2:+EXP:+eNULL
    SSLCertificateFile "/opt/lampp/etc/ssl.crt/server.crt"
    SSLCertificateKeyFile "/opt/lampp/etc/ssl.key/server.key"
    <FilesMatch "/.(cgi|shtml|phtml|php)$">
        SSLOptions +StdEnvVars
    </FilesMatch>
    BrowserMatch ".*MSIE.*" nokeepalive ssl-unclean-shutdown downgrade-1.0 force-response-1.0
    ErrorLog /opt/lampp/logs/ssl_error.log
    CustomLog "/opt/lampp/logs/ssl_request_log"  common
    <IfModule mpm_prefork_module>
        AssignUserId daemon daemon
    </IfModule>
</VirtualHost>




##--- ${domain} ---##
<VirtualHost ${domain}:80>
    ServerAdmin webmaster@${domain}
    DocumentRoot "/home/${domain}"
    ServerName ${domain}
    ServerAlias www.${domain}
    ErrorLog /opt/lampp/logs/${domain}-error.log
    CustomLog /opt/lampp/logs/${domain}-access.log common
    <IfModule mpm_prefork_module>
        AssignUserId ${username} daemon
    </IfModule>
</VirtualHost>

##----ssl yisuo.asia ---##
<VirtualHost ${domain}:443>
    DocumentRoot "/home/${domain}"
    ServerName ${domain}
    ServerAlias www.${domain}
    SSLEngine on
    SSLCipherSuite ALL:!ADH:!EXPORT56:RC4+RSA:+HIGH:+MEDIUM:+LOW:+SSLv2:+EXP:+eNULL
    SSLCertificateFile "/opt/lampp/etc/ssl.crt/${domain}.crt"
    SSLCertificateKeyFile "/opt/lampp/etc/ssl.key/${domain}.key"
    <FilesMatch "/.(cgi|shtml|phtml|php)$">
        SSLOptions +StdEnvVars
    </FilesMatch>
    BrowserMatch ".*MSIE.*" nokeepalive ssl-unclean-shutdown downgrade-1.0 force-response-1.0
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
/opt/lampp/ctlscript.sh restart apache

