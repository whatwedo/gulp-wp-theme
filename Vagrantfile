# -*- mode: ruby -*-
# vi: set ft=ruby :

# Config Github Settings
github_username = "whatwedo"
github_repo     = "Vaprobash"
github_branch   = "develop"
github_url      = "https://raw.githubusercontent.com/#{github_username}/#{github_repo}/#{github_branch}"

# Because this:https://developer.github.com/changes/2014-12-08-removing-authorizations-token/
# https://github.com/settings/tokens
github_pat          = "ad688311e1779b3d7237728aae6bacb2f8afb9fb"

# Server Configuration
hostname        = "gulp-wp-theme.dev"
start_dir       = "/vagrant"

# Set a local private network IP address.
# See http://en.wikipedia.org/wiki/Private_network for explanation
# You can use the following IP ranges:
#   10.0.0.1    - 10.255.255.254
#   172.16.0.1  - 172.31.255.254
#   192.168.0.1 - 192.168.255.254
server_ip             = "192.168.22.10"

# Calculate system resources
# Default: all CPU cores, an eighth of available RAM as memory and swap size
host = RbConfig::CONFIG['host_os']
if host =~ /darwin/
  server_cpus = `sysctl -n hw.ncpu`.to_i
  server_memory = `sysctl -n hw.memsize`.to_i / 1024 / 1024 / 8
elsif host =~ /linux/
  server_cpus = `nproc`.to_i
  server_memory = `grep 'MemTotal' /proc/meminfo | sed -e 's/MemTotal://' -e 's/ kB//'`.to_i / 1024 / 8
else # sorry Windows folks, I can't help you
  server_cpus = 1
  server_memory = 512
end
server_swap = server_memory

# Manual resource setting
# Overrides calculation
# server_cpus           = "1"   # Cores
# server_memory         = "384" # MB
# server_swap           = "768" # Options: false | int (MB) - Guideline: Between one or two times the server_memory

# UTC        for Universal Coordinated Time
# EST        for Eastern Standard Time
# CET        for Central European Time
# US/Central for American Central
# US/Eastern for American Eastern
server_timezone  = "Europe/Zurich"

# Database Configuration
mysql_root_password     = "root"        # We'll assume user "root"
mysql_version           = "5.5"         # Options: 5.5 | 5.6
mysql_enable_remote     = "true"        # remote access disabled when false
pgsql_postgres_password = "postgres"    # We'll assume user "postgres"
mongo_version           = "2.6"         # Options: 2.6 | 3.0
mongo_enable_remote     = "true"        # remote access disabled when false

# Languages and Packages
php_timezone          = server_timezone  # http://php.net/manual/en/timezones.php
php_version           = "7.0"            # Options: 5.6 | 7.0
ruby_version          = "latest"         # Choose what ruby version should be installed (will also be the default version)
ruby_gems             = [                # List any Ruby Gems that you want to install
  #"jekyll",
  #"sass",
  #"compass",
]

go_version            = "latest" # Example: go1.4 (latest equals the latest stable version)

# To install HHVM instead of PHP, set this to "true"
hhvm                  = "false"

# PHP Options
composer_packages     = [        # List any global Composer packages that you want to install
  #"phpunit/phpunit:4.0.*",
  #"codeception/codeception=*",
  #"phpspec/phpspec:2.0.*@dev",
  #"squizlabs/php_codesniffer:1.5.*",
]

# Default web server document root
# Symfony's public directory is assumed "web"
# Laravel's public directory is assumed "public"
public_folder         = "/vagrant/dist"

laravel_root_folder   = "/vagrant/laravel" # Where to install Laravel. Will `composer install` if a composer.json file exists
laravel_version       = "latest-stable" # If you need a specific version of Laravel, set it here
symfony_root_folder   = "/vagrant/symfony" # Where to install Symfony.

nodejs_version        = "latest"   # By default "latest" will equal the latest stable version
nodejs_packages       = [          # List any global NodeJS packages that you want to install
  #"grunt-cli",
  #"tsd",
  #"bower",
  #"yo",
]

# RabbitMQ settings
rabbitmq_user = "user"
rabbitmq_password = "password"

sphinxsearch_version  = "rel22" # rel20, rel21, rel22, beta, daily, stable

