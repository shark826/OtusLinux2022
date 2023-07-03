#!/bin/sh

borg create -C zstd borg@10.0.0.50:front_repo::front-etc-`date +%Y%m%d_%H%M%S` /etc/
borg prune --keep-last 24 borg@10.0.0.50:front_repo