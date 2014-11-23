#!/usr/bin/env bash

domain="$1"
webroot="$2"
name="$3"

root="/home/vagrant/$name"
webroot="$root/$webroot"

block="server {
    fastcgi_read_timeout 600;
    server_name $domain;
    root $webroot;

    location / {
        # try to serve file directly, fallback to app.php
        try_files \$uri /app.php\$is_args\$args;
    }
    # DEV
    # This rule should only be placed on your development environment
    # In production, don't include this and don't deploy app_dev.php or config.php
    location ~ ^/(app_dev|config)\.php(/|$) {
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param HTTPS off;
        fastcgi_param REMOTE_ADDR 127.0.0.1;
        fastcgi_param PHP_VALUE \"xdebug.max_nesting_level=1000
xdebug.remote_host=192.168.10.1
xdebug.remote_connect_back=0\";
    }
    # PROD
    location ~ ^/app\.php(/|$) {
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param HTTPS off;
        fastcgi_param REMOTE_ADDR 127.0.0.1;
        fastcgi_param PHP_VALUE \"xdebug.max_nesting_level=1000
xdebug.remote_host=192.168.10.1
xdebug.remote_connect_back=0\";
        # Prevents URIs that include the front controller. This will 404:
        # http://domain.tld/app.php/some-path
        # Remove the internal directive to allow URIs like this
        internal;
    }

    error_log /vagrant/logs/${domain}_error.log;
    access_log /vagrant/logs/${domain}_access.log;
}
"

echo "$block" > "/etc/nginx/sites-available/$1"
ln -fs "/etc/nginx/sites-available/$1" "/etc/nginx/sites-enabled/$1"
service nginx restart
service php5-fpm restart

# Create MySQL DB
mysql --user="root" --password="" -e "CREATE DATABASE IF NOT EXISTS $name;"

# Create shortcut in app/ for calling app/console for this site
template="#!/bin/sh
php console site console $3 -- \"\$*\"
"
echo "$template" > "/vagrant/app/$3"
chmod a+x "/vagrant/app/$3"

# Create shortcut in ssh/ for executing ssh commands for this site
template="#!/bin/sh
php omnibox site ssh $3 -- \"\$*\"
"
echo "$template" > "/vagrant/ssh/$3"
chmod a+x "/vagrant/ssh/$3"
