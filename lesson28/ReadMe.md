# Курс Administrator Linux. Professional

## Урок 28. Домашнее задание №17

### Резервное копирование. Настраиваем бэкапы  
  
**Создаём виртуальные машины**  
  
Использую _[Vagrantfile](Vagrantfile)_, который в репозитории  
  
```vagrant up```  
запускаем виртуальные машины  
  
Будут созданы виртуальные машины:  
с именем **_client_**, ip-адресом - **_192.168.11.150_**  
с именем **_backup_**, ip-адресом - **_192.168.11.160_**  

Отработает _[скрипт](install_script.sh)_, который установит на обе машины необходимое ПО   



Заходим на машину:  
```vagrant ssh backup```

здесь нужно примонтировать второй жесткий диск:  
```bash
[root@backup ~]# mkdir /var/backup
[root@backup ~]# mount /dev/sdb1 /var/backup/
[root@backup ~]# lsblk
NAME   MAJ:MIN RM SIZE RO TYPE MOUNTPOINT
sda      8:0    0  40G  0 disk
└─sda1   8:1    0  40G  0 part /
sdb      8:16   0   2G  0 disk
└─sdb1   8:17   0   2G  0 part /var/backup
```
Создать пользователя borg и дать права на папку для бэкапа этому пользовотелю

```bash
[root@backup ~]# useradd -m borg			
[root@backup ~]# chown borg:borg /var/backup/
```

Так же на обоих машинах нужно настроить между собой ssh соединение по ключу.
На сервер backup создаем каталог ~/.ssh/ и файл authorized_keys в каталоге /home/borg и установить прав доступа к файлам и каталогам  
```bash
[root@backup ~]# su - borg
[borg@backup ~]$ mkdir .ssh
[borg@backup ~]$ touch .ssh/authorized_keys
[borg@backup ~]$ chmod 700 .ssh
[borg@backup ~]$ chmod 600 .ssh/authorized_keys
```

На ВМ client генерируем ssh-ключ и добавляем его на сервер backup  в файл authorized_keys созданным на прошлом шаге 
	# ssh-keygen



Все дальнейшие действия будут проходить на машине **_client_**