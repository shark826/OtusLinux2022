# Курс Administrator Linux. Professional

### Домашнее задание №6
### Vagrant стенд для NFS

в ДЗ я сначала проведу настройку сервера и клиента в ручном режиме, а затем все команды на сервере и клиенте запишу в скрипты, которые будут исполняться при запуске Vagrant, используя измененый Vagrantfile.

**1. Создаём виртуальную машину**  
  
Использую Vagrantfile_ver0, который в репозитории    
  
  

```vagrant up ```  
запускаем виртуальную машину  
  
Будут созданы две виртуальные машины сервер с именем **_nfss_**, ip-адресом - **_192.168.56.10_** и клиент с именем **_nfsc_**, ip-адресом - **_192.168.56.11_**.  

Заходим на сервер:  
```vagrant ssh nfss```  

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

Стартую script для записи действий в консоли:  
```script lesson6_client.log```  


все вышеописаные команды включаю в скрипт **_nfsc_script.sh_**

**4. Удаление виртуальных машин и автоматизация стенда NFS**

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


