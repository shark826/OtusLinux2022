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

Выполняю запуск утилиты script для записи действий в консоли:  
```script lesson7.log```  

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
  

└── rpmbuild  
    ├── BUILD  
    ├── BUILDROOT  
    ├── RPMS  
    ├── SOURCES  
    ├── SPECS  
    └── SRPMS  
  
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
yum localinstall -y \rpmbuild/RPMS/x86_64/nginx-1.14.1-1.el7_4.ngx.x86_64.rpm
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


все вышеописаные команды записываю в скрипт **_nfss_script.sh_**



Стартую script для записи действий в консоли:  
```script lesson6_client.log```  


все вышеописаные команды включаю в скрипт **_nfsc_script.sh_**

**4. Удаление виртуальных машин и автоматизация стенда NFS**

Удаляю виртуалки

```
vagrant destroy nfss
vagrant destroy nfsc
```

Добавляем в Vagrantfile_ver0 ссылки на скрипты

у сервера  
```
nfss.vm.provision "shell", path: "nfss_script.sh"
```
  
у клиента  
```
nfss.vm.provision "shell", path: "nfsc_script.sh"
```


