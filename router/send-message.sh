#!/bin/sh

# 发送心跳包，
# 并将当前的流量统计信息发送到服务器。

HOST=`cat host | xargs`  # 获取HOST，并借助xargs移除其中空白字符。
KEY=`cat key | xargs`    # 获取KEY。

# 获取对局域网外意义下的上行流量（br-lan的rx_bytes）和下行流量（br-lan的tx_bytes）。
RX_BYTES=`cat /sys/devices/virtual/net/br-lan/statistics/rx_bytes`
TX_BYTES=`cat /sys/devices/virtual/net/br-lan/statistics/tx_bytes`

# 心跳并发送流量统计信息。
curl -L "http://$HOST/?key=$KEY&rx=$RX_BYTES&tx=$TX_BYTES"
