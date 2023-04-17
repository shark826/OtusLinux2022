# Курс Administrator Linux. Professional

## Урок 24. Домашнее задание №15

### Пользователи и группы. Авторизация и аутентификация. PAM  
  
**Создаём виртуальную машину**  
  
Использую _[Vagrantfile](Vagrantfile)_, который в репозитории  
  
```vagrant up```  
запускаем виртуальную машину  
  
Будет создана виртуальная машина с именем **_pam_**, ip-адресом - **_192.168.11.150_**

Заходим на машину:  
```vagrant ssh pam```
  
### Настройка запрета для всех пользователей (кроме группы Admin) логина в выходные дни (Праздники не учитываются)

1. Внутри виртуалки переходим в root пользователя:  
```sudo -i```  
2. Создаём пользователя otusadm и otus. Задаём пользователям одинаковые пароли:  

```bash
 sudo useradd otusadm && sudo useradd otus    
 echo "Otus2023!" | sudo passwd --stdin otusadm && echo "Otus2023!" | sudo passwd --stdin otus
```

3. Создаём группу admin и добавляем в нее пользователей vagrant,root и otusadm :  

```bash
sudo groupadd -f admin  
usermod otusadm -a -G admin && usermod root -a -G admin && usermod vagrant -a -G admin
```

>*Обратите внимание, что мы просто добавили пользователя otusadm в группу admin.*  
>**Это не делает пользователя otusadm администратором.**