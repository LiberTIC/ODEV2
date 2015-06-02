
## Going Live / Mise en (pre-)prod



### 1/ Clone repository

```
git clone https://github.com/LiberTIC/ODEV2.git
```

### 2/ Configure apache

00X-ode.preprod.com.conf:

```
<VirtualHost *:80>
    ServerName ode.preprod.lespolypodes.com

    SetEnvIf Authorization .+ HTTP_AUTHORIZATION=$0

    DocumentRoot /home/lespolypodes/apps/ODEV2/web
    <Directory /home/lespolypodes/apps/ODEV2/web>
        AllowOverride None
        Order Allow,Deny
        Allow from All
    Require all granted
        <IfModule mod_rewrite.c>
            Options -MultiViews
            RewriteEngine On
            #RewriteCond %{REQUEST_FILENAME} !-f
            #RewriteCond %{HTTP:Authorization} ^(.+)$
            #RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteRule ^(.*)$ app.php [QSA,L]
            #RewriteRule ^(.*)$ app.php [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},QSA,L]
        </IfModule>
    </Directory>

    # Output buffering needs to be off, to prevent high memory usage
    php_flag output_buffering off

    # This is also to prevent high memory usage
    php_flag always_populate_raw_post_data off

    # This is almost a given, but magic quotes is *still* on on some
    # linux distributions
    php_flag magic_quotes_gpc off

    # SabreDAV is not compatible with mbstring function overloading
    php_flag mbstring.func_overload off

    # uncomment the following lines if you install assets as symlinks
    # or run into problems when compiling LESS/Sass/CoffeScript assets
    # <Directory /var/www/project>
    #     Options FollowSymlinks
    # </Directory>

    ErrorLog /var/log/apache2/error.ode.preprod.log
    CustomLog /var/log/apache2/access.ode.preprod.log combined
</VirtualHost>
```

*Note: Change serverName to adapt to your needs*

Restart apache

```
sudo service apache2 restart
```

### 3/ Configure the Symfony project

Change app/cache and app/log permission

```
cd /path/to/ODEV2
sudo chmod -R 777 app/cache
sudo chmod -R 777 app/log
```

Clear the cache for the prod environment

```
sudo php app/console cache:clear --env=prod
```
