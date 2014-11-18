#!/usr/bin/env bash

# Install less
npm install -g less

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

# Make SSH faster
if [ -z "$(grep "Disable DNS lookups" /etc/ssh/sshd_config)" ]; then
    echo "UseDNS no # Disable DNS lookups" >> /etc/ssh/sshd_config
fi
if [ -z "$(grep "Disable slow GSS API auth" /etc/ssh/sshd_config)" ]; then
    echo "GSSAPIAuthentication no # Disable slow GSS API auth" >> /etc/ssh/sshd_config
fi
sudo service ssh restart >/dev/null
