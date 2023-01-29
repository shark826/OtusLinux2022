# Курс Administrator Linux. Professional

### Домашнее задание №11
### Автоматизация администрирования. Первые шаги с Ansible.

**1. Создаём виртуальную машину**  
  
Использую Vagrantfile, который в репозитории    
замечания:  
:ip_addr => '192.168.56.102', - это моя подсеть которую выдал VirtualBox  


вносим в .gitingore файлы с дисками, чтоб не пушить в репозиторий

```vagrant up ```  
запускаем виртуальную машину  
 
Заходим на сервер:  
```vagrant ssh```  

Внутри виртуалки переходим в root пользователя:  
```sudo -i```  

**2. Установка и нстройка Ansible**  

Так как мной был выбран дистрибутив Debian, то инсталяция описана по [ссылке](https://docs.ansible.com/ansible/2.7/installation_guide/intro_installation.html#latest-releases-via-apt-debian) из офицальной документцаии.  

Добавим репозиторий Ansible 
```deb http://ppa.launchpad.net/ansible/ansible/ubuntu trusty main```  

```bash
sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 93C4A3FD7BB9C367
sudo apt-get update
sudo apt-get install ansible
```
