<?php
namespace App\Services;

class Router
{
    private array $routes = [
        // 首页/认证
        'GET /' => ['HomeController', 'index'],
        'POST /auth/save' => ['AuthController', 'save'],
        'GET /auth/logout' => ['AuthController', 'logout'],
        'POST /auth/verify' => ['AuthController', 'verify'],
        
        // Zone 管理
        'GET /zones' => ['ZoneController', 'index'],
        'POST /zones/add' => ['ZoneController', 'add'],
        'POST /zones/delete' => ['ZoneController', 'delete'],
        'POST /zones/list' => ['ZoneController', 'list'],
        'POST /zones/export' => ['ZoneController', 'export'],
        
        // DNS 解析
        'GET /dns' => ['DnsController', 'index'],
        'POST /dns/add' => ['DnsController', 'add'],
        'POST /dns/update' => ['DnsController', 'update'],
        'POST /dns/delete' => ['DnsController', 'delete'],
        'POST /dns/list' => ['DnsController', 'list'],
        'POST /dns/batch' => ['DnsController', 'batch'],
        
        // 代理开关
        'GET /proxy' => ['ProxyController', 'index'],
        'POST /proxy/toggle' => ['ProxyController', 'toggle'],
        'POST /proxy/batch' => ['ProxyController', 'batch'],
        
        // SSL/HTTPS 设置
        'GET /ssl' => ['SslController', 'index'],
        'POST /ssl/update' => ['SslController', 'update'],
        'POST /ssl/batch' => ['SslController', 'batch'],
        
        // 缓存管理
        'GET /cache' => ['CacheController', 'index'],
        'POST /cache/purge' => ['CacheController', 'purge'],
        'POST /cache/settings' => ['CacheController', 'settings'],
        'POST /cache/batch' => ['CacheController', 'batch'],
        
        // 代码压缩/网络优化
        'GET /optimize' => ['OptimizeController', 'index'],
        'POST /optimize/update' => ['OptimizeController', 'update'],
        'POST /optimize/batch' => ['OptimizeController', 'batch'],
        
        // 规则管理
        'GET /rules' => ['RulesController', 'index'],
        'POST /rules/copy' => ['RulesController', 'copy'],
        'POST /rules/delete' => ['RulesController', 'delete'],
        'POST /rules/list' => ['RulesController', 'list'],
        'POST /rules/count' => ['RulesController', 'count'],
        'POST /rules/has-rules' => ['RulesController', 'hasRules'],
        'POST /rules/get-all' => ['RulesController', 'getAllRules'],
        'POST /rules/batch-delete' => ['RulesController', 'batchDelete'],
        
        // AI 爬虫管理
        'GET /ai-crawlers' => ['AiCrawlerController', 'index'],
        'POST /ai-crawlers/list' => ['AiCrawlerController', 'list'],
        'POST /ai-crawlers/update' => ['AiCrawlerController', 'update'],
        'POST /ai-crawlers/batch' => ['AiCrawlerController', 'batch'],
        'POST /ai-crawlers/block-all' => ['AiCrawlerController', 'blockAll'],
        'POST /ai-crawlers/allow-all' => ['AiCrawlerController', 'allowAll'],
        'POST /ai-crawlers/settings' => ['AiCrawlerController', 'settings'],
        
        // 批量设置
        'GET /settings' => ['SettingsController', 'index'],
        'POST /settings/update' => ['SettingsController', 'update'],
        'POST /settings/batch' => ['SettingsController', 'batch'],
        
        // 导出
        'GET /export' => ['ExportController', 'index'],
        'POST /export/zones' => ['ExportController', 'zones'],
    ];
    
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';
        
        $routeKey = "$method $uri";
        
        if (isset($this->routes[$routeKey])) {
            [$controllerName, $action] = $this->routes[$routeKey];
            $controllerClass = "App\\Controllers\\$controllerName";
            
            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                
                // 检查认证（排除首页和认证相关路由）
                if (!in_array($uri, ['/', '/auth/save', '/auth/verify', '/auth/logout'])) {
                    if (!$this->checkAuth()) {
                        $this->jsonResponse(['success' => false, 'message' => '请先配置 API 认证信息'], 401);
                        return;
                    }
                }
                
                $controller->$action();
                return;
            }
        }
        
        // 404
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => '路由不存在']);
    }
    
    private function checkAuth(): bool
    {
        return !empty($_SESSION['cf_auth']);
    }
    
    public static function jsonResponse(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
