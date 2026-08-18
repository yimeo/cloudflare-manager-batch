<?php
/**
 * CloudFlare 批量管理工具
 * 入口文件
 */

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('STORAGE_PATH', ROOT_PATH . '/storage');

// 自动加载
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = APP_PATH . '/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// 加载配置
$config = require CONFIG_PATH . '/app.php';

// 确保 storage 目录存在
$logPath = STORAGE_PATH . '/logs';
if (!is_dir($logPath)) {
    @mkdir($logPath, 0755, true);
}

// 启动会话（使用系统默认 session 存储路径）
session_start();

// 路由处理
$router = new App\Services\Router();
$router->dispatch();
