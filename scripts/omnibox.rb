class Omnibox
  def Omnibox.configure(config, settings)
    # Configure The Box
    config.vm.box = "ivonunes/homestead"
    config.vm.hostname = "omnibox"

    # Configure A Private Network IP
    config.vm.network :private_network, ip: settings["ip"] ||= "192.168.10.10"

    # Configure A Few VirtualBox Settings
    config.vm.provider "virtualbox" do |vb|
      vb.customize ["modifyvm", :id, "--memory", settings["memory"] ||= "2048"]
      vb.customize ["modifyvm", :id, "--cpus", settings["cpus"] ||= "1"]
      vb.customize ["modifyvm", :id, "--natdnsproxy1", "on"]
      vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
    end

    config.vm.provider "vmware_fusion" do |v|
      v.vmx["memsize"] = settings["memory"]
    end

    config.vm.provider "parallels" do |v|
      v.memory = settings["memory"]
      v.cpus = settings["cpus"]
    end

    # Configure The Public Key For SSH Access
    config.vm.provision "shell" do |s|
      s.inline = "echo $1 | tee -a /home/vagrant/.ssh/authorized_keys"
      s.args = [File.read(File.expand_path(settings["authorize"]))]
    end

    # Copy The SSH Private Keys To The Box
    settings["keys"].each do |key|
      config.vm.provision "shell" do |s|
        s.privileged = false
        s.inline = "echo \"$1\" > /home/vagrant/.ssh/$2 && chmod 600 /home/vagrant/.ssh/$2"
        s.args = [File.read(File.expand_path(key)), key.split('/').last]
      end
    end

    # Copy The Bash Aliases
    config.vm.provision "shell", inline: "cp /vagrant/aliases /home/vagrant/.bash_aliases"

    # Install extra stuff
    config.vm.provision "shell", inline: "sh /vagrant/scripts/provision.sh"

    # Install All The Configured Nginx Sites
    unless settings["sites"].nil?
      settings["sites"].each do |site|
        config.vm.synced_folder site["directory"], "/home/vagrant/" + site["name"], type: site["type"] ||= settings["defaultfoldertype"] ||= nil
        config.vm.provision "shell" do |s|
          s.inline = "bash /vagrant/scripts/serve.sh $1 $2 $3 $4 $5"
          s.args = [site["domain"], site["webroot"], site["name"], site["type"] ||= "", site["alias"] ||= ""]
        end
      end
    end

    # Configure All Of The Server Environment Variables
    if settings.has_key?("variables")
      settings["variables"].each do |var|
        config.vm.provision "shell" do |s|
          s.inline = "echo \"\nenv[$1] = '$2'\" >> /etc/php5/fpm/php-fpm.conf && service php5-fpm restart"
          s.args = [var["key"], var["value"]]
        end
      end
    end
  end
end
