<?php

// 诊断测试环境问题

echo "PHP 版本: " . PHP_VERSION . "\n";

// 检查 autoloader
$autoloadFile = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoloadFile)) {
    die("错误: vendor/autoload.php 不存在，请运行 composer install\n");
}

require $autoloadFile;

// 检查类是否可以加载
$classes = [
    'think\ai\Client',
    'think\ai\Exception',
    'think\ai\Response\ChatResponse',
    'think\ai\Builder\ChatBuilder',
    'think\ai\tests\TestCase',
];

echo "\n检查类加载:\n";
foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "✓ {$class}\n";
    } else {
        echo "✗ {$class} - 无法加载\n";
    }
}

// 检查 PHPUnit
echo "\n检查 PHPUnit:\n";
if (class_exists('PHPUnit\Framework\TestCase')) {
    echo "✓ PHPUnit 已安装\n";
} else {
    echo "✗ PHPUnit 未找到\n";
}

// 尝试实例化一个简单的对象
echo "\n测试实例化:\n";
try {
    $client = new think\ai\Client('test-token');
    echo "✓ Client 实例化成功\n";
} catch (Exception $e) {
    echo "✗ Client 实例化失败: " . $e->getMessage() . "\n";
}

echo "\n如果看到此消息，基本环境是正常的。\n";