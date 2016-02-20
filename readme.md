# Omnibox

Homestead inspired Vagrant development environment for all kinds of PHP projects.

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

## Check project packages for outdated versions and security issues

    # php omnibox site check symfony-webpack-react
    --- Checking project /home/vagrant/symfony-webpack-react ---
    SensioLabs Security Checker: 0 vulnerable packages
    Composer Climb: 1 package(s) can be upgraded
    npm: 31 package(s) can be upgraded

For a full list use the `verbose` argument:

    # php omnibox site check symfony-webpack-react verbose
    --- Checking project /home/vagrant/symfony-webpack-react ---
    SensioLabs Security Checker: 0 vulnerable packages
    Composer Climb: 1 package(s) can be upgraded
    - symfony/phpunit-bridge: 2.8.2 => 3.0.2
    npm: 31 package(s) can be upgraded
    - accepts: 1.2.13 => 1.3.1
    - array-flatten: 1.1.1 => 2.0.0
    - async-each: 0.1.6 => 1.0.0
    - babel-runtime: 5.8.35 => 6.5.0
    - bytes: 2.2.0 => 2.3.0
    - camelcase: 1.2.1 => 2.1.0
    - cookie: 0.1.5 => 0.2.3
    - core-js: 1.2.6 => 2.1.0
    - eslint: 1.10.3 => 2.2.0
    - eslint-plugin-import: 0.13.0 => 1.0.0-beta.0
    - fbjs: 0.6.1 => 0.7.2
    - get-stdin: 4.0.1 => 5.0.1
    - glob: 5.0.15 => 7.0.0
    - graceful-fs: 3.0.8 => 4.1.3
    - image-size: 0.3.5 => 0.4.0
    - lodash: 3.10.1 => 4.5.0
    - lodash.assign: 3.2.0 => 4.0.3
    - lodash.camelcase: 3.0.1 => 4.1.0
    - lodash.defaults: 3.1.2 => 4.0.1
    - lodash.pick: 3.1.0 => 4.1.0
    - mime: 1.2.11 => 1.3.4
    - minimatch: 2.0.10 => 3.0.0
    - path-exists: 1.0.0 => 2.1.0
    - path-to-regexp: 0.1.7 => 1.2.1
    - promise: 6.1.0 => 7.1.1
    - qs: 4.0.0 => 6.1.0
    - shebang-regex: 1.0.0 => 2.0.0
    - source-map: 0.1.43 => 0.5.3
    - strip-json-comments: 1.0.4 => 2.0.1
    - vary: 1.0.1 => 1.1.0
    - webpack: 2.0.7-beta => 1.12.13

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
