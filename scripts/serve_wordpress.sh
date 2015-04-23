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

    index index.php;

    # WordPress single blog rules.
    # Designed to be included in any server {} block.

    # This order might seem weird - this is attempted to match last if rules below fail.
    # http://wiki.nginx.org/HttpCoreModule
    location / {
        try_files \$uri \$uri/ /index.php?\$args;
    }

    # Add trailing slash to */wp-admin requests.
    rewrite /wp-admin\$ \$scheme://\$host\$uri/ permanent;

    # Directives to send expires headers and turn off 404 error logging.
    location ~* ^.+\.(ogg|ogv|svg|svgz|eot|otf|woff|mp4|ttf|rss|atom|jpg|jpeg|gif|png|ico|zip|tgz|gz|rar|bz2|doc|xls|exe|ppt|tar|mid|midi|wav|bmp|rtf)\$ {
           access_log off; log_not_found off; expires max;
    }

    # Uncomment one of the lines below for the appropriate caching plugin (if used).
    #include global/wordpress-wp-super-cache.conf;
    #include global/wordpress-w3-total-cache.conf;

    # Pass all .php files onto a php-fpm/php-fcgi server.
    location ~ [^/]\.php(/|\$) {
        fastcgi_split_path_info ^(.+?\.php)(/.*)\$;
        if (!-f \$document_root\$fastcgi_script_name) {
            return 404;
        }
        # This is a robust solution for path info security issue and works with \"cgi.fix_pathinfo = 1\" in /etc/php.ini (default)

        include fastcgi.conf;
        fastcgi_index index.php;
    #	fastcgi_intercept_errors on;
        fastcgi_pass unix:/var/run/php5-fpm.sock;
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
