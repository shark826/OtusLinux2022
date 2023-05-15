# Курс Administrator Linux. Professional

## Урок 30. Домашнее задание №19

### Настройка PXE сервера для автоматической установки
  
**Создаём виртуальные машины**  
  
Использую _[Vagrantfile](Vagrantfile)_, который в репозитории  
  
```vagrant up```  
запускаем виртуальные машины  
  
Будут созданы виртуальные машины:  
с именем **_pxeserver_**, ip-адресом - **_192.168.56.10_**  
с именем **_pxeclient_**, ip-адресом - **_192.168.56.20_**  

перед запуском стенда нужно описать playbook файл для ansible
готовый файл ansible-playbook [./ansible/playbook-provision.yml](здесь)

Далее буду описывать секции (task) файла **playbook-provision.yml**, ansible нужен для быстрого и автоматического развертывания сервисов на серверах Linux, так же отлаженный ansible-playbook исключает ошибки так называемого _"человеческого фактора"_

Конфигурационный файл: ansible.cfg — файл описывает базовые настройки для работы Ansible:
```bash
[defaults]
#Отключение проверки ключа хоста
host_key_checking = false
#Указываем имя файла инвентаризации
inventory = hosts
#Отключаем игнорирование предупреждений
command_warnings= false
```  

Файл инвентаризации host — данный файл хранит информацию о том, как подключиться к хосту:  
```bash
[servers]
pxeserver ansible_host=192.168.56.10 ansible_user=vagrant ansible_ssh_private_key_file=.vagrant/machines/inetRouter/virtualbox/private_key 
```

Весь процесс делится на три основных части:   
1. Установка и настройка Web-сервера (поставим Apache)
2. Установка и настройка TFTP-сервера
3. Установка и настройка DHCP-сервера  

### Установка и настройка Web-сервера
Для того, чтобы отдавать файлы по HTTP нам потребуется настроенный веб-сервер.

0. Так как у CentOS 8 закончилась поддержка, для установки пакетов нам потребуется поменять репозиторий. Сделать это можно с помощью следующих команд:
```bash
  sed -i 's/mirrorlist/#mirrorlist/g' /etc/yum.repos.d/CentOS-Linux-*
  sed -i 's|#baseurl=http://mirror.centos.org|baseurl=http://vault.centos.org|g' /etc/yum.repos.d/CentOS-Linux-*
```
1. Устанавливаем Web-сервер Apache: ```yum install httpd```  
2. Далее скачиваем образ CentOS 8 
```wget https://mirror.yandex.ru/centos/8-stream/isos/x86_64/CentOS-Stream-8-x86_64-latest-boot.iso```
3. Монтируем данный образ:
```mount -t iso9660 CentOS-Stream-8-x86_64-latest-boot.iso /mnt -o loop,ro```
4. Создаём каталог /iso и копируем в него содержимое данного каталога:
```bash
mkdir /iso
cp -r /mnt/* /iso
```
5. Ставим права 755 на каталог /iso: ```chmod -R 755 /iso```
6. Настраиваем доступ по HTTP для файлов из каталога /iso:
Создаем конфигурационный файл: vi /etc/httpd/conf.d/pxeboot.conf
Добавляем следующее содержимое в файл:
Alias /centos8 /iso
#Указываем адрес директории /iso
<Directory /iso>
    Options Indexes FollowSymLinks
    #Разрешаем подключения со всех ip-адресов
    Require all granted
Перезапускаем веб-сервер: systemctl restart httpd
Добавляем его в автозагрузку: systemctl enable httpd
1. Проверяем, что веб-сервер работает и каталог /iso доступен по сети:
С вашего компьютера сначала подключаемся к тестовой странице Apache: 
Если страница открылась, значит веб-сервер запустился

Далее проверям доступность файлов по сети:
Если файлы доступны, значит веб-сервер настроен корректно


