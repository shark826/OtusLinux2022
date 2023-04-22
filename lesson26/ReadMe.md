# Курс Administrator Linux. Professional

### Домашнее задание №16

### Урок26 Сбор и анализ логов

**1. Создаём виртуальные машины**  
  
Использую Vagrantfile, который в репозитории
  
```vagrant up```  
запускаем виртуальные машины  
  
Будут созданы две виртуальные машины web-сервер с именем **_web_**, ip-адресом - **_192.168.56.10_** и сборщик логов с именем **_log_**, ip-адресом - **_192.168.56.11_**.  

Заходим на web-сервер:  
```vagrant ssh web```  

Внутри виртуалки переходим в root пользователя:  
```sudo -i```  

**2. Настройка времени**  

Для правильной работы c логами, нужно, чтобы на всех хостах было настроено одинаковое время.  
Укажем часовой пояс (Московское время):  

```bash
cp /usr/share/zoneinfo/Europe/Moscow /etc/localtime
```

Перезупустим службу NTP Chrony:  

```bash
systemctl restart chronyd
```

Проверим, что служба работает корректно:

```bash
systemctl status chronyd
```

![служба работает корректно](./img/Screenshot_1.png)  


Далее проверим, что время и дата указаны правильно:

```bash
date
```

![время и дата указаны правильно](./img/Screenshot_2.png)  


**Настроить NTP нужно на обоих серверах.**

**3. Установка NGINX на сервере WEB**  

Для установки nginx сначала нужно установить epel-release, а затем nginx:

```bash
yum install epel-release 
yum install -y nginx  
```

Запустим и проверим, что nginx работает корректно:

```bash
systemctl start nginx
systemctl status nginx
```

![служба nginx работает корректно](./img/Screenshot_3.png)  

Также работу nginx можно проверить на хосте. В браузере ввведем в адерсную строку <http://192.168.56.10>

**4. Настройка центрального сервера сбора логов**

Подключимся по ssh к ВМ log: _vagrant ssh log_
Перейдем в пользователя root: _sudo -i_
rsyslog должен быть установлен по умолчанию в нашёй ОС, проверим это:

```bash
yum list rsyslog
```
![служба rsyslog работает корректно](./img/Screenshot_4.png)  

Все настройки Rsyslog хранятся в файле /etc/rsyslog.conf  
Для того, чтобы наш сервер мог принимать логи, нам необходимо внести следующие изменения в файл:  
Открываем порт 514 (TCP и UDP):  
Находим и раскомментируем строки:  

```bash
# Provides UDP syslog reception
$ModLoad imudp
$UDPServerRun 514

# Provides TCP syslog reception
$ModLoad imtcp
$InputTCPServerRun 514
```

В конец файла /etc/rsyslog.conf добавляем правила приёма сообщений от хостов:  

```bash
#Add remote logs
$template RemoteLogs,"/var/log/rsyslog/%HOSTNAME%/%PROGRAMNAME%.log"
*.* ?RemoteLogs
& ~
```
Далее сохраняем файл и перезапускаем службу rsyslog: ```systemctl restart rsyslog```  
Если ошибок не допущено, то у нас будут видны открытые порты TCP,UDP 514:  

![служба rsyslog работает корректно порты открыты](./img/Screenshot_5.png)  

**5. Настройка отправки логов с web-сервера**  

Заходим на web сервер: _vagrant ssh web_  
Переходим в root пользователя: _sudo -i_  
Проверим версию nginx: _rpm -qa | grep nginx_  
>Версия nginx должна быть 1.7 или выше. В нашем примере используется версия nginx 1.20

![версия nginx](./img/Screenshot_6.png)  

В файле конфигурации web-сервера /etc/nginx/nginx.conf раздел с логами и приводим к следующему виду:  

