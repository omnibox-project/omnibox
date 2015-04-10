#!/usr/bin/env bash

# Install less
npm install -g less@~1.7

# Install Java
echo debconf shared/accepted-oracle-license-v1-1 select true | sudo debconf-set-selections
echo debconf shared/accepted-oracle-license-v1-1 seen true | sudo debconf-set-selections
sudo apt-get install -y python-software-properties
sudo add-apt-repository ppa:webupd8team/java
sudo apt-get update
sudo apt-get install -y oracle-java7-installer

# Install Elasticsearch
wget -qO - https://packages.elasticsearch.org/GPG-KEY-elasticsearch | sudo apt-key add -
sudo add-apt-repository "deb http://packages.elasticsearch.org/elasticsearch/1.4/debian stable main"
sudo apt-get update && sudo apt-get install elasticsearch
sudo update-rc.d elasticsearch defaults 95 10

# Configure MySQL Access
mysql --user="root" --password="secret" -e "UPDATE mysql.user SET Password='' WHERE User='root';" 2>/dev/null
mysql --user="root" --password="secret" -e "FLUSH PRIVILEGES;" 2>/dev/null
mysql --user="root" --password="" -e "FLUSH PRIVILEGES;" 2>/dev/null
mysql --user="root" --password="" -e "UPDATE mysql.user SET Password='' WHERE User='root';" 2>/dev/null
mysql --user="root" --password="" -e "FLUSH PRIVILEGES;" 2>/dev/null
service mysql reload

# Remove old app/console shortcuts
rm -f /vagrant/app/*

# Remove old app/ssh shortcuts
rm -f /vagrant/ssh/*

# Remove old nginx configurations
rm -R -f /etc/nginx/sites-available/*
rm -R -f /etc/nginx/sites-enabled/*

# Remove empty directories left over from removed projects
find /home/vagrant/. -depth -type d -empty -exec rmdir "{}" \;

# Make SSH faster
if [ -z "$(grep "Disable DNS lookups" /etc/ssh/sshd_config)" ]; then
    echo "UseDNS no # Disable DNS lookups" >> /etc/ssh/sshd_config
fi
if [ -z "$(grep "Disable slow GSS API auth" /etc/ssh/sshd_config)" ]; then
    echo "GSSAPIAuthentication no # Disable slow GSS API auth" >> /etc/ssh/sshd_config
fi
sudo service ssh restart >/dev/null
