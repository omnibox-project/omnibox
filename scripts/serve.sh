#!/usr/bin/env bash
domain="$1"
webroot="$2"
name="$3"
type="$4"
alias="$5"
root="/home/vagrant/$name"
webroot="$root/$webroot"

block="server {
    fastcgi_read_timeout 600;
    server_name $domain $alias;
    root $webroot;

    # serve static files directly
    location ~* ^.+.(jpg|jpeg|gif|css|png|js|ico|html|xml|txt)$ {
        access_log        off;
        expires           max;
    }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }

    error_log /vagrant/logs/${domain}_error.log;
    access_log /vagrant/logs/${domain}_access.log;
}
"

# Create default nginx site configuration
echo "$block" > "/etc/nginx/sites-available/$1"
ln -fs "/etc/nginx/sites-available/$1" "/etc/nginx/sites-enabled/$1"

# Create MySQL DB
mysql --user="root" --password="" -e "CREATE DATABASE IF NOT EXISTS $name;"

# Create shortcut in ssh/ for executing ssh commands for this site
template="#!/bin/sh
php omnibox site ssh $3 -- \"\$*\"
"
echo "$template" > "/vagrant/ssh/$3"
chmod a+x "/vagrant/ssh/$3"

# Run specialized serve script for site type
if [ -f "./serve_$4.sh" ]; then
    ./serve_$4.sh $@
fi

# Restart nginx and php5-fpm
service nginx restart
service php5-fpm restart
