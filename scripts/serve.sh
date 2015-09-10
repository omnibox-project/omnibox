#!/usr/bin/env bash
server="$1"
domain="$2"
webroot="$3"
name="$4"
webconfig="$5"
share="$6"
ip="$7"
alias="$8"
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

        # serve static files directly
        location ~* ^.+.(jpg|jpeg|gif|css|png|js|ico|html|xml|txt)$ {
            access_log        off;
            expires           max;
        }

        location / {
            index index.html index.php; ## Allow a static html file to be shown first
        }

        location ~ \.php$ {
            try_files \$uri =404;
            fastcgi_pass 127.0.0.1:9000;
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
elif [ "$server" == "apache" ]; then
    block="
    <VirtualHost *:80>
        DocumentRoot $webroot
        ServerName $domain
        ServerAlias $domain.omnibox.com $alias
        ErrorLog \"/vagrant/logs/${domain}_error.log\"
        CustomLog \"/vagrant/logs/${domain}_access.log\" combined
        <Directory \"$webroot\">
            Require all granted
            AllowOverride All
            RewriteEngine On
            RewriteBase /
            RewriteOptions InheritBefore
            RewriteCond %{REQUEST_FILENAME} -f
            RewriteRule ^(.*\.php(/.*)?)$ fcgi://127.0.0.1:9000$webroot/\$1 [L,P]
        </Directory>
    </VirtualHost>
    "

    # Create default apache site configuration
    echo "$block" > "/etc/apache2/sites-available/$domain.conf"
    ln -fs "/etc/apache2/sites-available/$domain.conf" "/etc/apache2/sites-enabled/$domain.conf"
fi

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
