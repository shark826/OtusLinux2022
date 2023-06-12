# Курс Administrator Linux. Professional

## Проектная работа

### Развертывание сайта на WordPress, Percona Server for MySQL, Borg Backup, Prometheus & Grafana    
  
**Создаём виртуальные машины**  
  
Использую _[Vagrantfile](Vagrantfile)_, который в репозитории  
  
```vagrant up```  
запускаем виртуальные машины  
  
Будут созданы виртуальные машины:  
с именем **_frontserver_**, ip-адресами - **_192.168.56.10 и 10.0.0.10_**  
с именем **_mysqlservernode1_**, ip-адресом - **_10.0.0.20_**  
с именем **_mysqlservernode2_**, ip-адресом - **_10.0.0.21_**  
с именем **_backupserver_**, ip-адресом - **_10.0.0.30_**  
с именем **_promethgraf_**, ip-адресом - **_10.0.0.40_**  