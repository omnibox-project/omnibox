# Uberstead

Homestead on steriods for Symfony. Based on ivonunes/homestead.

## Features

- Console for adding/removing/updating sites
- /etc/hosts and the vagrantbox gets provisioned automatically

## Requirements
- Mac OS X
- Parallels Desktop
- Vagrant
- Vagrant Parallels Plugin

## Initial setup

- Run `composer install`
- Setup Uberstead with `sudo php console uberstead:settings`
- Run `vagrant up`
- Add your first site with `sudo php console site:add`

## Usage

- Run `sudo php console` for a list of available commands

## Database

See "Connecting To Your Databases" at http://laravel.com/docs/4.2/homestead

## TODO
- The settings command should be able to update the settings, not only create the settings initially
- Show nice table list with sites when running "sites:delete"
- Add support for domain aliases
- Generate new Symfony2 project and setup database
- Add support for generating bootstrap edition project
- Implement Vagrant-hostmanager?
