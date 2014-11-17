#!/usr/bin/env bash

# Install less
npm install -g less

# Create logs directory
mkdir -p /vagrant/logs

# Configure MySQL Access
mysql --user="root" --password="secret" -e "UPDATE mysql.user SET Password='' WHERE User='root';" 2>/dev/null
mysql --user="root" --password="secret" -e "FLUSH PRIVILEGES;" 2>/dev/null
mysql --user="root" --password="" -e "FLUSH PRIVILEGES;" 2>/dev/null
mysql --user="root" --password="" -e "UPDATE mysql.user SET Password='' WHERE User='root';" 2>/dev/null
mysql --user="root" --password="" -e "FLUSH PRIVILEGES;" 2>/dev/null
service mysql reload

# Remove old nginx configurations
rm -R -f /etc/nginx/sites-available/*
rm -R -f /etc/nginx/sites-enabled/*

# Remove empty directories left over from removed projects
find /home/vagrant/. -depth -type d -empty -exec rmdir "{}" \;
