<?php

require 'config.php';


$mysqli = new mysqli($GLOBALS['DB_HOST'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASS'], $GLOBALS['DB_NAME']);


// 验证Key
if (isset($_REQUEST['key'])) {
    if ($_REQUEST['key'] != $GLOBALS['KEY']) {
        print('Invalid Key.');
        return;
    }

    print('Key Accepted.');

    if (isset($_REQUEST['rx']) && isset($_REQUEST['tx'])) {
        $rx = $_REQUEST['rx']; $tx = $_REQUEST['tx'];
        $sql = "INSERT INTO `traffic` (`date`, `tx_bytes`, `rx_bytes`) VALUES (CURRENT_DATE, $tx, $rx);";
        $mysqli->query($sql);
    }

    if (isset($_REQUEST['heartbeat'])) {
        $date = date('Y-m-d H:i:s');
        $sql = "REPLACE INTO `status` (`name`, `value`) VALUES ('heartbeat', '$date');";
        $mysqli->query($sql);
    }

    return;
}

// 查询数据
$traffic_records = $mysqli->query("SELECT * from `traffic` ORDER BY `date` LIMIT 14")->fetch_all(MYSQLI_ASSOC);
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
        <title>Akane313.2</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0/dist/Chart.bundle.min.js"></script>
    </head>
    <body>
        <p>Welcome to Akane313.2</p>
        <canvas id="traffic-chart"></canvas>
        <script>
            let ctx = document.getElementById('traffic-chart').getContext('2d');
            let data = {
                labels: [ <?php 
                    print(implode(", ", array_map(function ($value) {
                        $time_string = strftime('%m-%d', strtotime($value));  // 将年月日格式的时间修改为月日
                        return "'$time_string'";                              // 用单引号包围日期字符串
                    }, $traffic_data['date']))); 
                ?> ],
                datasets: [{
                    label: '上行流量',
                    backgroundColor: '#fcd337',  // 柠檬黄
                    data: [ <?php
                        print(implode(', ', array_map(function ($value) {
                            return $value / pow(1024, 3);  // 将bytes转换成GiB
                        }, $traffic_data['rx_bytes'])));
                    ?> ]
                }, {
                    label: '下行流量',
                    backgroundColor: '#5698c3',  // 晴蓝
                    data: [ <?php
                        print(implode(', ', array_map(function ($value) {
                            return $value / pow(1024, 3);  // 将bytes转换成GiB
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
    </body>
</html>
