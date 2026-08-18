<?php
namespace App\Controllers;

use App\Services\CloudFlareApi;
use App\Services\Router;

class BaseController
{
    protected CloudFlareApi $api;
    
    public function __construct()
    {
        if (!empty($_SESSION['cf_auth'])) {
            $this->api = new CloudFlareApi();
        }
    }
    
    /**
     * 渲染视图
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';
        
        if (file_exists($viewFile)) {
            ob_start();
            include $viewFile;
            $content = ob_get_clean();
            
            // 使用布局
            include APP_PATH . '/Views/layouts/main.php';
        } else {
            echo "视图文件不存在: $viewFile";
        }
    }
    
    /**
     * JSON 响应
     */
    protected function json(array $data, int $code = 200): void
    {
        Router::jsonResponse($data, $code);
    }
    
    /**
     * 获取 POST 数据
     */
    protected function getPostData(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            return json_decode($raw, true) ?? [];
        }
        
        return $_POST;
    }
    
    /**
     * 获取选中的 Zone 列表
     */
    protected function getSelectedZones(array $data): array
    {
        $zones = [];
        
        // 从选择列表获取
        if (!empty($data['zone_ids'])) {
            if (is_string($data['zone_ids'])) {
                $zones = array_filter(array_map('trim', explode("\n", $data['zone_ids'])));
            } else {
                $zones = $data['zone_ids'];
            }
        }
        
        // 从域名列表获取对应的 zone_id
        if (!empty($data['domains'])) {
            $domains = is_string($data['domains']) 
                ? array_filter(array_map('trim', explode("\n", $data['domains'])))
                : $data['domains'];
            
            if (!empty($domains)) {
                $allZones = $this->api->getAllZones();
                if ($allZones['success'] ?? false) {
                    $zoneMap = [];
                    foreach ($allZones['result'] as $zone) {
                        $zoneMap[$zone['name']] = $zone['id'];
                    }
                    foreach ($domains as $domain) {
                        if (isset($zoneMap[$domain])) {
                            $zones[] = $zoneMap[$domain];
                        }
                    }
                }
            }
        }
        
        return $zones;
    }
    
    /**
     * 获取 Zone ID 到域名名称的映射
     */
    protected function getZoneMap(): array
    {
        $map = [];
        $allZones = $this->api->getAllZones();
        if ($allZones['success'] ?? false) {
            foreach ($allZones['result'] as $zone) {
                $map[$zone['id']] = $zone['name'];
            }
        }
        return $map;
    }

    /**
     * 记录日志
     */
    protected function log(string $message, string $level = 'info'): void
    {
        $logFile = STORAGE_PATH . '/logs/' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $line = "[$timestamp] [$level] $message" . PHP_EOL;
        file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
