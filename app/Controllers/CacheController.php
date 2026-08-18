<?php
namespace App\Controllers;

class CacheController extends BaseController
{
    /**
     * 缓存管理页面
     */
    public function index(): void
    {
        $this->render('cache/index');
    }
    
    /**
     * 清除缓存
     */
    public function purge(): void
    {
        $data = $this->getPostData();
        $zoneId = $data['zone_id'] ?? '';
        $purgeType = $data['purge_type'] ?? 'all'; // all, files, tags, hosts, prefixes
        
        if (empty($zoneId)) {
            $this->json(['success' => false, 'message' => '请指定域名']);
            return;
        }
        
        $purgeData = [];
        
        switch ($purgeType) {
            case 'all':
                $purgeData = ['purge_everything' => true];
                break;
            case 'files':
                $files = array_filter(array_map('trim', explode("\n", $data['files'] ?? '')));
                $purgeData = ['files' => $files];
                break;
            case 'tags':
                $tags = array_filter(array_map('trim', explode("\n", $data['tags'] ?? '')));
                $purgeData = ['tags' => $tags];
                break;
            case 'hosts':
                $hosts = array_filter(array_map('trim', explode("\n", $data['hosts'] ?? '')));
                $purgeData = ['hosts' => $hosts];
                break;
            case 'prefixes':
                $prefixes = array_filter(array_map('trim', explode("\n", $data['prefixes'] ?? '')));
                $purgeData = ['prefixes' => $prefixes];
                break;
        }
        
        $result = $this->api->post("/zones/{$zoneId}/purge_cache", $purgeData);
        $this->json($result);
    }
    
    /**
     * 缓存设置
     */
    public function settings(): void
    {
        $data = $this->getPostData();
        $zoneId = $data['zone_id'] ?? '';
        
        if (empty($zoneId)) {
            $this->json(['success' => false, 'message' => '请指定域名']);
            return;
        }
        
        $results = [];
        
        // 缓存级别: aggressive, basic, simplified
        if (isset($data['cache_level'])) {
            $result = $this->api->patch("/zones/{$zoneId}/settings/cache_level", [
                'value' => $data['cache_level'],
            ]);
            $results['cache_level'] = $result['success'] ?? false;
        }
        
        // 浏览器缓存 TTL
        if (isset($data['browser_cache_ttl'])) {
            $result = $this->api->patch("/zones/{$zoneId}/settings/browser_cache_ttl", [
                'value' => intval($data['browser_cache_ttl']),
            ]);
            $results['browser_cache_ttl'] = $result['success'] ?? false;
        }
        
        // Always Online
        if (isset($data['always_online'])) {
            $result = $this->api->patch("/zones/{$zoneId}/settings/always_online", [
                'value' => $data['always_online'],
            ]);
            $results['always_online'] = $result['success'] ?? false;
        }
        
        // 开发模式
        if (isset($data['development_mode'])) {
            $result = $this->api->patch("/zones/{$zoneId}/settings/development_mode", [
                'value' => $data['development_mode'],
            ]);
            $results['development_mode'] = $result['success'] ?? false;
        }
        
        $this->json(['success' => true, 'data' => $results]);
    }
    
    /**
     * 批量缓存操作
     */
    public function batch(): void
    {
        $data = $this->getPostData();
        $zoneIds = $this->getSelectedZones($data);
        $action = $data['action'] ?? 'purge';
        
        if (empty($zoneIds)) {
            $this->json(['success' => false, 'message' => '请选择域名']);
            return;
        }

        $zoneMap = $this->getZoneMap();
        
        $results = [];
        $successCount = 0;
        $failCount = 0;
        
        foreach ($zoneIds as $zoneId) {
            $zoneName = $zoneMap[$zoneId] ?? $zoneId;
            if ($action === 'purge') {
                $result = $this->api->post("/zones/{$zoneId}/purge_cache", ['purge_everything' => true]);
            } else {
                $settingResults = [];
                $zoneSuccess = true;
                if (isset($data['cache_level'])) {
                    $r = $this->api->patch("/zones/{$zoneId}/settings/cache_level", ['value' => $data['cache_level']]);
                    if (!($r['success'] ?? false)) $zoneSuccess = false;
                }
                if (isset($data['browser_cache_ttl'])) {
                    $r = $this->api->patch("/zones/{$zoneId}/settings/browser_cache_ttl", ['value' => intval($data['browser_cache_ttl'])]);
                    if (!($r['success'] ?? false)) $zoneSuccess = false;
                }
                if (isset($data['always_online'])) {
                    $r = $this->api->patch("/zones/{$zoneId}/settings/always_online", ['value' => $data['always_online']]);
                    if (!($r['success'] ?? false)) $zoneSuccess = false;
                }
                $result = ['success' => $zoneSuccess];
                usleep(100000);
            }
            
            if ($result['success'] ?? false) {
                $successCount++;
                $results[] = ['zone_id' => $zoneName, 'success' => true, 'message' => '操作成功'];
            } else {
                $failCount++;
                $errorMsg = $result['errors'][0]['message'] ?? '操作失败';
                $results[] = ['zone_id' => $zoneName, 'success' => false, 'message' => $errorMsg];
            }
            usleep(200000);
        }
        
        $this->json([
            'success' => true,
            'message' => "完成: 成功 {$successCount} 个, 失败 {$failCount} 个",
            'data' => $results,
            'summary' => ['success' => $successCount, 'fail' => $failCount, 'total' => count($zoneIds)],
        ]);
    }
}
