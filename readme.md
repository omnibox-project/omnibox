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
- Implement Vagrant-hostmanager?
- Run composer create-project with a progress bar when generating new project
- Add tests
- Remove unused items in app/ and ssh/
- Edit, don't only add hints in, parameters.yml when generating a new project
- Run initail setup (settings) command after composer has installed for the first time
- Confirm delete project, ask if project files should be removed as well

- Validate config input for IP, memory and cpus
- Validate config file, dont just check if it exists

- Ask if doctrine migrations should be migrated when settings up a new project with migrations
- Don't just check if config exists, check validate its contents and generate a new if there are any errors
- When choosing domain or path, list the default options and let the user pick one with a number or enter their own
- Make OS independent, but keep support for parallels in OSX
- Integrate vagrant share support. Add *.vagrantshare.com to the nginx conf and restart nginx.
- Create a output format that is used everywhere
- Send usage statistics for presenting on website, if user opts in
- Delete site by typing the project name instead of id in the list, to avoid accidental deletion
