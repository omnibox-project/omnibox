# Omnibox

Homestead inspired Vagrant development environment for Symfony, Magento, WordPress and plain PHP projects.

## Features
- Configures /etc/hosts automatically (if used with `sudo` or given the correct permissions)
- Multiple domains per configured site (aliases)
- Support for `vagrant share` using `sudo php omnibox site share`
- Runs both nginx and Apache; sites run in nginx by default but can be configured by setting the `server` key to `apache` in `omnibox.yaml` and running `sudo php omnibox reload` and `sudo php omnibox provision`

## Requirements
- Mac OS X
- Parallels Desktop
- Vagrant
- Vagrant Parallels Plugin

## Initial setup

- Run `composer install`
- Setup Omnibox with `sudo php omnibox config`
- Run `vagrant up`
- Add your first site with `sudo php omnibox site add`

## Usage

- Run `sudo php omnibox` for a list of available commands

## Database

See "Connecting To Your Databases" at http://laravel.com/docs/4.2/homestead

## TODO
- Confirm delete project, ask if project files should be removed as well
- Don't just check if config exists, check validate its contents and generate a new if there are any errors
- Validate config input for IP, memory and cpus
- When choosing domain or path, list the default options and let the user pick one with a number or enter their own
- Make OS/VM-provider independent, but keep support for Parallels in OS X
- Delete site by typing the project name instead of id in the list, to avoid accidental deletion
- Remove input and output arguments from methods
- Check if database exists when setting up a new project -> promt for removal or add suffix
- Add default project directory to config
- Site ssh and console commands fail when running with sudo - needs correct ssh key
