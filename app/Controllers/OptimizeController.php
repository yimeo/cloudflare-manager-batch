<?php
namespace App\Controllers;

class OptimizeController extends BaseController
{
    /**
     * 优化设置页面
     */
    public function index(): void
    {
        $this->render('optimize/index');
    }
    
    /**
     * 更新单个 Zone 的优化设置
     */
    public function update(): void
    {
        $data = $this->getPostData();
        $zoneId = $data['zone_id'] ?? '';
        
        if (empty($zoneId)) {
            $this->json(['success' => false, 'message' => '请指定域名']);
            return;
        }
        
        $results = $this->applyOptimizeSettings($zoneId, $data);
        $this->json(['success' => true, 'data' => $results]);
    }
    
    /**
     * 批量优化设置
     */
    public function batch(): void
    {
        $data = $this->getPostData();
        $zoneIds = $this->getSelectedZones($data);
        
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
            $zoneResults = $this->applyOptimizeSettings($zoneId, $data);
            $zoneSuccess = !in_array(false, $zoneResults, true);
            
            if ($zoneSuccess) $successCount++;
            else $failCount++;
            
            $results[] = [
                'zone_id' => $zoneName,
                'success' => $zoneSuccess,
                'details' => $zoneResults,
            ];
            usleep(200000);
        }
        
        $this->json([
            'success' => true,
            'message' => "完成: 成功 {$successCount} 个, 失败 {$failCount} 个",
            'data' => $results,
            'summary' => ['success' => $successCount, 'fail' => $failCount, 'total' => count($zoneIds)],
        ]);
    }
    
    /**
     * 应用优化设置到指定 Zone
     */
    private function applyOptimizeSettings(string $zoneId, array $data): array
    {
        $results = [];
        
        // 代码压缩 (HTML, CSS, JS)
        if (isset($data['minify'])) {
            $minify = $data['minify'];
            if (is_string($minify)) {
                $minify = json_decode($minify, true);
            }
            $result = $this->api->patch("/zones/{$zoneId}/settings/minify", [
                'value' => [
                    'html' => $minify['html'] ?? 'off',
                    'css' => $minify['css'] ?? 'off',
                    'js' => $minify['js'] ?? 'off',
                ],
            ]);
            $results['minify'] = $result['success'] ?? false;
            usleep(100000);
        }
        
        // Brotli 压缩
        if (isset($data['brotli'])) {
            $result = $this->api->patch("/zones/{$zoneId}/settings/brotli", [
                'value' => $data['brotli'],
            ]);
            $results['brotli'] = $result['success'] ?? false;
            usleep(100000);
        }
        
        // Early Hints
        if (isset($data['early_hints'])) {
            $result = $this->api->patch("/zones/{$zoneId}/settings/early_hints", [
                'value' => $data['early_hints'],
            ]);
            $results['early_hints'] = $result['success'] ?? false;
            usleep(100000);
        }
        
        // HTTP/2
        if (isset($data['http2'])) {
            $result = $this->api->patch("/zones/{$zoneId}/settings/http2", [
                'value' => $data['http2'],
            ]);
            $results['http2'] = $result['success'] ?? false;
            usleep(100000);
        }
        
        // HTTP/3
        if (isset($data['http3'])) {
            $result = $this->api->patch("/zones/{$zoneId}/settings/http3", [
                'value' => $data['http3'],
            ]);
            $results['http3'] = $result['success'] ?? false;
            usleep(100000);
        }
        
        // 0-RTT
        if (isset($data['0rtt'])) {
            $result = $this->api->patch("/zones/{$zoneId}/settings/0rtt", [
                'value' => $data['0rtt'],
            ]);
            $results['0rtt'] = $result['success'] ?? false;
            usleep(100000);
        }
        
        // IPv6
        if (isset($data['ipv6'])) {
            $result = $this->api->patch("/zones/{$zoneId}/settings/ipv6", [
                'value' => $data['ipv6'],
            ]);
            $results['ipv6'] = $result['success'] ?? false;
            usleep(100000);
        }
        
        // WebSockets
        if (isset($data['websockets'])) {
            $result = $this->api->patch("/zones/{$zoneId}/settings/websockets", [
                'value' => $data['websockets'],
            ]);
            $results['websockets'] = $result['success'] ?? false;
            usleep(100000);
        }
        
        // 图像优化 (Polish)
        if (isset($data['polish'])) {
            $result = $this->api->patch("/zones/{$zoneId}/settings/polish", [
                'value' => $data['polish'],
            ]);
            $results['polish'] = $result['success'] ?? false;
            usleep(100000);
        }
        
        // Prefetch Preload
        if (isset($data['prefetch_preload'])) {
            $result = $this->api->patch("/zones/{$zoneId}/settings/prefetch_preload", [
                'value' => $data['prefetch_preload'],
            ]);
            $results['prefetch_preload'] = $result['success'] ?? false;
            usleep(100000);
        }
        
        return $results;
    }
}
