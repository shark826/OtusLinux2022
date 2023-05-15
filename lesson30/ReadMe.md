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
1. Установка Web-сервера (поставим Apache)
2. Установка и настройка TFTP-сервера
3. Установка и настройка DHCP-сервера  



