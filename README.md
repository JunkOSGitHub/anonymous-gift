# Anonymous Gift

## TODO

- Repartir gifts
- Material create event

## Install

### MySQL

    sudo apt-get install mysql-server
    mysql -u root -p
    > CREATE DATABASE symfonytp;


### PHP5

    sudo apt-get install php5 php5-cli php5-mysql php5-fpm

Add in /etc/php5/fpm/php.ini

    date.timezone = Europe/Paris

### nginx

    sudo apt-get install nginx

conf nginx

    sudo nano /etc/nginx/sites-available/anonymous-gift

Sample content :

    server {
    	listen 80;
    	root PATH_TO_PROJECT/web/;

    	server_name anonymous-gift.local;

    	index index.html index.htm index.nginx-debian.html index.php;

      access_log /var/log/nginx/default-access_log;
    	error_log /var/log/nginx/default-error_log;

    	location / {
    		try_files $uri @rewriteapp;
    	}
    	location @rewriteapp{
    		rewrite ^(.*)$ /app_dev.php/$1 last;
    	}
    	location ~ ^/(app|app_dev|config)\.php(/|$) {
    		fastcgi_pass php5-fpm-sock;
    		fastcgi_split_path_info ^(.+\.php)(/.*)$;
    		include fastcgi_params;
    		fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    		fastcgi_param HTTPS off;
    	}
    }

Enable site :

    ln -s /etc/nginx/sites-available/anonymous-gift /etc/nginx/sites-enabled

Add host entry in /etc/hosts

    127.0.0.1 anonymous-gift.local

Restart

    sudo service nginx restart
    sudo service php5-fpm restart


### Composer

    curl -sS https://getcomposer.org/installer | php

### Dependencies

    php composer.phar install

### Create user

    http://symfony.centrale/register
