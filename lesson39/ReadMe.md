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

с ОС CentOS 9 Stream, каждая ВМ будет иметь по 1ГБ ОЗУ и по одному ядру CPU.  

### 1. Установка FreeIPA сервера

Для начала нам необходимо настроить [FreeIPA-сервер](https://www.freeipa.org/page/About). Подключимся к нему по SSH с помощью команды: ```vagrant ssh ipa.otus.lan``` и перейдём в root-пользователя: ```sudo -i```  

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


