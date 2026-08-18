<?php
namespace App\Controllers;

class SettingsController extends BaseController
{
    /**
     * 设置管理页面
     */
    public function index(): void
    {
        $this->render('settings/index');
    }
    
    /**
     * 更新单个 Zone 设置
     */
    public function update(): void
    {
        $data = $this->getPostData();
        $zoneId = $data['zone_id'] ?? '';
        $settings = $data['settings'] ?? [];
        
        if (empty($zoneId)) {
            $this->json(['success' => false, 'message' => '请指定域名']);
            return;
        }
        
        if (empty($settings)) {
            $this->json(['success' => false, 'message' => '请指定设置项']);
            return;
        }
        
        $results = [];
        foreach ($settings as $key => $value) {
            $result = $this->api->patch("/zones/{$zoneId}/settings/{$key}", [
                'value' => $value,
            ]);
            $results[$key] = $result['success'] ?? false;
            usleep(100000);
        }
        
        $this->json(['success' => true, 'data' => $results]);
    }
    
    /**
     * 批量修改设置
     */
    public function batch(): void
    {
        $data = $this->getPostData();
        $zoneIds = $this->getSelectedZones($data);
        $settings = $data['settings'] ?? [];
        
        if (empty($zoneIds)) {
            $this->json(['success' => false, 'message' => '请选择域名']);
            return;
        }

        $zoneMap = $this->getZoneMap();
        
        if (empty($settings)) {
            $this->json(['success' => false, 'message' => '请指定设置项']);
            return;
        }
        
        $results = [];
        $successCount = 0;
        $failCount = 0;
        
        foreach ($zoneIds as $zoneId) {
            $zoneName = $zoneMap[$zoneId] ?? $zoneId;
            $zoneResults = [];
            $zoneSuccess = true;
            
            foreach ($settings as $key => $value) {
                $result = $this->api->patch("/zones/{$zoneId}/settings/{$key}", ['value' => $value]);
                $zoneResults[$key] = $result['success'] ?? false;
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
