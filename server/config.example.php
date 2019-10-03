<?php

// 调试选项
error_reporting(E_ALL);
ini_set('display_errors', 'On');

// 时区设置
date_default_timezone_set('Asia/Shanghai');

// 验证密钥设置
$GLOBALS['KEY'] = '32ff2927d2a1563cc3d57df0f227b6ba7d3c19d7';

// 数据库设置
$GLOBALS['DB_HOST'] = '127.0.0.1';
$GLOBALS['DB_USER'] = 'user';
$GLOBALS['DB_PASS'] = 'pass';
$GLOBALS['DB_NAME'] = 'dbname';
