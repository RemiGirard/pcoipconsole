# pcoipconsole

This web app is made to get information and change configuration of Teradici terminals.

It's built with [Symfony 4.3](https://symfony.com/) web framework.

## Install

### Requirements
- PHP 7.1 or higher with php-xml, php-mysql, php-cli and php-curl
- [composer](https://getcomposer.org/download/)
- zip
- [mysql server](https://dev.mysql.com/downloads/mysql/) 
- LAMP with libapache2-mod-php or [symfony client](https://symfony.com/download) for development only

#### Build database
Create an empty database and a user with control over the database:
```
create database db_name;
create user 'db_user'@'localhost' identified by 'db_password';
grant all privileges on db_name.* to 'db_user'@'localhost';
```
variables defined in .env.local will override .env. Create this file and define database access information in it (ignored by git) :
```
# ./.env.local
DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name
# Uncomment the next line for a production environment
# APP_ENV=prod
```
There are two solutions to build the database architecture:
 1. From a backup
 
 This will recreate a database from the db_backup.sql:
 ```
 mysql -u username -p password db_name < "./db_backup.sql"
 ```
 2. From scratch
 
 This will create the database architecture without any data inside:
 ```
  php bin/console doctrine:migrations:migrate
 ```
 If you start from scratch you will have no user with "ROLE_ADMIN". After registering your first user from the web interface, grant it the "ROLE_ADMIN" role manually:
 ```
 php bin/console doctrine:query:sql "update user SET roles='[\"ROLE_ADMIN\"]' where username='admin';"
 ```
### Installation
```
git clone ...
cd pcoipconsole
```
```
composer install --no-dev --optimize-autoloader
```
OR for dev environnement with detailed log and error message:
```
composer install
```
### Example
#### Full installation on Ubuntu Server 2018 for new production environment:
```
sudo apt install mysql-server php-mysql php-cli php-curl php-xml composer zip apache2 libapache2-mod-php
sudo mysql_secure_installation
sudo mysql
```
```
create database db_name;
create user 'db_user'@'localhost' identified by 'db_password';
grant all privileges on db_name.* to 'db_user'@'localhost';
```
```
cd /var/www/
sudo mkdir pcoipconsole
sudo chown myuser:myuser pcoipconsole/
git clone ...myGitRepository... pcoipconsole/
cd pcoipconsole/
composer install --no-dev --optimize-autoloader
```
```
# /var/www/pcoipconsole/.env.local
APP_ENV=prod
DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name
```
```
sudo apt install acl
sudo setfacl -R -m u:www-data:rX pcoipconsole/
sudo setfacl -R -m u:www-data:rwX pcoipconsole/var/cache/ pcoipconsole/var/log/
sudo setfacl -dR -m u:www-data:rwX pcoipconsole/var/cache/ pcoipconsole/var/log/
```
```
# /etc/apache2/sites-available/000-default.conf
<VirtualHost *:80>

    DocumentRoot /var/www/pcoipconsole/public
    <Directory /var/www/pcoipconsole/public>
        AllowOverride None
        Order Allow,Deny
        Allow from All

        <IfModule mod_rewrite.c>
            Options -MultiViews
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteRule ^(.*)$ index.php [QSA,L]
        </IfModule>
    </Directory>

    # uncomment the following lines if you install assets as symlinks
    # or run into problems when compiling LESS/Sass/CoffeScript assets
    # <Directory /var/www/project>
    #     Options FollowSymlinks
    # </Directory>

    ErrorLog /var/log/apache2/symfony_error.log
    CustomLog /var/log/apache2/symfony_access.log combined
</VirtualHost>
```
```
sudo a2enmod rewrite
sudo service apache2 restart
```
The web app is now available. You can create a user with the web interface. Then give it ROLE_ADMIN:
```
php bin/console doctrine:query:sql "update user SET roles='[\"ROLE_ADMIN\"]' where username='myuser';"
```

### Usage
#### Server
Enabling TLS for dev environment:
```
symfony server:ca:install
```
Start the dev server:
```
symfony server:start
```
### Update
```
composer update
```
