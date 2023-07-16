 
# Курс Administrator Linux. Professional

### Домашнее задание №18
### Работа с Docker

в ДЗ использую машину с установленым Docker

Основные команды в Docker:  

```bash
docker images - список образов 
docker ps - список запущенных контейнеров
docker ps -a  - список всех контейнеров
docker run -d -p port:port container_name  - запуск контейнера
docker stop container_name - запуск контейнера
docker logs container_name - вывод логов контейнеров
docker inspect container_name - информация по запущенному контейнеру
docker build -t dockerhub_login/reponame:ver - сборка образа 
docker push/pull  - закинуть/забрать образ на/из репозитория
docker exec -it container_name sh  - зайти в оболочку контейнера
```

**1. Написанине Dockerfile**  
  
Dockerfile это стандартное название, если файл будет иметь другое имя, то при сборке используем ключ -f  
Содержимое Dockerfile:  

```bash
FROM nginx:alpine-slim
ENV TZ=Europe/Moscow
COPY index.html /usr/share/nginx/html/
COPY logo.svg /usr/share/nginx/html/
EXPOSE 80
```
Собирается образ на основе официального NGINX  
ставим свой часовой пояс  
копирую кастомные файлы на замену дефолтных, в частности корректирую начальную страницу  
открываем порт в контейнере  

Собираем образ:  
```bash
docker build -t shark826/nginx-otus:1
```

Загружаем образ на репозиторий:  

```bash
docker push shark826/nginx-otus:1
```
Для использования кастомного образа нужно выполнить команду:  
```bash
docker run -d -p 80:80 --name ngotus shark826/nginx-otus:1
```
октройте http://localhost и увидим измененую страницу  NGINX  

![NGINX](nginxotus.png)  
  
  
либо по команде  
```bash
curl localhost
```
в конце увидим добавленные строки

```bash
<p></p>
<h1>Administrator Linux. Professional!</h1>
<p></p>
<img src="logo.svg" alt="logo-otus-ny" />
```

**2. Разница между контейнером и образом**  

*Контейнер*  
Один Docker-контейнер это 1 сервис, сами контейнеры создаются из Docker-образов. Можно вносить изменения в работающем контейнере, а потом закоммитить его и получить новый образ.  

```bash
docker commit 6e6829ad513e shark826/nginx-otus:2
```
*Образ*  
Контейнеры создаются из образов, Docker-образ используется в качестве шаблона для создания контейнеров. Несколько контейнеров c разными именами могут быть запущены, используя один и тот же образ. Сам по себе Docker-образ невозможно «запускать», запускаются контейнеры, внутри которых работает приложение. Образы можно хранить в Docker Repository, например, Docker Hub или GitLab, откуда образы можно загрузить на хостовую систему.  
Образ собирается из слоев, каждый слой собирается из своей директивы в Dockerfile'е, где директивы ENV и ARG не являются слоями и начального образа, из директивы FROM в Dockerfile.  

**3. Можно ли в контейнере собрать ядро?**  
В Docker-контейнере можно собрать ядро с произвольными патчами, флагами конфигурации и тегом, например, repository на сайте Docker Hub для сборки ядра Debian по ссылкам:  
hub https://hub.docker.com/r/tomzo/buildkernel  
git https://github.com/tomzo/docker-kernel-ide  





Example  docker-compose.yml

```code
---
version: "3.1"

services:
  dashy:
    image: lissy93/dashy:latest
    container_name: dashy
    volumes:
      - /home/roman/dashy:/app/public
#      - /home/proxy12/dashy/item-icons:/app/public/item-icons/
    ports:
      - 8090:80
    restart: unless-stopped

```




