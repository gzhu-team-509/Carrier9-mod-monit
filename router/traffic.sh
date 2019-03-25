#!bin/sh

# 将当前的流量统计信息发送到服务器

HOST=`cat host | xargs`  # 获取HOST，并借助xargs移除其中空白字符
KEY=`cat key | xargs`    # 获取KEY

RX_BYTES=`cat /sys/devices/virtual/net/br-lan/statistics/rx_bytes`
TX_BYTES=`cat /sys/devices/virtual/net/br-lan/statistics/tx_bytes`

curl -s -o /dev/null "http://$HOST/?rx=$RX_BYTES&tx=$TX_BYTES&key=$KEY"
