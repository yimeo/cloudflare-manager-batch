<?php
/**
 * 应用配置文件
 */
return [
    'app_name' => 'CloudFlare 批量管理工具',
    'app_version' => '1.0.0',
    'debug' => false,
    
    // CloudFlare API 配置
    'cloudflare' => [
        'api_base' => 'https://api.cloudflare.com/client/v4',
        // 认证方式: token 或 key
        'auth_type' => 'token', // token | key
    ],
    
    // 会话配置
    'session' => [
        'lifetime' => 7200, // 2小时
    ],
    
    // 日志配置
    'log' => [
        'path' => STORAGE_PATH . '/logs',
        'level' => 'info',
    ],
    
    // 批量操作配置
    'batch' => [
        'max_concurrent' => 5,    // 最大并发请求数
        'delay_ms' => 200,        // 请求间隔(毫秒)
        'max_zones' => 500,       // 单次最大处理Zone数
    ],
];
