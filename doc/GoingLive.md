
## Going Live / Mise en (pre-)prod



### 1/ Clone repository

```
git clone https://github.com/LiberTIC/ODEV2.git
```

### 2/ Configure apache

00X-ode.preprod.com.conf:

```
VirtualHost *:80>
    ServerName ode.preprod.com

    DocumentRoot /path/to/ODEV2/web
    <Directory /path/to/ODEV2/web>
        AllowOverride None
        Order Allow,Deny
        Allow from All
        Require all granted
        <IfModule mod_rewrite.c>
            Options -MultiViews
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteRule ^(.*)$ app.php [QSA,L]
        </IfModule>
    </Directory>

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
