#!/usr/bin/env bash
domain="$1"
webroot="$2"
name="$3"
webconfig="$4"
share="$5"
alias="$6"
if [ "$share" == "1" ]; then
    alias="$alias $share *.vagrantcloud.com"
fi

root="/home/vagrant/$name"
webroot="$root/$webroot"

block="server {
    fastcgi_read_timeout 600;
    server_name $domain $alias;
    root $webroot;

    location / {
        # try to serve file directly, fallback to app.php
        try_files \$uri /app.php\$is_args\$args /index.php\$is_args\$args;
    }
    # DEV
    # This rule should only be placed on your development environment
    # In production, don't include this and don't deploy app_dev.php or config.php
    location ~ ^/(app_dev|config)\.php(/|$) {
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_NAME \$fastcgi_script_name;
        fastcgi_param PATH_INFO \$fastcgi_path_info;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param HTTPS off;
        fastcgi_param REMOTE_ADDR 127.0.0.1;
        fastcgi_param PHP_VALUE \"xdebug.max_nesting_level=1000
xdebug.remote_host=192.168.10.1
xdebug.remote_connect_back=0\";
    }
    # PROD
    location ~ ^/(app|index)\.php(/|$) {
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_NAME \$fastcgi_script_name;
        fastcgi_param PATH_INFO \$fastcgi_path_info;
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

# Create nginx site configuration
echo "$block" > "/etc/nginx/sites-available/$domain"

# Create shortcut in app/ for calling app/console for this site
template="#!/bin/sh
php omnibox site console $name -- \"\$*\"
"
echo "$template" > "/vagrant/app/$name"
chmod a+x "/vagrant/app/$name"
