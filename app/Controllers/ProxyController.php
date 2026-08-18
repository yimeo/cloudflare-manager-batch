<?php
namespace App\Controllers;

class ProxyController extends BaseController
{
    /**
     * 代理管理页面
     */
    public function index(): void
    {
        $this->render('proxy/index');
    }
    
    /**
     * 切换单个记录的代理状态
     */
    public function toggle(): void
    {
        $data = $this->getPostData();
        $zoneId = $data['zone_id'] ?? '';
        $recordId = $data['record_id'] ?? '';
        $proxied = (bool)($data['proxied'] ?? false);
        
        if (empty($zoneId) || empty($recordId)) {
            $this->json(['success' => false, 'message' => '参数不完整']);
            return;
        }
        
        // 先获取当前记录信息
        $record = $this->api->get("/zones/{$zoneId}/dns_records/{$recordId}");
        if (!($record['success'] ?? false)) {
            $this->json(['success' => false, 'message' => '获取记录失败']);
            return;
        }
        
        $recordData = $record['result'];
        $result = $this->api->patch("/zones/{$zoneId}/dns_records/{$recordId}", [
            'proxied' => $proxied,
        ]);
        
        $this->json($result);
    }
    
    /**
     * 批量开关代理
     */
    public function batch(): void
    {
        $data = $this->getPostData();
        $zoneIds = $this->getSelectedZones($data);
        $proxied = (bool)($data['proxied'] ?? false);
        $recordType = $data['record_type'] ?? ''; // 可选，筛选记录类型
        $recordName = $data['record_name'] ?? ''; // 可选，筛选记录名
        
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
            $params = ['per_page' => 100];
            if (!empty($recordType)) $params['type'] = $recordType;
            if (!empty($recordName)) $params['name'] = $recordName;
            
            // 获取 DNS 记录
            $records = $this->api->get("/zones/{$zoneId}/dns_records", $params);
            
            if (!($records['success'] ?? false)) {
                $results[] = ['zone_id' => $zoneName, 'success' => false, 'message' => '获取记录失败'];
                $failCount++;
                continue;
            }
            
            $zoneSuccess = 0;
            $zoneFail = 0;
            
            foreach ($records['result'] as $record) {
                if (!in_array($record['type'], ['A', 'AAAA', 'CNAME'])) continue;
                if ($record['proxied'] === $proxied) continue;
                
                $result = $this->api->patch("/zones/{$zoneId}/dns_records/{$record['id']}", [
                    'proxied' => $proxied,
                ]);
                
                if ($result['success'] ?? false) {
                    $successCount++;
                    $zoneSuccess++;
                } else {
                    $failCount++;
                    $zoneFail++;
                }
                usleep(200000);
            }
            
            $results[] = [
                'zone_id' => $zoneName,
                'success' => true,
                'message' => "成功 {$zoneSuccess} 条, 失败 {$zoneFail} 条",
            ];
        }
        
        $statusText = $proxied ? '开启' : '关闭';
        $this->json([
            'success' => true,
            'message' => "批量{$statusText}代理完成: 成功 {$successCount} 条, 失败 {$failCount} 条",
            'data' => $results,
            'summary' => ['success' => $successCount, 'fail' => $failCount],
        ]);
    }
}
