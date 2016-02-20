#!/usr/bin/env bash
server="$1"
domain="$2"
webroot="$3"
name="$4"
webconfig="$5"
share="$6"
ip="$7"
alias="$8 $9 $10 $11 $12 $13 $14 $15 $16"
if [ "$share" == "1" ]; then
    alias="$alias *.vagrantcloud.com"
fi

root="/home/vagrant/$name"
webroot="$root/$webroot"

if [ "$server" == "nginx" ]; then
    block="server {
        listen $ip:80;
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
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_split_path_info ^(.+\.php)(/.*)$;
            include fastcgi_params;
            fastcgi_param SCRIPT_NAME \$fastcgi_script_name;
            fastcgi_param PATH_INFO \$fastcgi_path_info;
            fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
            fastcgi_param HTTPS off;
            fastcgi_param REMOTE_ADDR 127.0.0.1;
        }
        # PROD
        location ~ ^/(app|index)\.php(/|$) {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_split_path_info ^(.+\.php)(/.*)$;
            include fastcgi_params;
            fastcgi_param SCRIPT_NAME \$fastcgi_script_name;
            fastcgi_param PATH_INFO \$fastcgi_path_info;
            fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
            fastcgi_param HTTPS off;
            fastcgi_param REMOTE_ADDR 127.0.0.1;
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
fi

# Create shortcut in app/ for calling app/console or bin/console for this site
template="#!/bin/sh
php omnibox site console $name -- \"\$*\"
"
echo "$template" > "/vagrant/app/$name"
chmod a+x "/vagrant/app/$name"

# Create shortcut in console/ for calling app/console or bin/console for this site
echo "$template" > "/vagrant/console/$name"
chmod a+x "/vagrant/console/$name"
