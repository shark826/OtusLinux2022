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

```bash
rpm -i nginx-1.22.1-1.el7.ngx.src.rpm
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


