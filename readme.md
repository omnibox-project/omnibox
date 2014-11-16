# Uberstead

Homestead on steriods for Symfony based on ivonunes/homestead.

## Features

- Console for adding/removing/updating sites
- /etc/hosts and the vagrantbox gets provisioned automatically

## Requirements
- Mac OS X
- Parallels Desktop
- Vagrant
- Vagrant Parallels Plugin

## Usage

- Run `composer install`
- Setup Uberstead with `sudo php console uberstead:settings`
- Run `vagrant up`
- Add your first site with `sudo php console site:add`

## Database

See "Connecting To Your Databases" at http://laravel.com/docs/4.2/homestead

## TODO
- The settings command should be able to update the settings, not only create the settings initially
