#!/bin/bash
sudo -i
yum install nano epel-release -y && yum install spawn-fcgi php php-cli mod_fcgid httpd -y
