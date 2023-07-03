#!/bin/sh

mysqldump wordpres > /mnt/wpdatabase.sql

borg create -C zstd borg@10.0.0.50:mysql_repo::mysql-dump-`date +%Y%m%d_%H%M%S` /mnt/wpdatabase.sql
borg prune --keep-last 24 borg@10.0.0.50:mysql_repo