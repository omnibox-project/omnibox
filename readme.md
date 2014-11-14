# Uberstead

Homestead but for Symfony and Parallels based on ivonunes/homestead.

## Configure

- "vagrant box add ivonunes/homestead"
- Copy uberstead.yaml.dist to uberstead.yaml
- Configure the sites parameter with your own projects
- Point the configured domains to the IP of the Vagrant box in your hosts file (192.168.10.10 by default)
- "vagrant up"

## Database

See "Connecting To Your Databases" at http://laravel.com/docs/4.2/homestead

## TODO
- Script to add/remove sites, should fix hosts file and re-provision
