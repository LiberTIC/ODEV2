
# Going Live: Production configuration proposals

These configuration tips are provided “as is”, without warranty of any kind, express or implied. 

### 1/ Clone repository

```bash
git clone https://github.com/LiberTIC/ODEV2.git
```

### 2/ Configure apache

00X-ode.preprod.com.conf:

```
<VirtualHost *:80>

    ServerName ode

    SetEnvIf Authorization .+ HTTP_AUTHORIZATION=$0

    DocumentRoot /home/lespolypodes/apps/ODEV2/web
    <Directory /home/lespolypodes/apps/ODEV2/web>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
        Require all granted
    </Directory>
    
    SetEnvIf Request_URI "\.(ico|pdf|flv|jpg|jpeg|png|gif|js|css|swf|txt|cur)$" dontlog
    SetEnvIf Request_URI "^_" dontlog
    SetEnvIf Request_URI "^/_" dontlog
    LogFormat "%V %h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\" \"%{cookie}i\" test" combined_cookie_vhost
    CustomLog /var/log/apache2/access.ode.log combined_cookie_vhost env=!dontlog
    ErrorLog  /var/log/apache2/error.ode.log
    
    # Possible values include: debug, info, notice, warn, error, crit, alert, emerg.
    LogLevel warn
    
    <IfModule mod_rewrite.c>
        Options -MultiViews
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^(.*)$ app.php [QSA,L]
    </IfModule>

    # Preventing high memory usage
    php_flag output_buffering off
    php_flag always_populate_raw_post_data off

    # SabreDAV is not compatible with mbstring function overloading
    php_flag mbstring.func_overload off
    
    # WebPerf: gzip HTTP responses when possible
    
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE application/x-javascript
        AddOutputFilterByType DEFLATE application/javascript
        AddOutputFilterByType DEFLATE application/json
        AddOutputFilterByType DEFLATE image/svg+xml
        AddOutputFilterByType DEFLATE image/x-icon
        AddOutputFilterByType DEFLATE text/richtext
        AddOutputFilterByType DEFLATE text/plain
        AddOutputFilterByType DEFLATE text/xsd
        AddOutputFilterByType DEFLATE text/xsl
        AddOutputFilterByType DEFLATE text/xml
        AddOutputFilterByType DEFLATE text/x-component
        AddOutputFilterByType DEFLATE text/html
        AddOutputFilterByType DEFLATE text/css
        AddOutputFilterByType DEFLATE text/javascript
        # These are already compressed:
        SetEnvIfNoCase Request_URI \.(?:exe|t?gz|zip|bz2|sit|rar|gif|jpe?g|png|avi|mpg|swf|flv|mov|mp3|ogv|mp4|pdf|webm|ogg|rm)$ no-gzip dont-vary
    </IfModule>
    
    # WebPerf: Expire headers
    
    <ifModule mod_expires.c>
        ExpiresActive On
        ExpiresDefault "access plus 5 seconds"
        ExpiresByType image/x-icon "access plus 1 month"
        ExpiresByType image/jpeg "access plus 1 month"
        ExpiresByType image/png "access plus 1 month"
        ExpiresByType image/gif "access plus 1 month"
        ExpiresByType video/ogg "access plus 1 month"
        ExpiresByType audio/ogg "access plus 1 month"
        ExpiresByType video/mp4 "access plus 1 month"
        ExpiresByType video/webm "access plus 1 month"
        ExpiresByType application/x-shockwave-flash "access plus 1 month"
        ExpiresByType text/css "access plus 604800 seconds"
        ExpiresByType text/javascript "access plus 216000 seconds"
        ExpiresByType application/javascript "access plus 216000 seconds"
        ExpiresByType application/x-javascript "access plus 216000 seconds"
        ExpiresByType application/x-font-ttf "access plus 1 month"
        ExpiresByType font/opentype "access plus 1 month"
        ExpiresByType application/x-font-woff "access plus 1 month"
        ExpiresByType image/svg+xml "access plus 1 month"
        ExpiresByType application/vnd.ms-fontobject "access plus 1 month"
        #ExpiresByType text/html "access plus 600 seconds"
        #ExpiresByType application/xhtml+xml "access plus 600 seconds"
    </ifModule>
    
    # WebPerf: Cache-Control Headers (for assets only, Symfony2 already manages HTML output)
    
    <ifModule mod_headers.c>
      <filesMatch ".(ico|jpe?g|png|gif|swf)$">
        Header set Cache-Control "public"
      </filesMatch>
      <filesMatch ".(css)$">
        Header set Cache-Control "public"
      </filesMatch>
      <filesMatch ".(js)$">
        Header set Cache-Control "private"
      </filesMatch>
      #<filesMatch ".(x?html?|php)$">
      #  Header set Cache-Control "private, must-revalidate"
      #</filesMatch>
    </ifModule>

</VirtualHost>
```

*Note: Change serverName to adapt to your needs*

Restart apache

```bash
sudo service apache2 restart
```

### 3/ Configure I/O permissions

```
cd /path/to/ODEV2
find ~/releases -type d -exec chmod 750 {} \;
find ~/releases -type f -exec chmod 640 {} \;
```

Change app/cache and app/logs permission

```bash
sudo chmod -R 750 app/cache
sudo chmod -R 750 app/logs
```
an other possibility is to use ACL :
```bash
$ HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
$ sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs
$ sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX app/cache app/logs
```
Clear the cache for the prod environment

```bash
php app/console cache:clear --env=prod
```
