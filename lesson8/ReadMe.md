# Курс Administrator Linux. Professional

### Домашнее задание №8
### Работа с загрузчиком

в ДЗ использую виртуальную машину Centos7. Описание действий со скриншотами

**1. Попасть в систему без пароля несколькими способами**  
  
запустить виртуальную машину и при выборе ядра для загрузки нажать e - в
данном контексте edit. 

![меню загрузки](menu1_1.png)

Попадаем в окно где мы можем изменить параметры загрузки:

Способ 1. **init=/bin/sh**  
В конце строки начинающейся с *linux16* добавляем *init=/bin/sh* и нажимаем **сtrl-x** для загрузки в систему  

![параметры загрузки](menu1_2.png)

Вы попали в систему, но Рутовая файловая система при этом монтируется в режиме Read-Only. Если вы хотите перемонтировать ее в
режим Read-Write можно воспользоваться командой:  

```bash
mount -o remount,rw /
```
проверим что рутовая фс теперь доступна на запись  
```bash
 mount | grep root
 ```
![результат команд](shell1.png)


Способ 2. **rd.break**  
В конце строки начинающейся с *linux16* добавляем *rd.break* и нажимаем **сtrl-x** для загрузки в систему.  

![параметры загрузки](menu2_2.png)

Попадаем в emergency mode. Наша корневая файловая система смонтирована (опять же в режиме Read-Only), но мы не в ней.  
Пример как попасть в рутовую фс и поменять пароль администратора:  
```bash
mount -o remount,rw /sysroot
chroot /sysroot
passwd root
touch /.autorelabel
```
![результат команд](shell2_2.png)

После чего можно перезагружаться и заходить в систему с новым паролем.  

Способ 3. **rw init=/sysroot/bin/sh**

В строке начинающейся с *linux16* заменяем *ro* на *rw init=/sysroot/bin/sh* и нажимаем сtrl-x
для загрузки в систему  

![параметры загрузки](menu3_2.png)

В целом то же самое что и в прошлом примере, но файловая система сразу
смонтирована в режим Read-Write  


**2. Переименование VG**  

посмотрим текущее состояние системы:  

```bash
vgs
```
![LVM](shell_lvm1.png)

Нас интересует вторая строка с именем Volume Group  
Приступим к переименованию:  
```bash
vgrename centos OtusRoot
```
![LVM](shell_lvm2.png)


правим [/etc/fstab](fstab), [/etc/default/grub](grub), [/boot/grub2/grub.cfg](grub.gfg). Везде заменяем старое
название на новое. По ссылкам можно увидеть примеры получившихся файлов.

Пересоздаем initrd image, чтобы он знал новое название Volume Group
```bash
mkinitrd -f -v /boot/initramfs-$(uname -r).img $(uname -r)
```

![LVM](shell_lvm3.png)


После чего можем перезагружаться и если все сделано правильно успешно грузимся с новым именем Volume Group и проверяем:  

![LVM](shell_lvm4.png)


**3. Настройка клиента**  

переходим в режи root:  
```bash
su
```

Стартую script для записи действий в консоли:  
```script lesson6_client.log```  

Так же как и на сервере сделаем установку утилит:  
```bash
yum install nfs-utils -y
```  

- включаем firewall и проверяем, что он работает  

```bash
systemctl enable firewalld.service --now
systemctl status firewalld.service
```

- добавляем в _/etc/fstab_ строку_
```
echo "192.168.56.10:/srv/share/ /mnt nfs vers=3,proto=udp,noauto,x-systemd.automount 0 0" >> /etc/fstab
```
и выполняем
```bash
systemctl daemon-reload
systemctl restart remote-fs.target
```
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


