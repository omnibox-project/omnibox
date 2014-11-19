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
- Add support for domain aliases
- Generate new Symfony2 project and setup database
- Implement Vagrant-hostmanager?
- Run composer create-project with a progress bar when generating new project
- Add tests
- Remove unused items in app/ and ssh/
- Edit, don't only add hints in, parameters.yml when generating a new project
- Run initail setup (settings) command after composer has installed for the first time
- Clear /etc/exports from non existing folders before running vagrant reload to prevent errors
- Confirm delete project, ask if project files should be removed as well
- Validate config input for IP, memory and cpus
- Ask if doctrine migrations should be migrated when settings up a new project with migrations
- Don't just check if config exists, check validate its contents and generate a new if there are any errors
