# Курс Administrator Linux. Professional

### Домашнее задание №11

### Автоматизация администрирования. Первые шаги с Ansible

**1. Создаём виртуальную машину**  
  
Использую Vagrantfile, который в репозитории
замечания:  
:ip_addr => '192.168.56.150', - это моя подсеть которую выдал VirtualBox  

вносим в .gitingore файлы с дисками, чтоб не пушить в репозиторий

**2. Установка и настройка Ansible на хосте**  

Так как на хостовой машине мной был выбран дистрибутив Debian, то инсталяция описана по [ссылке](https://docs.ansible.com/ansible/2.7/installation_guide/intro_installation.html#latest-releases-via-apt-debian) из офицальной документцаии.  

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

**2.1 Параметры соеденения с виртуальной машиной**

```vagrant up```  
запускаем виртуальную машину  

Для подключения к хосту nginx нам необходимо будет передать множество параметров - это особенность Vagrant. Узнать эти параметры можно с помощью команды
```vagrant ssh-config```

```bash
Host nginx
  HostName 127.0.0.1
  User vagrant
  Port 2222
  UserKnownHostsFile /dev/null
  StrictHostKeyChecking no
  PasswordAuthentication no
  IdentityFile ~/.vagrant/machines/nginx/virtualbox/private_key
  IdentitiesOnly yes
  LogLevel FATAL

```
из полученных данных создадим [inventory файл](hosts) следующего содержания:

```bash
[web]
nginx ansible_host=127.0.0.1 ansible_port=2222 ansible_user=vagrant ansible_private_key_file=.vagrant/machines/nginx/virtualbox/private_key

```
Проверим, что Ansible может управлять нашим хостом. Сделать это можно с помощью команды: 
```bash
$ ansible nginx -i hosts -m ping

nginx | SUCCESS => {
    "ansible_facts": {
        "discovered_interpreter_python": "/usr/bin/python"
    },
    "changed": false,
    "ping": "pong"
}

```
Для установки дефолтных значений нужно создать файл _ansible.cfg_ в текущей дериктории проекта, либо в файле _/etc/ansible/ansible.cfg_
Отредактируем файл _/etc/ansible/ansible.cfg_ и приведем его к виду:  

```bash
[defaults]
inventory = /etc/ansible/hosts
remote_user = vagrant
host_key_checking = False
retry_files_enabled = False
```
Из файла hosts можно убрать параметр о пользователе - *ansible_user=vagrant*, т.к. мы его вписали как дефолтного  
Снова проверим связь:  

```bash
$ ansible nginx -m ping
nginx | SUCCESS => {
    "ansible_facts": {
        "discovered_interpreter_python": "/usr/bin/python"
    },
    "changed": false,
    "ping": "pong"
}
```
**3. Ad-Hoc команды**

Посмотрим какое ядро установлено на хосте:
```bash
$ ansible nginx -m command -a "uname -r"
nginx | CHANGED | rc=0 >>
3.10.0-1127.el7.x86_64
```
Проверим статус сервиса firewalld
```bash
$ ansible nginx -m systemd -a name=firewalld
nginx | SUCCESS => {
    "ansible_facts": {
        "discovered_interpreter_python": "/usr/bin/python"
    },
    "changed": false,
    "name": "firewalld",
    "status": {
        "ActiveEnterTimestampMonotonic": "0",
        "ActiveExitTimestampMonotonic": "0",
        "ActiveState": "inactive", не активен
```
Установим пакет epel-release на наш хост
```bash
$ ansible nginx -m yum -a "name=epel-release state=present" -b
nginx | CHANGED => {
    "ansible_facts": {
        "discovered_interpreter_python": "/usr/bin/python"
    },
    "changed": true,  пакет установился
    "changes": {
        "installed": [
            "epel-release"
        ]
    },
```

***4. Playbook**

**_Плейбуки_** — это базовые компоненты Ansible, которые записывают и исполняют конфигурацию Ansible. Обычно это основной способ автоматизировать набор задач, которые мы хотели бы выполнять на удалённой машине.  
Они собирают все ресурсы, которые нужны, чтобы оркестрировать упорядоченные процессы и не выполнять одни и те же действия вручную. Плейбуки можно использовать повторно и распространять. Их можно легко написать в YAML и так же легко прочитать.  

Напишем простой Playbook который будет делать установку пакета _epel-release._  
Создайте файл epel.yml со следующим содержимым:
```bash
---
- name: Install EPEL Repo
  hosts: nginx
  become: true
  tasks:
    - name: Install EPEL Repo package from standart repo
      yum:
        name: epel-release
        state: present

```

выполним Playbook:  

```bash

$ ansible-playbook epel.yml 

PLAY [Install EPEL Repo] *******************************************************

TASK [Gathering Facts] *********************************************************
ok: [nginx]

TASK [Install EPEL Repo package from standart repo] ****************************
ok: [nginx]

PLAY RECAP *********************************************************************
nginx                      : ok=2    changed=0    unreachable=0    failed=0    skipped=0    rescued=0    ignored=0   

```

**5. Playbook для установки и конфигурации веб-сервера NGINX**

