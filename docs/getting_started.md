# Getting started

## Vagrant
A minimal vagrant environment is prepared where tests can be run in isolation in an environment that, for us, represents
a production environment.

To boot your vagrant machine simply run:

`vagrant up`

This might take a while, also because some Ansible roles are being run. 

Verified to work on a Linux host machine running Ubuntu 18.10 Cosmic. With Ansible, Vagrant & Virtualbox versions:

```
$ vagrant --version
Vagrant 2.0.3

$ ansible --version
ansible 2.5.2

$ ansible-playbook --version 
ansible-playbook 2.5.2
```

VirtualBox at Version 5.2.18

## Installing dependencies
After a successful boot of the Vagrant box, you should be able to install the composer dependencies. You can 'enter' the
 guest with the `vagrant ssh` command. Next, cd to the project folder in the guest. `cd /vagrant` 
 
Installing the composer dependencies is as simple as running:

```bash
composer install
```

Running composer install will also prepare the autoloader, build bootstrap files and clear and warm up the development cache.

## Testing the API in a dev environment
Ansible provisioning does not configure a web server to run the API on. You can use the Symfony built in web server. 
From your host run: `bin/console server:start`. Or do the same from the guest, but ensure you configure your host files
accordingly. 