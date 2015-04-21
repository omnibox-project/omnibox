#!/usr/bin/env bash
domain="$1"
webroot="$2"
name="$3"
webconfig="$4"
share="$5"
alias="$6"
if [ "$share" == "1" ]; then
    alias="$alias *.vagrantcloud.com"
fi

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
        try_files \$uri =404;
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_NAME \$fastcgi_script_name;
        fastcgi_param PATH_INFO \$fastcgi_path_info;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
    }

    error_log /vagrant/logs/${domain}_error.log;
    access_log /vagrant/logs/${domain}_access.log;
}
"

# Create default nginx site configuration
echo "$block" > "/etc/nginx/sites-available/$domain"
ln -fs "/etc/nginx/sites-available/$domain" "/etc/nginx/sites-enabled/$domain"

# Create MySQL DB
mysql --user="root" --password="" -e "CREATE DATABASE IF NOT EXISTS $name;"

# Create shortcut in ssh/ for executing ssh commands for this site
template="#!/bin/sh
php omnibox site ssh $name -- \"\$*\"
"
echo "$template" > "/vagrant/ssh/$name"
chmod a+x "/vagrant/ssh/$name"

# Run specialized serve script for site type
if [ -f "/vagrant/scripts/serve_$webconfig.sh" ]; then
    bash /vagrant/scripts/serve_$webconfig.sh $@
fi
