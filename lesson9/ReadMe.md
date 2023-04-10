# Курс Administrator Linux. Professional

## Урок 9. Домашнее задание №8
### Работа с Systemd  
  
  
**Создаём виртуальную машину**  
  
Использую _[Vagrantfile](Vagrantfile)_, который в репозитории  и файл скрипт _[sysd_script.sh](sysd_script.sh)_, который установит необходимые пакеты для выполнения ДЗ   
  
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


Приводим файл /etc/sysconfig/spawn-fcgi к след виду:  
```bash
cat /etc/sysconfig/spawn-fcgi

# You must set some working options before the "spawn-fcgi" service will work.
# If SOCKET points to a file, then this file is cleaned up by the init script.
#
# See spawn-fcgi(1) for all possible options.
#
# Example :
SOCKET=/var/run/php-fcgi.sock
OPTIONS="-u apache -g apache -s $SOCKET -S -M 0600 -C 32 -F 1 -- /usr/bin/php-cgi"
```

В каталоге /etc/systemd/system создаём юнит-файл spawn-fcgi.service:  

```bash
cat /etc/systemd/system/spawn-fcgi.service

[Unit]
Description=Spawn-fcgi startup service by Otus
After=network.target

[Service]
Type=simple
PIDFile=/var/run/spawn-fcgi.pid
EnvironmentFile=/etc/sysconfig/spawn-fcgi
ExecStart=/usr/bin/spawn-fcgi -n $OPTIONS
KillMode=process

[Install]
WantedBy=multi-user.target
```

Убеждаемся что все успешно работает:  

```bash
systemctl start spawn-fcgi

systemctl status spawn-fcgi
● spawn-fcgi.service - Spawn-fcgi startup service by Otus
   Loaded: loaded (/etc/systemd/system/spawn-fcgi.service; disabled; vendor preset: disabled)
   Active: active (running) since Sun 2023-02-19 08:36:41 UTC; 9s ago
 Main PID: 2542 (php-cgi)
   CGroup: /system.slice/spawn-fcgi.service
           ├─2542 /usr/bin/php-cgi
           ├─2543 /usr/bin/php-cgi
           ├─2544 /usr/bin/php-cgi
           ├─2545 /usr/bin/php-cgi
           ├─2546 /usr/bin/php-cgi
           ├─2547 /usr/bin/php-cgi
           ├─2548 /usr/bin/php-cgi
           ├─2549 /usr/bin/php-cgi
           ├─2550 /usr/bin/php-cgi
           ├─2551 /usr/bin/php-cgi
           ├─2552 /usr/bin/php-cgi
           ├─2553 /usr/bin/php-cgi
           ├─2554 /usr/bin/php-cgi
           ├─2555 /usr/bin/php-cgi
           ├─2556 /usr/bin/php-cgi
           ├─2557 /usr/bin/php-cgi
           ├─2558 /usr/bin/php-cgi
           ├─2559 /usr/bin/php-cgi
           ├─2560 /usr/bin/php-cgi
           ├─2561 /usr/bin/php-cgi
           ├─2562 /usr/bin/php-cgi
           ├─2563 /usr/bin/php-cgi
           ├─2564 /usr/bin/php-cgi
           ├─2565 /usr/bin/php-cgi
           ├─2566 /usr/bin/php-cgi
           ├─2567 /usr/bin/php-cgi
           ├─2568 /usr/bin/php-cgi
           ├─2569 /usr/bin/php-cgi
           ├─2570 /usr/bin/php-cgi
           ├─2571 /usr/bin/php-cgi
           ├─2572 /usr/bin/php-cgi
           ├─2573 /usr/bin/php-cgi
           └─2574 /usr/bin/php-cgi

Feb 19 08:36:41 sysd-less9 systemd[1]: Started Spawn-fcgi startup service by Otus.

```

### Часть 3. Дополнить юнит-файл apache httpd возможностью запустить несколько инстансов сервера с разными конфигами

Для запуска нескольких экземпляров сервиса будем использовать шаблон в конфигурации файла окружения, который находится в каталоге /usr/lib/systemd/system. Файл называется httpd.service, переименуем его в httpd@service.  
В строке EnvironmentFile=/etc/sysconfig/httpd-%i добавим спецификатор %i для подстановки имён инстансов.  

