#!/bin/bash

# 注意，我们这里使用了 "echo 3"，但是不推荐使用在产品环境中，应该使用 "echo 1"

# https://linux.cn/article-5627-1.html

sync; echo 1 > /proc/sys/vm/drop_caches
sync; echo 3 > /proc/sys/vm/drop_caches 
sync; echo 2 > /proc/sys/vm/drop_caches

swapoff -a && swapon -a

ps aux | grep ssserver
ps -fC rpcbind | more
