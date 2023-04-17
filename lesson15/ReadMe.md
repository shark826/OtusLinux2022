# Курс Administrator Linux. Professional

### Домашнее задание №11

### Автоматизация администрирования. Первые шаги с Ansible

**1. Создаём виртуальную машину**  
  
Использую Vagrantfile, который в репозитории
замечания:  
:ip_addr => '192.168.56.102', - это моя подсеть которую выдал VirtualBox  

вносим в .gitingore файлы с дисками, чтоб не пушить в репозиторий

```vagrant up```  
запускаем виртуальную машину  

Заходим на сервер:  
```vagrant ssh```  

Внутри виртуалки переходим в root пользователя:  
```su```  
обновим систему:  

```apt update && apt dist-upgrade -y```  

**2. Установка и нстройка Ansible**  

Так как мной был выбран дистрибутив Debian, то инсталяция описана по [ссылке](https://docs.ansible.com/ansible/2.7/installation_guide/intro_installation.html#latest-releases-via-apt-debian) из офицальной документцаии.  

Добавим репозиторий Ansible  
```deb http://ppa.launchpad.net/ansible/ansible/ubuntu trusty main```  
установим ключи от репозитория, обновим кэш пакетов и установим Ansible  

```bash
sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 93C4A3FD7BB9C367
sudo apt-get update
sudo apt-get install ansible
```

> если при добавлении ключа выйдет ошибка:  
> E: gnupg, gnupg2 and gnupg1 do not seem to be installed, but one of them is required for this operation  
> то нужно проинсталировать пакеты **gnupg, gnupg2 and gnupg1**

после успешной установки, проверим версию Ansible

```ansible --version```

на экран выйдет информация:  

```bash
ansible 2.10.8
  config file = None
  configured module search path = ['/root/.ansible/plugins/modules', '/usr/share/ansible/plugins/modules']
  ansible python module location = /usr/lib/python3/dist-packages/ansible
  executable location = /usr/bin/ansible
  python version = 3.9.2 (default, Feb 28 2021, 17:03:44) [GCC 10.2.1 20210110]
```
