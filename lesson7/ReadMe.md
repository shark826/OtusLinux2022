# Курс Administrator Linux. Professional

### Домашнее задание №7
### Управления пакетами. Дистрибьюция софта

в ДЗ я сначала проведу настройку сервера в ручном режиме, а затем все команды запишу в скрипт, который будет исполняться при запуске Vagrant

**1. Создаём виртуальную машину**  
  
Использую Vagrantfile, который в репозитории    
  
  

```vagrant up ```  
запускаем виртуальную машину  
  

Заходим на сервер:  
```vagrant ssh rpm```  

Внутри виртуалки переходим в root пользователя:  
```sudo -i```  

**2. Настройка сервера**  

Установка утилит для сборки пакетов и установки локального репозитория:  
```bash
yum install -y redhat-lsb-core wget rpmdevtools rpm-build createrepo yum-utils
```  

**3. Создаем свой RPM пакет**  

Для примера соберем пакет NGINX с поддержкой openssl  
Загрузим SRPM пакет NGINX  
```bash
wget https://nginx.org/packages/centos/7/SRPMS/nginx-1.22.1-1.el7.ngx.src.rpm

```
Устоновим этот пакет, посл установки пакета в домашней директории создается древо каталогов для
сборки:  
```bash
rpm -i nginx-1.22.1-1.el7.ngx.src.rpm
```
дерево каталогов  
  
```  
└── rpmbuild  
    ├── BUILD  
    ├── BUILDROOT  
    ├── RPMS  
    ├── SOURCES  
    ├── SPECS  
    └── SRPMS  
```  
скачаем и разархивируем последний исходники для openssl - он
потребуется при сборке

```bash
wget --no-check-certificate https://www.openssl.org/source/openssl-1.1.1s.tar.gz
tar -xvf openssl-1.1.1s.tar.gz
```

поставим все зависимости чтобы в процессе сборки не было ошибок  
```bash
yum-builddep rpmbuild/SPECS/nginx.spec
```

поправить сам spec файл чтобы NGINX собирался с необходимыми нам опциями:  

```bash
vi rpmbuild/SPECS/nginx.spec
```
добавим опцию для сборки с указанием пути к openssl 

```bash
%build
./configure %{BASE_CONFIGURE_ARGS} \
    --with-cc-opt="%{WITH_CC_OPT}" \
    --with-ld-opt="%{WITH_LD_OPT}" \
    --with-debug \
    --with-openssl=/root/openssl-1.1.1s

```
сборка __RPM__ пакета:
```bash
rpmbuild -bb rpmbuild/SPECS/nginx.spec
```

Убедимся что пакеты создались:
```bash
ll rpmbuild/RPMS/x86_64/
```
```bash
total 4128
-rw-r--r--. 1 root root 2208748 дек 24 17:23 nginx-1.22.1-1.el7.ngx.x86_64.rpm
-rw-r--r--. 1 root root 2012556 дек 24 17:23 nginx-debuginfo-1.22.1-1.el7.ngx.x86_64.rpm

```

Устанавливаем пакет и убедимся что nginx работает
```bash
yum localinstall -y \rpmbuild/RPMS/x86_64/nginx-1.22.1-1.el7.ngx.x86_64.rpm
systemctl start nginx
systemctl status nginx
```
вывод команды _systemctl status nginx_
```bash
● nginx.service - nginx - high performance web server
   Loaded: loaded (/usr/lib/systemd/system/nginx.service; disabled; vendor preset: disabled)
   Active: active (running) since Сб 2022-12-24 17:26:16 UTC; 26min ago
     Docs: http://nginx.org/en/docs/
  Process: 18590 ExecStart=/usr/sbin/nginx -c /etc/nginx/nginx.conf (code=exited, status=0/SUCCESS)
 Main PID: 18591 (nginx)
   CGroup: /system.slice/nginx.service
           ├─18591 nginx: master process /usr/sbin/nginx -c /etc/nginx/nginx.conf
           └─18592 nginx: worker process

дек 24 17:26:16 rpm systemd[1]: Starting nginx - high performance web server...
дек 24 17:26:16 rpm systemd[1]: Started nginx - high performance web server.
```


**4. Создаем свой репозиторий и размещаем там ранее собранный RPM**

Директория у NGINX по умолчанию /usr/share/nginx/html. Создадим там каталог repo:  

```bash
mkdir /usr/share/nginx/html/repo
```

Копируем туда наш собранный RPM и, например, RPM для установки jetty-server:  
```bash
cp rpmbuild/RPMS/x86_64/nginx-1.22.1-1.el7.ngx.x86_64.rpm /usr/share/nginx/html/repo/
wget http://mirror.centos.org/centos/7/os/x86_64/Packages/jetty-server-9.0.3-8.el7.noarch.rpm -O /usr/share/nginx/html/repo/jetty-server-9.0.3-8.el7.noarch.rpm
```
Проверим каталог нашего репозитория:  
```bash
ll /usr/share/nginx/html/repo

total 2496
-rw-r--r--. 1 root root  339016 июл  4  2014 jetty-server-9.0.3-8.el7.noarch.rpm
-rw-r--r--. 1 root root 2208748 дек 24 19:34 nginx-1.22.1-1.el7.ngx.x86_64.rpm


```
Инициализируем репозиторий командой:  

```bash
createrepo /usr/share/nginx/html/repo/
Spawning worker 0 with 2 pkgs
Workers Finished
Saving Primary metadata
Saving file lists metadata
Saving other metadata
Generating sqlite DBs
Sqlite DBs complete

```

Настроим в NGINX доступ к листингу каталога:  
В  файле /etc/nginx/conf.d/default.conf добавим директиву _autoindex on_. В результате секция location будет выглядеть так:  
```bash
location / {
root /usr/share/nginx/html;
index index.html index.htm;
autoindex on; 
}
```
Проверяем синтаксис и перезапускаем NGINX:  
```bash
nginx -t
nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
nginx: configuration file /etc/nginx/nginx.conf test is successful
nginx -s reload
```

Протестируем свой репозиторий  
Добавим его в **/etc/yum.repos.d:**  
```bash
cat >> /etc/yum.repos.d/otus.repo << EOF
[otus]
name=otus-linux
baseurl=http://localhost/repo
gpgcheck=0
enabled=1
EOF
```

Убедимся что репозиторий подключился и посмотрим что в нем есть:  
```bash
yum repolist enabled | grep otus
otus otus-linux 2

yum list | grep otus
nginx 1.14.1 otus
jetty-server-9.0.3-8.el7 otus
```
Установим репозиторий percona-release:  
```bash
yum install percona-release -y
```