elasticsearch_version = "2.3.1" # 5.0.0-alpha1, 2.3.1, 2.2.2, 2.1.2, 1.7.5

# Add SSH public key
ssh_pub_key = File.readlines("#{Dir.home}/.ssh/id_rsa.pub").first.strip

# Check vagrant version
Vagrant.require_version ">= 1.8.0"

# Configure VM
Vagrant.configure("2") do |config|

  # Set server to Ubuntu 14.04
  config.vm.box = "ubuntu/trusty64"

  config.vm.define "Vaprobash" do |vapro|
  end

  if Vagrant.has_plugin?("vagrant-hostmanager")
    config.hostmanager.enabled = true
    config.hostmanager.manage_host = true
    config.hostmanager.ignore_private_ip = false
    config.hostmanager.include_offline = false
  else
    warn "The recommeded plugin 'vagrant-hostmanager' is currently not installed. You can install it by executing: 'vagrant plugin install vagrant-hostmanager'"
  end

  # Create a hostname, don't forget to put it to the `hosts` file
  # This will point to the server's default virtual host
  # TO DO: Make this work with virtualhost along-side xip.io URL
  config.vm.hostname = hostname

  # Configure network
  if Vagrant.has_plugin?("vagrant-auto_network")
    config.vm.network :private_network, :ip => "0.0.0.0", :auto_network => true
  else
    warn "The recommeded plugin 'vagrant-auto_network' is currently not installed. You can install it by executing: 'vagrant plugin install vagrant-auto_network'"
    config.vm.network :private_network, ip: server_ip
  end

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  # config.vm.network "forwarded_port", guest: 80, host: 8080

  # Enable agent forwarding over SSH connections
  config.ssh.forward_agent = true

  # Configure shared folder
  if Vagrant::Util::Platform.windows? then
    config.vm.synced_folder ".", "/vagrant",
      id: "core",
      type: "smb",
      :mount_options => ['mfsymlinks,dir_mode=0755,file_mode=0755']
  else
    config.vm.synced_folder ".", "/vagrant",
      id: "core",
      :nfs => true,
      :mount_options => ['nolock,vers=3,udp,noatime,actimeo=2,fsc']
  end

  # Replicate local .gitconfig file if it exists
  if File.file?(File.expand_path("~/.gitconfig"))
    config.vm.provision "file", source: "~/.gitconfig", destination: ".gitconfig"
  end

  # Add SSH public key to authorized_keys
  config.vm.provision "shell" do |s|
    s.inline = <<-SHELL
      echo #{ssh_pub_key} >> /home/vagrant/.ssh/authorized_keys
      echo #{ssh_pub_key} >> /root/.ssh/authorized_keys
    SHELL
  end

  # If using VirtualBox
  config.vm.provider :virtualbox do |vb|

    vb.name = hostname

    # Set server cpus
    vb.customize ["modifyvm", :id, "--cpus", server_cpus]

    # Set server memory
    vb.customize ["modifyvm", :id, "--memory", server_memory]

    # Set the timesync threshold to 10 seconds, instead of the default 20 minutes.
    # If the clock gets more than 15 minutes out of sync (due to your laptop going
    # to sleep for instance, then some 3rd party services will reject requests.
    vb.customize ["guestproperty", "set", :id, "/VirtualBox/GuestAdd/VBoxService/--timesync-set-threshold", 10000]

    # Allow symlinks inside shared folders
    vb.customize ["setextradata", :id, "VBoxInternal2/SharedFoldersEnableSymlinksCreate/v-root", "1"]

    # Prevent VMs running on Ubuntu to lose internet connection
    # vb.customize ["modifyvm", :id, "--natdnsproxy1", "on"]

    # Share VPN connection from host to guest
    vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]

    # Automatically update VirtualBox Guest Additions
    if Vagrant.has_plugin?("vagrant-vbguest")
      # set auto_update to false, if you do NOT want to check the correct
      # additions version when booting this machine
      config.vbguest.auto_update = true
    else
      warn "The recommeded plugin 'vagrant-vbguest' is currently not installed. You can install it by executing: 'vagrant plugin install vagrant-vbguest'"
    end

    # Force use of linked clones
    vb.linked_clone = true

  end


  ####
  # Base Items
  ##########

  # Provision Base Packages
  config.vm.provision "shell", path: "#{github_url}/scripts/base.sh", args: [github_url, server_swap, server_timezone, start_dir]

  # optimize base box
  config.vm.provision "shell", path: "#{github_url}/scripts/base_box_optimizations.sh", privileged: true

  # Provision PHP
  config.vm.provision "shell", path: "#{github_url}/scripts/php.sh", args: [php_timezone, hhvm, php_version]

  # Enable MSSQL for PHP
  # config.vm.provision "shell", path: "#{github_url}/scripts/mssql.sh"

  # Provision Vim
  # config.vm.provision "shell", path: "#{github_url}/scripts/vim.sh", args: github_url

  # Provision Docker
  config.vm.provision "shell", path: "#{github_url}/scripts/docker.sh", args: "permissions"

  # Provision docker-compose
  config.vm.provision "shell", path: "#{github_url}/scripts/docker-compose.sh"


  ####
  # Web Servers
  ##########

  # Provision Apache Base
  config.vm.provision "shell", path: "#{github_url}/scripts/apache.sh", args: [server_ip, public_folder, hostname, github_url]

  # Provision Nginx Base
  # config.vm.provision "shell", path: "#{github_url}/scripts/nginx.sh", args: [server_ip, public_folder, hostname, github_url]


  ####
  # Databases
  ##########

  # Provision MySQL
  config.vm.provision "shell", path: "#{github_url}/scripts/mysql.sh", args: [mysql_root_password, mysql_version, mysql_enable_remote]

  # Provision PostgreSQL
  # config.vm.provision "shell", path: "#{github_url}/scripts/pgsql.sh", args: pgsql_postgres_password

  # Provision SQLite
  # config.vm.provision "shell", path: "#{github_url}/scripts/sqlite.sh"

  # Provision RethinkDB
  # config.vm.provision "shell", path: "#{github_url}/scripts/rethinkdb.sh"

  # Provision Couchbase
  # config.vm.provision "shell", path: "#{github_url}/scripts/couchbase.sh"

  # Provision CouchDB
  # config.vm.provision "shell", path: "#{github_url}/scripts/couchdb.sh"

  # Provision MongoDB
  # config.vm.provision "shell", path: "#{github_url}/scripts/mongodb.sh", args: [mongo_enable_remote, mongo_version]

  # Provision MariaDB
  # config.vm.provision "shell", path: "#{github_url}/scripts/mariadb.sh", args: [mysql_root_password, mysql_enable_remote]

  # Provision Neo4J
  # config.vm.provision "shell", path: "#{github_url}/scripts/neo4j.sh"


  ####
  # Search Servers
  ##########

  # Install Elasticsearch
  # config.vm.provision "shell", path: "#{github_url}/scripts/elasticsearch.sh", args: [elasticsearch_version]

  # Install SphinxSearch
  # config.vm.provision "shell", path: "#{github_url}/scripts/sphinxsearch.sh", args: [sphinxsearch_version]

  ####
  # Search Server Administration (web-based)
  ##########

  # Install ElasticHQ
  # Admin for: Elasticsearch
  # Works on: Apache2, Nginx
  # config.vm.provision "shell", path: "#{github_url}/scripts/elastichq.sh"


  ####
  # In-Memory Stores
  ##########

  # Install Memcached
  # config.vm.provision "shell", path: "#{github_url}/scripts/memcached.sh"

  # Provision Redis (without journaling and persistence)
  # config.vm.provision "shell", path: "#{github_url}/scripts/redis.sh"

  # Provision Redis (with journaling and persistence)
  # config.vm.provision "shell", path: "#{github_url}/scripts/redis.sh", args: "persistent"
  # NOTE: It is safe to run this to add persistence even if originally provisioned without persistence


  ####
  # Utility (queue)
  ##########

  # Install Beanstalkd
  # config.vm.provision "shell", path: "#{github_url}/scripts/beanstalkd.sh"

  # Install Heroku Toolbelt
  # config.vm.provision "shell", path: "https://toolbelt.heroku.com/install-ubuntu.sh"

  # Install Supervisord
  # config.vm.provision "shell", path: "#{github_url}/scripts/supervisord.sh"

  # Install Kibana
  # config.vm.provision "shell", path: "#{github_url}/scripts/kibana.sh"

  # Install ØMQ
  # config.vm.provision "shell", path: "#{github_url}/scripts/zeromq.sh"

  # Install RabbitMQ
  # config.vm.provision "shell", path: "#{github_url}/scripts/rabbitmq.sh", args: [rabbitmq_user, rabbitmq_password]


  ####
  # Additional Languages
  ##########

  # Install Nodejs
  config.vm.provision "shell", path: "#{github_url}/scripts/nodejs.sh", privileged: false, args: nodejs_packages.unshift(nodejs_version, github_url)

  # Install Ruby Version Manager (RVM)
  config.vm.provision "shell", path: "#{github_url}/scripts/rvm.sh", privileged: false, args: ruby_gems.unshift(ruby_version)

  # Install Go Version Manager (GVM)
  # config.vm.provision "shell", path: "#{github_url}/scripts/go.sh", privileged: false, args: [go_version]

  # Install Oracle Java 8
  # config.vm.provision "shell", path: "#{github_url}/scripts/oracle-java.sh"


  ####
  # Frameworks and Tooling
  ##########

  # Provision Composer
  # You may pass a github auth token as the first argument
  config.vm.provision "shell", path: "#{github_url}/scripts/composer.sh", privileged: false, args: [github_pat, composer_packages.join(" ")]

  # Provision Laravel
  # config.vm.provision "shell", path: "#{github_url}/scripts/laravel.sh", privileged: false, args: [server_ip, laravel_root_folder, public_folder, laravel_version]

  # Provision Symfony
  # config.vm.provision "shell", path: "#{github_url}/scripts/symfony.sh", privileged: false, args: [server_ip, symfony_root_folder, public_folder]

  # Install Screen
  # config.vm.provision "shell", path: "#{github_url}/scripts/screen.sh"

  # Install Mailcatcher
  config.vm.provision "shell", path: "#{github_url}/scripts/mailcatcher.sh"

  # Install git-ftp
  # config.vm.provision "shell", path: "#{github_url}/scripts/git-ftp.sh", privileged: false

  # Install Ansible
  # config.vm.provision "shell", path: "#{github_url}/scripts/ansible.sh"

  # Install Android
  # config.vm.provision "shell", path: "#{github_url}/scripts/android.sh"

  # Install Maven
  # config.vm.provision "shell", path: "#{github_url}/scripts/maven.sh"

  # Install M4
  # config.vm.provision "shell", path: "#{github_url}/scripts/m4.sh"

  # Install Puppet Client
  # config.vm.provision "shell", path: "#{github_url}/scripts/puppet-client.sh"

  # Install wkhtml2pdf
  # config.vm.provision "shell", path: "#{github_url}/scripts/wkhtmltopdf.sh"

  # Install tutum-cli
  # config.vm.provision "shell", path: "#{github_url}/scripts/tutum-cli.sh"

  # Install phpMyAdmin
  # config.vm.provision "shell", path: "#{github_url}/scripts/phpmyadmin.sh", args: [mysql_root_password]

  # Install docker-nuke
  config.vm.provision "shell", path: "#{github_url}/scripts/docker-nuke.sh"

  # Install mkdocs
  # config.vm.provision "shell", path: "#{github_url}/scripts/mkdocs.sh"

  # Install wp-cli
  config.vm.provision "shell", path: "#{github_url}/scripts/wp-cli.sh"


  ####
  # Customization
  ##########

  # Corporate settings
  config.vm.provision "shell", path: "#{github_url}/scripts/whatwedo.sh"


  ####
  # Local Scripts
  # Any local scripts you may want to run post-provisioning.
  # Add these to the same directory as the Vagrantfile.
  ##########
  # config.vm.provision "shell", path: "./vm-init.sh"


  ####
  # System restart
  # Restart VM after provisioning
  ##########
  if Vagrant.has_plugin?("vagrant-reload")
    config.vm.provision :reload
  else
    warn "The recommeded plugin 'vagrant-reload' is currently not installed. You can install it by executing: 'vagrant plugin install vagrant-reload'"
  end


  ####
  # Cleaning up
  ##########

  # Basic cleaning up task
  config.vm.provision "shell", path: "#{github_url}/scripts/cleanup.sh"


end
