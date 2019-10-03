<?php

/**
 * 路由器状态页面
 */


require 'config.php';


error_reporting(E_ALL);
ini_set('display_errors', 'On');
date_default_timezone_set('Asia/Shanghai');


// `println($str)`输出`$str`并附加换行符。
function println($str) {
    print(strval($str)."\n");
}


$mysqli = new mysqli($GLOBALS['DB_HOST'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASS'], $GLOBALS['DB_NAME']);


// 如果请求中带有key字段，则尝试验证key。
if (isset($_REQUEST['key'])) {
    if ($_REQUEST['key'] != $GLOBALS['KEY']) {
        println('Invalid Key.');
        return;
    }

    println('Key Accepted.');

    // 记录心跳。
    if (isset($_REQUEST['heartbeat']) && $_REQUEST['heartbeat']) {
        $date = date('Y-m-d H:i:s');
        $sql = "REPLACE INTO `status` (`name`, `value`) VALUES ('heartbeat', '$date');";
        $mysqli->query($sql);
       
        println('Hearbeat updated: '."$date");
    }

    // 记录上行流量和下行流量。
    if (isset($_REQUEST['rx']) && isset($_REQUEST['tx'])) {
        // 获取本次汇报的RX和TX。
        $rx = $_REQUEST['rx']; $tx = $_REQUEST['tx'];

        // 获取上次汇报的RX和TX。
        $last_rx = 0; $last_tx = 0;
        {
            $sql = "SELECT `name`, `value` FROM `status` WHERE `name`='last-rx' OR `name`='last-tx';"
            if ($result = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC)) {
                foreach ($result as $line) {
                    if ($line['name'] == 'last-rx') {
                        $last_rx = intval($line['value']);
                    }
                    if ($line['name'] == 'last-tx') {
                        $last_tx = intval($line['value']);
                    }
                }
                $result->close();
            }
        }
        println("$last_rx $last_tx");
        
        // 计算汇报间的RX和TX增量。


        // 获取到目前为止，今天的的RX和TX。
        $today_rx = 0;
        $today_tx = 0;

        // 更新今天的RX和TX。

        $sql = "INSERT INTO `traffic` (`date`, `tx_bytes`, `rx_bytes`) VALUES (CURRENT_DATE, $tx, $rx);";
        $mysqli->query($sql);
    }

    return;  // 对于带有key字段的请求，在此处结束脚本，不输出html页面。
}

// 查询最近14天内的流量数据，数据按日期升序排列。
$traffic_records = $mysqli->query("SELECT * FROM (SELECT * FROM `traffic` ORDER BY `date` DESC LIMIT 14) AS `traffic` ORDER BY `date` ASC;")->fetch_all(MYSQLI_ASSOC);
$traffic_data = ['date' => array(), 'tx_bytes' => array(), 'rx_bytes' => array()];
foreach (['date', 'tx_bytes', 'rx_bytes'] as $key) {
    for ($i = 0; $i < count($traffic_records); ++$i) {
        array_push($traffic_data[$key], $traffic_records[$i][$key]);
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Carrier9</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <script src="https://cdn.jsdelivr.net/npm/jquery@2.2.4/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0/dist/Chart.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" type="text/css" />
    </head>
    <body>
        <div class="container">
            <div class="row">
                <p calss="col-12">Welcome to Carrier9</p>
                <p id="internet-status-hint" class="col-6"></p>
                <p id="heartbeat-moment" class="col-6"></p>
                <script>
                    <?php 
                        $heartbeat_moment = $mysqli->query('SELECT `value` FROM `status` where `name`="heartbeat"')->fetch_row()[0];  // 形如“2019-03-27 13:17:00”。                    
                        $heartbeat_moment = strtotime($heartbeat_moment);  // 转换为自Unix纪元的秒数。
                        $internet_availble = time() - $heartbeat_moment <= 100;
                        print("let internetAvailable = ".($internet_availble ? 'true' : 'false').";\n");
                    ?>
                    <?php 
                        print("let heartbeatMoment = '".strftime('%Y-%m-%d %H:%M:%S UTC', $heartbeat_moment)."';\n");
                    ?>
                    document.getElementById('heartbeat-moment').innerHTML = `心跳时间：${heartbeatMoment}`;
                    document.getElementById('internet-status-hint').innerHTML = `互联网状态：${internetAvailable ? '可用' : '不可用'}`;
                </script>
            </div>
            <div class="row">
                <canvas id="traffic-chart" class="col-12"></canvas>
                <script>
                    let ctx = document.getElementById('traffic-chart').getContext('2d');
                    let data = {
                        labels: [ <?php 
                            print(implode(", ", array_map(function ($value) {
                                $time_string = strftime('%m-%d', strtotime($value));  // 将年月日格式的时间修改为月日。
                                return "'$time_string'";                              // 用单引号包围日期字符串。
                            }, $traffic_data['date']))); 
                        ?> ],
                        datasets: [{
                            label: '上行流量',
                            backgroundColor: '#fcd337',  // 柠檬黄
                            data: [ <?php
                                print(implode(', ', array_map(function ($value) {
                                    return round($value / pow(1024, 3), 2);  // 将bytes转换成GiB。
                                }, $traffic_data['rx_bytes'])));
                            ?> ]
                        }, {
                            label: '下行流量',
                            backgroundColor: '#5698c3',  // 晴蓝
                            data: [ <?php
                                print(implode(', ', array_map(function ($value) {
                                    return round($value / pow(1024, 3), 2);  // 将bytes转换成GiB。
                                }, $traffic_data['tx_bytes'])));
                            ?> ]
                        }],
                    };
                    let options = {
                        title: {
                            display: true,
                            text: '过去14天内的流量统计（GiB）'
                        },
                        scales: {
                            xAxes: [{
                                stacked: true,
                            }],
                            yAxes: [{
                                stacked: true,
                            }]
                        },
                        tooltips: {
                            mode: 'index'
                        }
                    };
                    let chart = new Chart(ctx, {
                        type: 'bar',
                        data: data,
                        options: options
                    });
                </script>
            </div>
        </div>
    </body>
</html>
