class Omnibox
  def Omnibox.configure(config, settings)
    # Configure The Box
    config.vm.box = "alvassin/homestead-parallels"
    config.vm.box_version = "1.0.0"
    config.vm.hostname = "omnibox"

    # Configure A Private Network IP
    config.vm.network :private_network, ip: settings["ip"] ||= "192.168.10.10"
    config.vm.network :private_network, ip: settings["apache_ip"] ||= "192.168.10.11"

    # Configure A Few VirtualBox Settings
    config.vm.provider "virtualbox" do |vb|
      vb.customize ["modifyvm", :id, "--memory", settings["memory"] ||= "1024"]
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
      v.name = "omnibox"
      v.update_guest_tools = true
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
    config.vm.provision "shell" do |s|
        s.inline = "sh /vagrant/scripts/provision.sh $1 $2"
        s.args = [settings["ip"] ||= "192.168.10.10", settings["apache_ip"] ||= "192.168.10.11"]
    end

    # Install All The Configured Nginx Sites
    unless settings["sites"].nil?
      settings["sites"].each do |site|
        config.vm.synced_folder site["directory"], "/home/vagrant/" + site["name"], type: site["type"] ||= settings["defaultfoldertype"] ||= nil
        config.vm.provision "shell" do |s|
          s.path = 'scripts/serve.sh'
          s.args = [site["server"] ||= "nginx", site["domain"], site["webroot"], site["name"], site["webconfig"] ||= "", site["share"] ||= "", settings["ip"] ||= "192.168.10.10", site["alias"] ||= ""]
        end
      end
    end

    # Restart nginx and php5-fpm
    config.vm.provision "shell" do |s|
      s.inline = "service nginx restart && service apache2 restart && service php5-fpm restart"
      s.args = []
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

    # Restart nginx after mount
    config.vm.provision :shell, :inline => "sudo service nginx restart && sudo service apache2 restart", run: "always"
  end
end
