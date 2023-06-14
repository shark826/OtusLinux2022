# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
  config.vm.box = "centos/7"

  config.vm.provision "ansible" do |ansible|
    #ansible.verbose = "vvv"
    ansible.playbook = "site.yml"
    ansible.become = "true"
  end

  config.vm.provider "virtualbox" do |v|
    v.memory = 1024
    v.cpus = 2
  end

  config.vm.define "webserver" do |web|
    web.vm.network "private_network", ip: "192.168.50.10", virtualbox__intnet: "net1"
    web.vm.network "forwarded_port", guest:8080, host:8080
    web.vm.hostname = "webserver"
  end
  config.vm.define "prometheus" do |web|
    web.vm.network "private_network", ip: "192.168.50.11", virtualbox__intnet: "net1"
    web.vm.network "forwarded_port", guest:9090, host:9090
    web.vm.network "forwarded_port", guest:3000, host:3000
    web.vm.hostname = "prometheus"
  end


end
