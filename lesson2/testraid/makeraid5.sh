#!/bin/bash

# занулить суберблоеи на дисках, на случай если они когда-то были в другом рэйде

mdadm --zero-superblock --force /dev/sd{b,c,d,e}

# удалить старые метаданные и подпись на дисках

wipefs --all --force /dev/sd{b,c,d,e}

# создаем рейд 5

mdadm --create --verbose /dev/md0 -l 5 -n 4 /dev/sd{b,c,d,e}
