# Курс Administrator Linux. Professional

### Домашнее задание №8
### Работа с Systemd  
  
  
**Создаём виртуальную машину**  
  
Использую _Vagrantfile_, который в репозитории  и файл скрипт _sysd_script.sh_, который установит необходимые пакеты для выполнения ДЗ   
  
```vagrant up ```  
запускаем виртуальную машину  
  
Будет создана виртуальная машина с именем **_sysd-less9_**, ip-адресом - **_192.168.56.10_** 

Заходим на машину:  
```vagrant ssh sysd-less9```    
  
## Часть 1. Пишем свой сервис


Внутри виртуалки переходим в root пользователя:  
```sudo -i```  

Выполняю запуск утилиты script для записи действий в консоли:  
```script lesson6_server.log```  

**2. Настройка сервера**  

Установка утилит:  
```bash
yum install nfs-utils
```  

- включаем firewall и проверяем, что он работает  

```bash
systemctl enable firewalld.service --now
systemctl status firewalld.service
```
- разрешаем в firewall доступ к сервисам NFS  
```bash
firewall-cmd --add-service="nfs3" \
            --add-service="rpc-bind" \
             --add-service="mountd" \
             --permanent
firewall-cmd --reload
```
- включаем сервер NFS и проверяем статус  
```bash
systemctl enable nfs --now
systemctl status nfs
```
- проверяем наличие слушаемых портов 2049/udp, 2049/tcp, 20048/udp, 20048/tcp, 111/udp, 111/tcp  
```bash
ss -tnplu
```
- создаём и настраиваем директорию, которая будет экспортирована в будущем  
```bash
mkdir -p /srv/share/upload
chown -R nfsnobody:nfsnobody /srv/share
chmod 0777 /srv/share/upload
```
- создаём в файле __/etc/exports__ структуру, которая позволит экспортировать ранее созданную директорию
```bash
cat << EOF > /etc/exports
/srv/share 192.168.56.11/32(rw,sync,root_squash)
EOF
```
- экспортируем ранее созданную директорию
```bash
exportfs -r
```
- проверяем экспортированную директорию следующей командой
```bash
exportfs -s
```

все вышеописаные команды записываю в скрипт **_nfss_script.sh_**

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


