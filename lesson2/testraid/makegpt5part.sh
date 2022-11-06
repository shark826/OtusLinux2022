#!/bin/bash

DEVICE=/dev/md0
for i in {1..5} ; do sgdisk -n ${i}:0:+25M $DEVICE ; done
lsblk
