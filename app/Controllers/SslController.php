<?php
namespace App\Controllers;

class SslController extends BaseController
{
    /**
     * SSL 设置页面
     */
    public function index(): void
    {
        $this->render('ssl/index');
    }
    
    /**
     * 更新单个 Zone 的 SSL 设置
     */
    public function update(): void
    {
        $data = $this->getPostData();
        $zoneId = $data['zone_id'] ?? '';
        
        if (empty($zoneId)) {
            $this->json(['success' => false, 'message' => '请指定域名']);
            return;
        }
        
        $results = [];
        
        // SSL 模式: off, flexible, full, strict
        if (isset($data['ssl_mode'])) {
            $result = $this->api->patch("/zones/{$zoneId}/settings/ssl", [
                'value' => $data['ssl_mode'],
            ]);
            $results['ssl_mode'] = $result['success'] ?? false;
        }
        
        // 最低 TLS 版本
        if (isset($data['min_tls_version'])) {
            $result = $this->api->patch("/zones/{$zoneId}/settings/min_tls_version", [
                'value' => $data['min_tls_version'],
            ]);
            $results['min_tls_version'] = $result['success'] ?? false;
        }
        
        // 始终使用 HTTPS
        if (isset($data['always_use_https'])) {
            $result = $this->api->patch("/zones/{$zoneId}/settings/always_use_https", [
                'value' => $data['always_use_https'],
            ]);
            $results['always_use_https'] = $result['success'] ?? false;
        }
        
        // 自动 HTTPS 重写
        if (isset($data['automatic_https_rewrites'])) {
            $result = $this->api->patch("/zones/{$zoneId}/settings/automatic_https_rewrites", [
                'value' => $data['automatic_https_rewrites'],
            ]);
            $results['automatic_https_rewrites'] = $result['success'] ?? false;
        }
        
        // TLS 1.3
        if (isset($data['tls_1_3'])) {
            $result = $this->api->patch("/zones/{$zoneId}/settings/tls_1_3", [
                'value' => $data['tls_1_3'],
            ]);
            $results['tls_1_3'] = $result['success'] ?? false;
        }
        
        $this->json(['success' => true, 'data' => $results]);
    }
    
    /**
     * 批量设置 SSL
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
        
        $settings = [];
        if (isset($data['ssl_mode'])) $settings['ssl'] = $data['ssl_mode'];
        if (isset($data['min_tls_version'])) $settings['min_tls_version'] = $data['min_tls_version'];
        if (isset($data['always_use_https'])) $settings['always_use_https'] = $data['always_use_https'];
        if (isset($data['automatic_https_rewrites'])) $settings['automatic_https_rewrites'] = $data['automatic_https_rewrites'];
        if (isset($data['tls_1_3'])) $settings['tls_1_3'] = $data['tls_1_3'];
        
        if (empty($settings)) {
            $this->json(['success' => false, 'message' => '请至少选择一项设置']);
            return;
        }
        
        $results = [];
        $successCount = 0;
        $failCount = 0;
        
        foreach ($zoneIds as $zoneId) {
            $zoneName = $zoneMap[$zoneId] ?? $zoneId;
            $zoneResults = [];
            $zoneSuccess = true;
            
            foreach ($settings as $settingId => $value) {
                $result = $this->api->patch("/zones/{$zoneId}/settings/{$settingId}", ['value' => $value]);
                $zoneResults[$settingId] = $result['success'] ?? false;
                if (!($result['success'] ?? false)) $zoneSuccess = false;
                usleep(100000);
            }
            
            if ($zoneSuccess) $successCount++;
            else $failCount++;
            
            $results[] = [
                'zone_id' => $zoneName,
                'success' => $zoneSuccess,
                'details' => $zoneResults,
            ];
        }
        
        $this->json([
            'success' => true,
            'message' => "完成: 成功 {$successCount} 个, 失败 {$failCount} 个",
            'data' => $results,
            'summary' => ['success' => $successCount, 'fail' => $failCount, 'total' => count($zoneIds)],
        ]);
    }
}
