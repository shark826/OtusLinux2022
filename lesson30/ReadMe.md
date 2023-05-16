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
Создаем конфигурационный файл: ```vi /etc/httpd/conf.d/pxeboot.conf```  
Добавляем следующее содержимое в файл[./ansible/templates/pxeboot.conf](pxeboot).conf:  
```bash
Alias /centos8 /iso
#Указываем адрес директории /iso
<Directory /iso>
    Options Indexes FollowSymLinks
    #Разрешаем подключения со всех ip-адресов
    Require all granted
```  
Перезапускаем веб-сервер: ```systemctl restart httpd```  
Добавляем его в автозагрузку: ```systemctl enable httpd```  
7. Проверяем, что веб-сервер работает и каталог /iso доступен по сети:
С вашего компьютера в браузере ввдем адрес:  
```http://127.0.0.1:8081/centos8```   

Если файлы доступны, значит веб-сервер настроен корректно


Начало сценария для ansible [./ansible/playbook-provision-part1.yml](тут)

### Установка и настройка TFTP-сервера   

TFTP-сервер потребуется для отправки первичных файлов загрузки (vmlinuz, initrd.img и т. д.)

1. Устанавливаем tftp-сервер: ```yum install tftp-server```
2. Запускаем службу: ```systemctl start tftp.service```
3. Рабочий каталог **/var/lib/tftpboot** где будут храниться файлы, которые будет отдавать TFTP-сервер.
4. Автозапуск службы: ```systemctl enable tftp.service```
5. Созаём каталог, в котором будем хранить наше меню загрузки:
```mkdir /var/lib/tftpboot/pxelinux.cfg```
6. Создаём меню-файл: ```vi /var/lib/tftpboot/pxelinux.cfg/default```
Содержимое файла:  
```bash
default menu.c32
prompt 0
#Время счётчика с обратным отсчётом (установлено 15 секунд)
timeout 15
#Параметр использования локального времени
ONTIME local
#Имя «шапки» нашего меню
menu title OTUS-Linux PXE Boot Menu
       #Описание первой строки
       label 1
       #Имя, отображаемое в первой строке
       menu label ^ Graph install CentOS 8.4
       #Адрес ядра, расположенного на TFTP-сервере
       kernel /vmlinuz
       #Адрес файла initrd, расположенного на TFTP-сервере
       initrd /initrd.img
       #Получаем адрес по DHCP и указываем адрес веб-сервера
       append ip=eth1:dhcp inst.repo=http://10.0.0.20/centos8
       label 2
       menu label ^ Text install CentOS 8.4
       kernel /vmlinuz
       initrd /initrd.img
       append ip=eth1:dhcp inst.repo=http://10.0.0.20/centos8 text
       label 3
       menu label ^ rescue installed system
       kernel /vmlinuz
       initrd /initrd.img
       append ip=eth1:dhcp inst.repo=http://10.0.0.20/centos8 rescue

```
7. Распакуем файл syslinux-tftpboot-6.04-5.el8.noarch.rpm:
rpm2cpio /iso/BaseOS/Packages/syslinux-tftpboot-6.04-5.el8.noarch.rpm | cpio -dimv

8. После распаковки в каталоге пользователя root будет создан каталог tftpboot из которого потребуется скопировать следующие файлы:
- pxelinux.0
- ldlinux.c32
- libmenu.c32
- libutil.c32
- menu.c32
- vesamenu.c32
```bash
cd tftpboot
cp pxelinux.0 ldlinux.c32 libmenu.c32 libutil.c32 menu.c32 vesamenu.c32 /var/lib/tftpboot/
```
9. Также в каталог /var/lib/tftpboot/ нам потребуется скопировать файлы initrd.img и vmlinuz, которые располагаются в каталоге /iso/images/pxeboot/:
```cp /iso/images/pxeboot/{initrd.img,vmlinuz} /var/lib/tftpboot/```  
10. Далее перезапускаем TFTP-сервер и добавляем его в автозагрузку:
```bash
systemctl restart tftp.service 
systemctl enable tftp.service
```
СценариЙ для Настройки TFTP-сервера ansible [./ansible/playbook-provision-part2.yml](тут)   


### Установка и настройка DHCP-сервера   

1. Устанавливаем DHCP-сервер: yum install dhcp-server
2. Правим конфигурационный файл: vi /etc/dhcp/dhcpd.conf
```bash
option space pxelinux;
option pxelinux.magic code 208 = string;
option pxelinux.configfile code 209 = text;
option pxelinux.pathprefix code 210 = text;
option pxelinux.reboottime code 211 = unsigned integer 32;
option architecture-type code 93 = unsigned integer 16;

#Указываем сеть и маску подсети, в которой будет работать DHCP-сервер
subnet 10.0.0.0 netmask 255.255.255.0 {
        #Указываем шлюз по умолчанию, если потребуется
        #option routers 10.0.0.1;
        #Указываем диапазон адресов
        range 10.0.0.100 10.0.0.120;

        class "pxeclients" {
          match if substring (option vendor-class-identifier, 0, 9) = "PXEClient";
          #Указываем адрес TFTP-сервера
          next-server 10.0.0.20;
          #Указываем имя файла, который надо запустить с TFTP-сервера
          filename "pxelinux.0";
        }
}
```   

СценариЙ для Настройки DHCP-сервера ansible [./ansible/playbook-provision-part3.yml](тут)   