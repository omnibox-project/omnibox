VAGRANTFILE_API_VERSION = "2"

path = "#{File.dirname(__FILE__)}"

require 'yaml'
require path + '/scripts/uberstead.rb'

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  Uberstead.configure(config, YAML::load(File.read(path + '/uberstead.yaml')))
end
