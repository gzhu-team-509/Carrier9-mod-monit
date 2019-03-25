<?php

require 'config.php';


// 验证Key
if (isset($_REQUEST['key'])) {
    if ($_REQUEST['key'] != $GLOBALS['KEY']) {
        print('Invalid Key.');
        return;
    }

    print('Key Accepted.');

    $mysqli = new mysqli($GLOBALS['DB_HOST'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASS'], $GLOBALS['DB_NAME']);

    if (isset($_REQUEST['rx']) && isset($_REQUEST['tx'])) {
        $rx = $_REQUEST['rx']; $tx = $_REQUEST['tx'];
        $sql = "INSERT INTO `traffic` (`date`, `tx_bytes`, `rx_bytes`) VALUES (CURRENT_DATE, $tx, $rx);";
        $mysqli->query($sql);
    }

    if (isset($_REQUEST['heartbeat'])) {
        $date = date('Y-m-d H:i:s');
        print($date);
        $sql = "REPLACE INTO `status` (`name`, `value`) VALUES ('heartbeat', '$date');";
        print($sql);
        $mysqli->query($sql);
    }

    return;
}

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Akane313.2</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    </head>
    <body>
        <p>Welcome to Akane313.2</p>
    </body>
</html>
