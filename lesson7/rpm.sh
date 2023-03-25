#!/bin/bash
sudo -i
cd /root/
yum install -y redhat-lsb-core wget rpmdevtools rpm-build createrepo yum-utils gcc
wget https://nginx.org/packages/centos/7/SRPMS/nginx-1.22.1-1.el7.ngx.src.rpm
rpm -i nginx-1.22.1-1.el7.ngx.src.rpm
wget --no-check-certificate https://www.openssl.org/source/openssl-1.1.1s.tar.gz
tar -xvf openssl-1.1.1s.tar.gz
yum-builddep -y rpmbuild/SPECS/nginx.spec
cp /vagrant/nginx.spec rpmbuild/SPECS/nginx.spec
rpmbuild -bb rpmbuild/SPECS/nginx.spec
ll rpmbuild/RPMS/x86_64/
yum localinstall -y \rpmbuild/RPMS/x86_64/nginx-1.14.1-1.el7_4.ngx.x86_64.rpm
systemctl start nginx
systemctl status nginx
mkdir /var/www/html/repo
cp rpmbuild/RPMS/x86_64/nginx-1.22.1-1.el7.ngx.x86_64.rpm /usr/share/nginx/html/repo/
wget http://mirror.centos.org/centos/7/os/x86_64/Packages/jetty-server-9.0.3-8.el7.noarch.rpm -O /usr/share/nginx/html/repo/jetty-server-9.0.3-8.el7.noarch.rpm
createrepo /usr/share/nginx/html/repo/
curl -a http://localhost/repo/
cat >> /etc/yum.repos.d/otus.repo << EOF
[otus]
name=otus-linux
baseurl=http://localhost/repo
gpgcheck=0
enabled=1
EOF
yum repolist enabled | grep otus
yum list | grep otus
yum install percona-release -y
