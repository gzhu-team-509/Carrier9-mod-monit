#!/bin/sh

# 发送心跳包

HOST=`cat host | xargs`  # 获取HOST，并借助xargs移除其中空白字符
KEY=`cat key | xargs`    # 获取KEY

curl -s -o /dev/null "http://$HOST/?heartbeat=1&key=$KEY"
