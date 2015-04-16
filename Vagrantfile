VAGRANTFILE_API_VERSION = "2"
ENV['VAGRANT_DEFAULT_PROVIDER'] = "parallels"

path = "#{File.dirname(__FILE__)}"

require 'yaml'
require path + '/scripts/omnibox.rb'

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  Omnibox.configure(config, YAML::load(File.read(path + '/omnibox.yaml')))
end
