# Курс Administrator Linux. Professional

### Домашнее задание №5
### Практические навыки работы с ZFS

**1. Создаём виртуальную машину**  
  
Использую Vagrantfile, который в репозитории    
замечания:  
:ip_addr => '192.168.56.102', - это моя подсеть которую выдал VirtualBox  
:dfile => '/home/roman/VMachines/otus/lesson5/sata1.vdi', - путь для хранения дисков, иначе из-за синхронизации общей папки долго стартует вирт.машина  

вносим в .gitingore файлы с дисками, чтоб не пушить в репозиторий

```vagrant up ```  
запускаем виртуальную машину  
  
Будет создана виртуальная машина, с 8 дисками и уже установленным и готовым к работе ZFS.  
>Прим. ZFS заработал после выполнения команды: *modprobe zfs*

Заходим на сервер:  
```vagrant ssh```  

Внутри виртуалки переходим в root пользователя:  
```sudo -i```  

Выполняю запуск утилиты script для записи действий в консоли:  
```script lesson5_1.log```  

**1. Пример определения наилучшего метода сжатия**  

Смотрим список дисков в виртуальной машине:  
```lsblk```

>Прим. выводы команд так же в репорзитории в виде скриншотов или лог-файлов утилиты script: *lesson5_1.log lesson5_2.log lesson5_3.log*

Создаём 4 пула каждый из двух дисков в режиме RAID 1:  
```
zpool create otus1 mirror /dev/sdb /dev/sdc
zpool create otus2 mirror /dev/sdd /dev/sde
zpool create otus3 mirror /dev/sdf /dev/sdg
zpool create otus4 mirror /dev/sdh /dev/sdi
```
Смотрим информацию о пулах:  
```zpool list```

Добавим разные алгоритмы сжатия в каждый пул:  
- Алгоритм lzjb:  
```zfs set compression=lzjb otus1```
- Алгоритм lz4:  
```zfs set compression=lz4 otus2```
- Алгоритм gzip:  
```zfs set compression=gzip-9 otus3```
- Алгоритм zle:  
```zfs set compression=zle otus4```

Проверим, что все файловые системы имеют разные методы сжатия:  

```zfs get all | grep compression```
```
otus1  compression           lzjb                   local
otus2  compression           lz4                    local
otus3  compression           gzip-9                 local
otus4  compression           zle                    local
```

Сжатие файлов будет работать только с файлами, которые были добавлены после включение настройки сжатия.   
Скачаем один и тот же текстовый файл во все пулы:  
```
for i in {1..4}; do wget -P /otus$i https://gutenberg.org/cache/epub/2600/pg2600.converter.log; done
```

Проверим наличие файла во всех пулах:  

```
ls -l /otus*
```
```
/otus1:
total 22033
-rw-r--r--. 1 root root 40875295 Nov  2 08:36 pg2600.converter.log

/otus2:
total 17979
-rw-r--r--. 1 root root 40875295 Nov  2 08:36 pg2600.converter.log

/otus3:
total 10952
-rw-r--r--. 1 root root 40875295 Nov  2 08:36 pg2600.converter.log

/otus4:
total 39946
-rw-r--r--. 1 root root 40875295 Nov  2 08:36 pg2600.converter.log
```

Проверим, сколько места занимает один и тот же файл в разных пулах и проверим степень сжатия файлов:  
```zfs list```
```
NAME    USED  AVAIL     REFER  MOUNTPOINT
otus1  21.6M   330M     21.5M  /otus1
otus2  17.7M   334M     17.6M  /otus2
otus3  10.8M   341M     10.7M  /otus3
otus4  39.0M   313M     38.9M  /otus4
```
```zfs get all | grep compressratio | grep -v ref```
```
otus1  compressratio         1.80x                  -
otus2  compressratio         2.21x                  -
otus3  compressratio         3.63x                  -
otus4  compressratio         1.00x                  -
```
Таким образом, у нас получается, что алгоритм gzip-9 применный в otus3 самый эффективный по сжатию.  


**2. Определение настроек пула**  

Стартую script для записи действий в консоли:  
```script lesson5_2.log```  

Скачиваем архив в домашний каталог:  

```
wget -O archive.tar.gz --no-check-certificate 'https://drive.google.com/u/0/uc?id=1KRBNW33QWqbvbVHa3hLJivOAt60yukkg&export=download'
```

Разархивируем его:  

```tar -xzvf archive.tar.gz```
```
zpoolexport/
zpoolexport/filea
zpoolexport/fileb
```

Проверим, возможно ли импортировать данный каталог в пул:  
```
zpool import -d zpoolexport/
```
Сделаем импорт данного пула к нам в ОС:  
```
zpool import -d zpoolexport/ otus
```

Далее нам нужно определить настройки  
Запрос сразу всех параметров пула:  
``zpool get all otus``  
Запрос сразу всех параметром файловой системы:  
``zfs get all otus``  


**3. Работа со снапшотом**

Стартую script для записи действий в консоли:  
```script lesson5_3.log```  

Скачаем файл:  

```
wget -O otus_task2.file --no-check-certificate 'https://drive.google.com/u/0/uc?id=1gH8gCL9y7Nd5Ti3IRmplZPF1XjzxeRAG&export=download'
```
Восстановим файловую систему из снапшота:  
```
zfs receive otus/test@today < otus_task2.file
```

Далее, ищем в каталоге /otus/test файл с именем “secret_message”:  
```
find /otus/test -name "secret_message"
```
```
/otus/test/task1/file_mess/secret_message
```

Смотрим содержимое найденного файла:  
```
cat /otus/test/task1/file_mess/secret_message
```
```
https://github.com/sindresorhus/awesome
```

пройдем по ссылке из секретной фразы
скриншот в репозитории


