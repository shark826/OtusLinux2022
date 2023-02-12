# Курс Administrator Linux. Professional

## Урок 9. Домашнее задание №8
### Работа с Systemd  
  
  
**Создаём виртуальную машину**  
  
Использую _Vagrantfile_, который в репозитории  и файл скрипт _[sysd_script.sh](sysd_script.sh)_, который установит необходимые пакеты для выполнения ДЗ   
  
```vagrant up ```  
запускаем виртуальную машину  
  
Будет создана виртуальная машина с именем **_sysd-less9_**, ip-адресом - **_192.168.56.10_** 

Заходим на машину:  
```vagrant ssh sysd-less9```    
  
### Часть 1. Пишем свой сервис

Стоит задача написать сервис, который будет раз в 30 секунд мониторить логфайл на предмет наличия ключевого слова. Файл и слово должны задаваться в _/etc/sysconfig_  


Внутри виртуалки переходим в root пользователя:  
```sudo -i```  

Создаём файл с конфигурацией для сервиса в директории /etc/sysconfig - из неё сервис будет брать необходимые переменные.  

```bash
cat /etc/sysconfig/watchlog

# Configuration file for my watchdog service
# Place it to /etc/sysconfig
# File and word in that file that we will be monit
WORD="ALERT"
LOG=/var/log/watchlog.log
```
Затем создаем /var/log/watchlog.log и пишем туда строки на своё усмотрение, плюс ключевое слово ‘ALERT’  

```bash
cat /var/log/watchlog.log

Start-Date: 2023-02-02  20:28:14
Commandline: packagekit role='update-packages'
ALERT
ALERT
Install: docker-compose-plugin
Upgrade: docker-ce-cli:amd64 
End-Date: 2023-02-02  20:28:22
```

Создим скрипт watchlog.sh, который с помощью команды logger отправляет лог в системный журнал:  

```bash
cat /opt/watchlog.sh

#!/bin/bash
WORD=$1
LOG=$2
DATE=`date`
if grep $WORD $LOG &> /dev/null
then
logger "$DATE: I found word, Master!"
else
exit 0
fi
```
В каталоге /etc/systemd/system создаем юнит для сервиса watchlog.service:  

```bash
cat /etc/systemd/system/watchlog.service 

[Unit]
Description=My watchlog service

[Service]
Type=oneshot
EnvironmentFile=/etc/sysconfig/watchlog
ExecStart=/opt/watchlog.sh $WORD $LOG
```

Там же создаем юнит для таймера watchlog.timer:  

```bash
cat /etc/systemd/system/watchlog.timer   

[Unit]
Description=Run watchlog script every 30 second

[Timer]
#Run every 30 second
OnUnitActiveSec=30
Unit=watchlog.service

[Install]
WantedBy=multi-user.target
```

Запускаем таймер: systemctl start watchlog.timer и проверяем вывод:  

```bash
systemctl start watchlog.timer
tail -f /var/log/messages

Feb 12 11:39:37 sysd-less9 systemd: Starting My watchlog service...
Feb 12 11:39:37 sysd-less9 root: Sun Feb 12 11:39:17 UTC 2023: I found word, Master!
Feb 12 11:39:37 sysd-less9 systemd: Started My watchlog service.
Feb 12 11:40:08 sysd-less9 systemd: Starting My watchlog service...
Feb 12 11:40:08 sysd-less9 root: Sun Feb 12 11:40:08 UTC 2023: I found word, Master!
Feb 12 11:40:08 sysd-less9 systemd: Started My watchlog service.
Feb 12 11:40:38 sysd-less9 systemd: Starting My watchlog service...
Feb 12 11:40:38 sysd-less9 root: Sun Feb 12 11:41:08 UTC 2023: I found word, Master!
Feb 12 11:40:38 sysd-less9 systemd: Started My watchlog service.
Feb 12 11:41:08 sysd-less9 systemd: Starting My watchlog service...
Feb 12 11:41:08 sysd-less9 root: Sun Feb 12 11:41:48 UTC 2023: I found word, Master!
Feb 12 11:41:08 sysd-less9 systemd: Started My watchlog service.
```

### Часть 2. Из epel установить spawn-fcgi и переписать init-скрипт на unit-файл.



- заходим в директорию `/mnt/` и проверяем успешность монтирования
```bash
mount | grep mnt
```

все вышеописаные команды включаю в скрипт **_nfsc_script.sh_**


**4. Проверка работоспособности**

- заходим на сервер
- заходим в каталог `/srv/share/upload`
- создаём тестовый файл `touch check_file`
![Создаём тестовый файл](server.png)
- заходим на клиент
- заходим в каталог `/mnt/upload`
- проверяем наличие ранее созданного файла
- создаём тестовый файл `touch client_file`
- проверяем, что файл успешно создан
![Клиент тестовый файл](client.png)

**5. Удаление виртуальных машин и автоматизация стенда NFS**

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

переименовываю файл Vagrantfile_ver0 в Vagrantfile для старта автоматизированного стенда