```bash
cat /usr/lib/systemd/system/httpd@.service 

[Unit]
Description=The Apache HTTP Server
After=network.target remote-fs.target nss-lookup.target
Documentation=man:httpd(8)
Documentation=man:apachectl(8)

[Service]
Type=notify
EnvironmentFile=/etc/sysconfig/httpd-%i 
ExecStart=/usr/sbin/httpd $OPTIONS -DFOREGROUND
ExecReload=/usr/sbin/httpd $OPTIONS -k graceful
ExecStop=/bin/kill -WINCH ${MAINPID}
# We want systemd to give httpd some time to finish gracefully, but still want
# it to kill httpd after TimeoutStopSec if something went wrong during the
# graceful stop. Normally, Systemd sends SIGTERM signal right after the
# ExecStop, which would kill httpd. We are sending useless SIGCONT here to give
# httpd time to finish.
KillSignal=SIGCONT
PrivateTmp=true

[Install]
WantedBy=multi-user.target
```

В каталоге /etc/sysconfig создаём два файла окружения httpd-first и httpd-second, в которых задается опция для запуска веб-сервера с необходимым конфигурационным файлом:  

```bash
cat /etc/sysconfig/httpd-first 

#
# This file can be used to set additional environment variables for
# the httpd process, or pass additional options to the httpd
# executable.
#
# Note: With previous versions of httpd, the MPM could be changed by
# editing an "HTTPD" variable here.  With the current version, that
# variable is now ignored.  The MPM is a loadable module, and the
# choice of MPM can be changed by editing the configuration file
# /etc/httpd/conf.modules.d/00-mpm.conf.
# 

#
# To pass additional options (for instance, -D definitions) to the
# httpd binary at startup, set OPTIONS here.
#
OPTIONS="-f /etc/httpd/conf/first.conf"

#
# This setting ensures the httpd process is started in the "C" locale
# by default.  (Some modules will not behave correctly if
# case-sensitive string comparisons are performed in a different
# locale.)
#
LANG=C

```

```bash
cat /etc/sysconfig/httpd-second

#
# This file can be used to set additional environment variables for
# the httpd process, or pass additional options to the httpd
# executable.
#
# Note: With previous versions of httpd, the MPM could be changed by
# editing an "HTTPD" variable here.  With the current version, that
# variable is now ignored.  The MPM is a loadable module, and the
# choice of MPM can be changed by editing the configuration file
# /etc/httpd/conf.modules.d/00-mpm.conf.
# 

#
# To pass additional options (for instance, -D definitions) to the
# httpd binary at startup, set OPTIONS here.
#
OPTIONS="-f /etc/httpd/conf/second.conf"

#
# This setting ensures the httpd process is started in the "C" locale
# by default.  (Some modules will not behave correctly if
# case-sensitive string comparisons are performed in a different
# locale.)
#
LANG=C


```


В каталоге с конфигурацией httpd /etc/httpd/conf создаем два файла конфигурации first.conf и second.conf. В каждом из них сменим порт сервера  и путь к PidFile. В конфигурационном файле first.conf оставляем порт по умолчанию 80 и прописываем путь до пид-файла /var/run/httpd-first.pid. В файле second.conf порт меняем на 8080 и путь до пид-файла на /var/run/httpd-second.pid.  

> ниже приведены только измененые строки в дефолтном конфиге  

```bash
cat /etc/httpd/conf/first.conf

PidFile /var/run/httpd-first.pid
Listen 80

```

```bash
cat /etc/httpd/conf/second.conf 

PidFile /var/run/httpd-second.pid
Listen 8080

```

Запустим и проверим работу:  

```bash
systemctl start httpd@first.service
systemctl start httpd@second.service
ss -tnulp | grep httpd

tcp    LISTEN     0      128    [::]:8080               [::]:*                   users:(("httpd",pid=1130,fd=4),("httpd",pid=1129,fd=4),("httpd",pid=1128,fd=4),("httpd",pid=1127,fd=4),"httpd",pid=1126,fd=4),("httpd",pid=1125,fd=4),("httpd",pid=1124,fd=4))
tcp    LISTEN     0      128    [::]:80                 [::]:*                   users:(("httpd",pid=1083,fd=4),("httpd",pid=1082,fd=4),("httpd",pid=1081,fd=4),("httpd",pid=1080,fd=4),("httpd",pid=1079,fd=4),("httpd",pid=1078,fd=4),("httpd",pid=1077,fd=4))
```
