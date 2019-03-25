# 路由器端安装和配置

## 安装

```sh
wget -c https://github.com/gzhu-team-509/Akane313.2/archive/master.zip -O akane.zip
unzip -o akane.zip
rm -f akane.zip
cd Akane313.2-master/router && chmod +x *.sh
```

## 配置

1. 配置KEY和HOST

    将`example.key`和`example.host`分别重命名为`key`和`host`，并修改其中信息。

2. `traffic.sh` 流量统计信息

    配置Crontab定时运行脚本。

    ```crontab
    # Send interface traffic counters to host
    30 23 * * * /path/to/traffic.sh
    ```
