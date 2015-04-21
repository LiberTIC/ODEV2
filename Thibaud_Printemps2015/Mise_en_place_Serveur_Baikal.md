Mise en place serveur Baïkal de Dev
===================================

### Machine virtuelle

Vagrant est disponible [ici](https://www.vagrantup.com/downloads.html)!

**Création de la machine virtuelle:**
```bash
mkdir ~/Applications/virtHost/
cd ~/Applications/virtHost/

vagrant init ubuntu/trusty32

# Décommenter la ligne config.vm.network
# Et ajouter un 8000 => 8000
vim Vagrantfile

vagrant up

#Se connecter à la machine virtuelle pour la suite
vagrant ssh
```

**Mise en place LAMP**
```bash
sudo apt-get install apache2 apache2-mpm-prefork
sudo apt-get install libapache2-mod-php5 apache2-utils php5
sudo apt-get install mysql-server imagemagick
sudo apt-get install php5-dev phpmyadmin

sudo apt-get install php-apc php5-mysql php5-sqlite php5-cli
sudo apt-get install php5-curl php5-gd php5-imagick
sudo apt-get install php5-imap php5-xsl php5-common
sudo apt-get install php5-mcrypt php5-memcache
sudo apt-get install php5-ps php5-intl 

# Si vous n'avez pas de mot de passe pour Root, vous devez autoriser l'authentification sans mot de passe
# Décommentez la ligne "$cfg['Servers'][$i]['AllowNoPassword'] = TRUE;"
sudo vim /etc/phpmyadmin/config.inc.php
```

Vous devriez pouvoir vous connecter à PhpMyAdmin avec http://localhost:8080/phpmyadmin/

**Mise en place Baïkal**
```bash
# 1. Install composer
curl -sS https://getcomposer.org/installer | php

# 2. Install git & npm
sudo apt-get install git npm

cd /vagrant/

# 3. Clone the Baïkal2 source code
$ git clone -b 2.0.0-alpha2 https://github.com/netgusto/Baikal.git

# 4. Enter the Baikal folder
$ cd Baikal

# 5. Initialize the application settings
$ cp app/config/defaults/data.parameters.dist.yml data/parameters.yml
$ cp app/config/defaults/data.environment.dist.yml data/environment.yml

# 6. Configure your database connection in data/environment.yml
# // open 'data/environment.yml', uncomment and edit the DATABASE_URL variable
vim data/environment.yml

# 7. Install Baïkal PHP dependencies, and initialize Baïkal
# // at the root of the project
$ composer install --optimize-autoloader

# 7.1 If the precedent line throws an exception about UserMetadata table missing,
# Add it directly inside phpmyadmin:
# Table: UserMetadate / id: int (AutoInc.), roles: varchar, userid int

# 8. Install required node packages in the global scope:
$ sudo npm install -g bower grunt-cli

# 8.1 Fix the node missing link
sudo ln -s /usr/bin/nodejs /usr/bin/node

# 9. Unpack subprojects
$ npm run unpack

# 10. Boot the development server
$ npm run dev
```

Vous devriez pouvoir vous connecter à Baikal avec http://localhost:8000/

Utilisez les identifiants: **admin/password**

Pour tester votre serveur, ajouter le calDAV avec l'url suivante:

http://localhost:8000/dav/cal/principals/{username}/ (Par exemple: admin)