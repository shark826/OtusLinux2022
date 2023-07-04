#!/bin/sh

borg create -C zstd borg@10.0.0.50:drbd_repo::drbd-www-`date +%Y%m%d_%H%M%S` /mnt/otus/frontwww
borg prune --keep-last 16 borg@10.0.0.50:drbd_repo