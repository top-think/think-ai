<?php

// 测试引导文件

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', '1');

// 加载 Composer autoloader
$autoloadFile = dirname(__DIR__) . '/vendor/autoload.php';
if (!file_exists($autoloadFile)) {
    die('请先运行 composer install' . PHP_EOL);
}

require $autoloadFile;

// 设置默认时区
date_default_timezone_set('Asia/Shanghai');

// 设置测试环境变量
putenv('THINK_AI_TEST_MODE=true');
putenv('THINK_AI_TOKEN=test-token');

// 确保 Mockery 正确关闭
if (class_exists('Mockery')) {
    Mockery::close();
}