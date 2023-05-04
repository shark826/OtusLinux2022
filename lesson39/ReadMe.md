# Курс Administrator Linux. Professional

## Урок 39. Домашнее задание №24

### LDAP. Централизованная авторизация и аутентификация 
  
**Создаём виртуальные машины**  
  
Использую _[Vagrantfile](Vagrantfile)_, который в репозитории  
  
```vagrant up```  
запускаем виртуальные машины  
  
Будут созданы виртуальные машины:  
с именем **_ipa.otus.lan_**, ip-адресом - **_192.168.56.10_**  
с именем **_client1.otus.lan_**, ip-адресом - **_192.168.56.11_**  
с именем **_client2.otus.lan_**, ip-адресом - **_192.168.56.12_**  

с ОС CentOS 8 Stream, каждая ВМ будет иметь по 2ГБ ОЗУ и по одному ядру CPU.  

### 1. Установка FreeIPA сервера

Для начала нам необходимо настроить [FreeIPA-сервер](https://www.freeipa.org/page/About). Подключимся к нему по SSH с помощью команды: ```vagrant ssh ipa.otus.lan``` и перейдём в root-пользователя: ```sudo -i```  

>На момент написания ДЗ ОС Centos8 выдавала ошибку при обновлении или установки пакетов
>
>Решение:
>в консоли выполнить команду:  
>cd /etc/yum.repos.d/ | sed -i 's/mirrorlist/#mirrorlist/g' /etc/yum.repos.d/CentOS-* | sed -i 's|#baseurl=http://mirror.centos.org|baseurl=http://vault.centos.org|g' /etc/yum.repos.d/CentOS-*
  

Сделаем настройку FreeIPA-сервера:  
Установим часовой пояс: ```timedatectl set-timezone Europe/Moscow```  
Установим утилиту chrony: ```yum install -y chrony```  
Запустим chrony и добавим его в автозагрузку: ```systemctl enable chronyd --now```  
Выключим Firewall: ```systemctl stop firewalld```  
Отключим автозапуск Firewalld: ```systemctl disable firewalld```  
Остановим Selinux: ```setenforce 0```  
Для полного отключеия Selinux, поменяем в файле _/etc/selinux/config_, параметр _SELINUX_ на **disabled**  
```vi /etc/selinux/config```  
```bash
# This file controls the state of SELinux on the system.
# SELINUX= can take one of these three values:
#     enforcing - SELinux security policy is enforced.
#     permissive - SELinux prints warnings instead of enforcing.
#     disabled - No SELinux policy is loaded.
SELINUX=disabled
# SELINUXTYPE= can take one of these three values:
#     targeted - Targeted processes are protected,
#     minimum - Modification of targeted policy. Only selected processes are protected. 
#     mls - Multi Level Security protection.
SELINUXTYPE=targeted
```

Для дальнейшей настройки FreeIPA нам потребуется, чтобы DNS-сервер хранил запись о нашем LDAP-сервере. В рамках данной лабораторной работы мы не будем настраивать отдельный DNS-сервер и просто добавим запись в файл /etc/hosts
```vi /etc/hosts```
```bash
127.0.0.1   localhost localhost.localdomain 
127.0.1.1 ipa.otus.lan ipa
192.168.56.10 ipa.otus.lan ipa
```

Установим модуль DL1: ```yum install -y @idm:DL1```  
Установим FreeIPA-сервер: ```yum install -y ipa-server```  

Запустим скрипт установки: ```ipa-server-install```
>Потребуется указать параметры нашего LDAP-сервера, после ввода каждого параметра >нажимаем Enter, если нас устраивает параметр, указанный в квадратных скобках, то >можно сразу нажимать Enter:

Далее начнётся процесс установки. Процесс установки занимает примерно 10-15 минут (иногда время может быть другим). Если мастер успешно выполнит настройку FreeIPA то в конце мы получим сообщение: 
__*The ipa-server-install command was successful*__

При вводе параметров установки мы вводили 2 пароля:  
**Directory Manager** password — это пароль администратора сервера каталогов, У этого пользователя есть полный доступ к каталогу.  
**IPA admin** password — пароль от пользователя FreeIPA admin

Скриншоты установки:  

![скрин1](./img/Screenshot_1.png)  
![скрин2](./img/Screenshot_2.png) 
![скрин3](./img/Screenshot_3.png) 
![скрин4](./img/Screenshot_4.png) 