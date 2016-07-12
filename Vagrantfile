# -*- mode: ruby -*-
# vi: set ft=ruby :

# ---- Configuration variables ----

RAM               = 256            # Default memory size in MB
IP_POOL           = "10.55.0.0/24" # IP address pool for communication with/between VMs

BOX               = "debian/jessie64" # Source: https://atlas.hashicorp.com/debian/boxes/jessie64

HOSTS = {
    "redistest"  => [BOX, RAM],
    "corvus1"    => [BOX, RAM],
    "redis1"     => [BOX, RAM],
    "redis2"     => [BOX, RAM],
    "redis3"     => [BOX, RAM],
    "redis4"     => [BOX, RAM],
    "redis5"     => [BOX, RAM],
    "redis6"     => [BOX, RAM],
}

GROUPS = {
  "corvus"  => ["corvus1"],
  "redis"   => ["redis1", "redis2", "redis3", "redis4", "redis5", "redis6"],
}

VAGRANTFILE_DIR = File.dirname(__FILE__)
ANSIBLE_INVENTORY_DIR = "#{VAGRANTFILE_DIR}/ansible/inventory"

# ---- Vagrant configuration ----

unless Vagrant.has_plugin?("vagrant-auto_network")
  raise "vagrant-auto_network is not installed!"
end

AutoNetwork.default_pool = IP_POOL

Vagrant.configure(2) do |config|
  config.vm.box_check_update = false

  if Vagrant.has_plugin?("vagrant-vbguest")
    config.vbguest.auto_update = false
  end

  HOSTS.each do | (name, cfg) |
    box, ram = cfg

    config.vm.define name do |machine|
      machine.vm.box   = box
      machine.vm.guest = :debian

      machine.vm.provider "virtualbox" do |vbox|
        vbox.memory = ram
      end

      machine.vm.hostname = name
      machine.vm.network :private_network, :auto_network => true
      machine.vm.synced_folder '.', '/vagrant', disabled: true
    end
  end

  config.vm.provision "vai" do |ansible|
    ansible.inventory_dir = ANSIBLE_INVENTORY_DIR
    ansible.groups = GROUPS
  end

end
