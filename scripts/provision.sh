#!/usr/bin/env bash
ip="$1"
apacheip="$2"

sudo apt-get update

# Install graphviz
if [ "$(dpkg -s graphviz 2>/dev/null|wc -l)" -eq "0" ]; then
    sudo apt-get install -y graphviz
fi

# Install apache
if [ "$(dpkg -s apache2 2>/dev/null|wc -l)" -eq "0" ]; then
    sudo apt-get install -y apache2
fi

# Install php5-memcache
if [ "$(dpkg -s php5-memcache 2>/dev/null|wc -l)" -eq "0" ]; then
    sudo apt-get install -y php5-memcache
fi

# Install ca-certificates
if [ "$(dpkg -s ca-certificates 2>/dev/null|wc -l)" -eq "0" ]; then
    sudo apt-get install -y ca-certificates
fi

# Configure swap
if [ ! -f "/swapfile" ]; then
    sudo fallocate -l 2G /swapfile
    sudo chmod 600 /swapfile
    sudo mkswap /swapfile
    sudo swapon /swapfile
    sudo echo "" >> /etc/fstab
    sudo echo "/swapfile   none    swap    sw    0   0" >> /etc/fstab
fi


# Configure apache
if [ -z "$(grep "Listen $apacheip" /etc/apache2/ports.conf)" ]; then
    sudo sed -i "s/^Listen.*\$/Listen $apacheip:80/g" /etc/apache2/ports.conf
    sudo a2enmod rewrite
    sudo a2enmod vhost_alias
    sudo a2enmod expires
    sudo a2enmod proxy_fcgi
    sudo service apache2 restart
fi

# Configure php-fpm
if [ -z "$(grep "listen = 9000" /etc/php5/fpm/pool.d/www.conf)" ]; then
    sudo echo "listen = 9000" >> /etc/php5/fpm/pool.d/www.conf
    sudo echo "; priority=99
realpath_cache_size=32M
apc.shm_size=512M
output_buffering=0
realpath_cache_ttl=86400
upload_max_filesize=200M
post_max_size=200M
opcache.enable=1
opcache.max_accelerated_files=32000
opcache.memory_consumption=512
opcache.revalidate_freq=0
error_reporting=E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED
short_open_tag = On
memory_limit = 256M
xdebug.coverage_enable=0
xdebug.max_nesting_level=1000
xdebug.remote_host=192.168.10.1
xdebug.remote_connect_back=0" > /etc/php5/mods-available/omnibox.ini
    sudo php5enmod omnibox
    sudo service php5-fpm restart
fi

# Install less
npm install -g less@~1.7

# Install Java
if [ "$(dpkg -s oracle-java7-installer 2>/dev/null|wc -l)" -eq "0" ]; then
    echo debconf shared/accepted-oracle-license-v1-1 select true | sudo debconf-set-selections
    echo debconf shared/accepted-oracle-license-v1-1 seen true | sudo debconf-set-selections
    sudo apt-get install -y python-software-properties
    sudo add-apt-repository ppa:webupd8team/java
    sudo apt-get update
    sudo apt-get install -y oracle-java7-installer
fi

# Install Elasticsearch
if [ "$(dpkg -s elasticsearch 2>/dev/null|wc -l)" -eq "0" ]; then
    wget -qO - https://packages.elasticsearch.org/GPG-KEY-elasticsearch | sudo apt-key add -
    sudo add-apt-repository "deb http://packages.elasticsearch.org/elasticsearch/1.5/debian stable main"
    sudo apt-get update
    sudo apt-get install -y elasticsearch
    sudo update-rc.d elasticsearch defaults 95 10
fi

# Configure MySQL Access
mysql --user="root" --password="secret" -e "UPDATE mysql.user SET Password='' WHERE User='root';" 2>/dev/null
mysql --user="root" --password="secret" -e "FLUSH PRIVILEGES;" 2>/dev/null
mysql --user="root" --password="" -e "FLUSH PRIVILEGES;" 2>/dev/null
mysql --user="root" --password="" -e "UPDATE mysql.user SET Password='' WHERE User='root';" 2>/dev/null
mysql --user="root" --password="" -e "FLUSH PRIVILEGES;" 2>/dev/null
service mysql reload

# Remove old app shortcuts
rm -f /vagrant/app/*

# Remove old console shortcuts
rm -f /vagrant/console/*

# Remove old app/ssh shortcuts
rm -f /vagrant/ssh/*

# Remove old nginx configurations
rm -R -f /etc/nginx/sites-available/*
rm -R -f /etc/nginx/sites-enabled/*

# Remove old apache configurations
rm -R -f /etc/apache2/sites-available/*
rm -R -f /etc/apache2/sites-enabled/*

# Remove empty directories left over from removed projects
find /home/vagrant/. -maxdepth 1 -type d -empty -exec rmdir "{}" \;

# Make SSH faster
if [ -z "$(grep "Disable DNS lookups" /etc/ssh/sshd_config)" ]; then
    echo "UseDNS no # Disable DNS lookups" >> /etc/ssh/sshd_config
fi
if [ -z "$(grep "Disable slow GSS API auth" /etc/ssh/sshd_config)" ]; then
    echo "GSSAPIAuthentication no # Disable slow GSS API auth" >> /etc/ssh/sshd_config
fi
sudo service ssh restart >/dev/null