```bash
error_log /var/log/nginx/error.log;
error_log syslog:server=192.168.56.11:514,tag=nginx_error;
...
access_log syslog:server=192.168.56.11:514,tag=nginx_access,severity=info combined;
...
```  
Для access_log указываем удаленный сервер и уровень логов, которые нужно отправлять.  
Для error_log добавляем удаленный сервер. Если требуется чтобы логи хранились локально и отправлялись на удаленный сервер, требуется указать 2 строки. 	
Tag нужен для того, чтобы логи записывались в разные файлы.  
По умолчанию, error-логи отправляют логи, которые имеют severity: error, crit, alert и emerg. Если трубуется хранить или пересылать логи с другим severity, то это также можно указать в настройках nginx. 
Далее проверяем, что конфигурация nginx указана правильно: _nginx -t_  
```bash
[root@web ~]# nginx -t
nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
nginx: configuration file /etc/nginx/nginx.conf test is successful
[root@web ~]# 
```
Далее перезапустим nginx: _systemctl restart nginx_  

Чтобы проверить, что логи ошибок также улетают на удаленный сервер, можно удалить картинку, к которой будет обращаться nginx во время открытия веб-сраницы: _rm /usr/share/nginx/html/img/header-background.png_

Попробуем несколько раз зайти по адресу **http://192.168.50.10**
Далее заходим на log-сервер и смотрим информацию об nginx:
```
cat /var/log/rsyslog/web/nginx_access.log 
cat /var/log/rsyslog/web/nginx_error.log 
```

![logs nginx](./img/Screenshot_7.png)  

**6. Настройка аудита, контролирующего изменения конфигурации nginx**  

За аудит отвечает утилита _auditd_, в Centos обычно он уже предустановлен. Проверим это:  

```bash
[vagrant@web ~]$ rpm -qa | grep audit
audit-2.8.5-4.el7.x86_64
audit-libs-2.8.5-4.el7.x86_64
[vagrant@web ~]$ 
```
Настроим аудит изменения конфигурации nginx:
Добавим правило, которое будет отслеживать изменения в конфигруации nginx. Для этого в конец файла _/etc/audit/rules.d/audit.rules_ добавим следующие строки:

![audit.rules](./img/Screenshot_8.png)  


Данные правила позволяют контролировать запись (w) и измения атрибутов (a) в файле конфигурации /etc/nginx/nginx.conf и всех файлов каталога /etc/nginx/default.d/  
Для более удобного поиска к событиям добавляется метка **nginx_conf**  
Перезапускаем службу auditd: _service auditd restart_  

После данных изменений у нас начнут локально записываться логи аудита. Чтобы проверить, что логи аудита начали записываться локально, нужно внести изменения в файл /etc/nginx/nginx.conf или поменять его атрибут, потом посмотреть информацию об изменениях: _ausearch -f /etc/nginx/nginx.conf_

![audit.rules9](./img/Screenshot_9.png)

Также можно воспользоваться поиском по файлу /var/log/audit/audit.log, указав наш тэг: 
*grep nginx_conf /var/log/audit/audit.log*

![audit.rules10](./img/Screenshot_10.png)


Далее настроим пересылку логов на удаленный сервер. Auditd по умолчанию не умеет пересылать логи, для пересылки на web-сервере потребуется установить пакет **audispd-plugins:** _yum -y install audispd-plugins_  

Найдем и поменяем следующие строки в файле /etc/audit/auditd.conf: 

![audit.rules11](./img/Screenshot_11.png)

В name_format  указываем HOSTNAME, чтобы в логах на удаленном сервере отображалось имя хоста. 
В файле /etc/audisp/plugins.d/au-remote.conf поменяем параметр _active_ на _yes_:

![audit.rules12](./img/Screenshot_12.png)

В файле /etc/audisp/audisp-remote.conf требуется указать адрес сервера и порт, на который будут отправляться логи:

![audit.rules13](./img/Screenshot_13.png)  

Перезапускаем службу auditd: _service auditd restart_
На этом настройка web-сервера завершена. 

Произведем правки на Log-сервере, скорректируем файл _/etc/audit/auditd.conf:_
раскомментируем строку ```tcp_listen_port = 60```  

Перезапустим службу auditd: _service auditd restart_  

На этом настройка пересылки логов аудита закончена. Можем попробовать поменять атрибут у файла **/etc/nginx/nginx.conf** и проверить на log-сервере, что пришла информация об изменении атрибута:  

![audit.rules14](./img/Screenshot_14.png)

Информация из файла аудита по серверу web:  

![audit.rules15](./img/Screenshot_15.png)