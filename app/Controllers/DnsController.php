<?php
namespace App\Controllers;

class DnsController extends BaseController
{
    /**
     * DNS 管理页面
     */
    public function index(): void
    {
        $this->render('dns/index');
    }
    
    /**
     * 获取指定 Zone 的 DNS 记录
     */
    public function list(): void
    {
        $data = $this->getPostData();
        $zoneId = $data['zone_id'] ?? '';
        
        if (empty($zoneId)) {
            $this->json(['success' => false, 'message' => '请指定域名']);
            return;
        }
        
        $params = [];
        if (!empty($data['type'])) $params['type'] = $data['type'];
        if (!empty($data['name'])) $params['name'] = $data['name'];
        
        $result = $this->api->get("/zones/{$zoneId}/dns_records", $params);
        $this->json($result);
    }
    
    /**
     * 批量添加 DNS 记录
     */
    public function add(): void
    {
        $data = $this->getPostData();
        $zoneIds = $this->getSelectedZones($data);
        
        if (empty($zoneIds)) {
            $this->json(['success' => false, 'message' => '请选择域名']);
            return;
        }
        
        $recordType = $data['record_type'] ?? 'A';
        $recordName = $data['record_name'] ?? '@';
        $ttl = intval($data['ttl'] ?? 1);
        $proxied = !empty($data['proxied']);
        
        $contents = [];
        if (!empty($data['contents'])) {
            $lines = array_filter(array_map('trim', explode("\n", $data['contents'])));
            foreach ($lines as $line) {
                if (strpos($line, ':') !== false) {
                    [$domain, $ip] = explode(':', $line, 2);
                    $contents[trim($domain)] = trim($ip);
                } else {
                    $contents['_default'] = trim($line);
                }
            }
        }
        
        $defaultContent = $data['content'] ?? ($contents['_default'] ?? '');
        $zoneMap = $this->getZoneMap();
        
        $results = [];
        $successCount = 0;
        $failCount = 0;
        
        foreach ($zoneIds as $zoneId) {
            $zoneName = $zoneMap[$zoneId] ?? $zoneId;
            $content = $contents[$zoneName] ?? $defaultContent;
            
            if (empty($content)) {
                $results[] = ['zone_id' => $zoneName, 'success' => false, 'message' => '未指定解析值'];
                $failCount++;
                continue;
            }
            
            $recordData = [
                'type' => $recordType,
                'name' => $recordName,
                'content' => $content,
                'ttl' => $ttl,
                'proxied' => $proxied,
            ];
            
            if (in_array($recordType, ['MX', 'SRV']) && isset($data['priority'])) {
                $recordData['priority'] = intval($data['priority']);
            }
            
            $result = $this->api->post("/zones/{$zoneId}/dns_records", $recordData);
            
            if ($result['success'] ?? false) {
                $successCount++;
                $results[] = [
                    'zone_id' => $zoneName,
                    'success' => true,
                    'message' => "添加成功: {$recordName} -> {$content}",
                ];
            } else {
                $failCount++;
                $errorMsg = $result['errors'][0]['message'] ?? '未知错误';
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
    
    /**
     * 批量更新 DNS 记录
     */
    public function update(): void
    {
        $data = $this->getPostData();
        $zoneIds = $this->getSelectedZones($data);
        
        if (empty($zoneIds)) {
            $this->json(['success' => false, 'message' => '请选择域名']);
            return;
        }
        
        $recordType = $data['record_type'] ?? 'A';
        $recordName = $data['record_name'] ?? '@';
        $newContent = $data['content'] ?? '';
        $ttl = intval($data['ttl'] ?? 1);
        $proxied = isset($data['proxied']) ? (bool)$data['proxied'] : null;
        
        // 获取域名映射
        $zoneMap = $this->getZoneMap();
        
        $results = [];
        $successCount = 0;
        $failCount = 0;
        
        foreach ($zoneIds as $zoneId) {
            $zoneName = $zoneMap[$zoneId] ?? $zoneId;
            
            // 查找匹配的记录
            $records = $this->api->get("/zones/{$zoneId}/dns_records", [
                'type' => $recordType,
                'name' => $recordName,
            ]);
            
            if (!($records['success'] ?? false) || empty($records['result'])) {
                $results[] = ['zone_id' => $zoneName, 'success' => false, 'message' => '未找到匹配的记录'];
                $failCount++;
                continue;
            }
            
            foreach ($records['result'] as $record) {
                $updateData = [
                    'type' => $recordType,
                    'name' => $record['name'],
                    'content' => $newContent ?: $record['content'],
                    'ttl' => $ttl,
                ];
                
                if ($proxied !== null) {
                    $updateData['proxied'] = $proxied;
                }
                
                $result = $this->api->put("/zones/{$zoneId}/dns_records/{$record['id']}", $updateData);
                
                if ($result['success'] ?? false) {
                    $successCount++;
                    $results[] = ['zone_id' => $zoneName, 'record_id' => $record['id'], 'success' => true, 'message' => '更新成功'];
                } else {
                    $failCount++;
                    $errorMsg = $result['errors'][0]['message'] ?? '未知错误';
                    $results[] = ['zone_id' => $zoneName, 'record_id' => $record['id'], 'success' => false, 'message' => $errorMsg];
                }
                
                usleep(200000);
            }
        }
        
        $this->json([
            'success' => true,
            'message' => "完成: 成功 {$successCount} 个, 失败 {$failCount} 个",
            'data' => $results,
            'summary' => ['success' => $successCount, 'fail' => $failCount],
        ]);
    }
    
    /**
     * 批量删除 DNS 记录
     */
    public function delete(): void
    {
        $data = $this->getPostData();
        $zoneIds = $this->getSelectedZones($data);
        
        if (empty($zoneIds)) {
            $this->json(['success' => false, 'message' => '请选择域名']);
            return;
        }

        $zoneMap = $this->getZoneMap();
        $recordType = $data['record_type'] ?? '';
        $recordName = $data['record_name'] ?? '';
        $clearAll = !empty($data['clear_all']);
        
        $results = [];
        $successCount = 0;
        $failCount = 0;
        
        foreach ($zoneIds as $zoneId) {
            $zoneName = $zoneMap[$zoneId] ?? $zoneId;
            $params = [];
            if (!$clearAll) {
                if (!empty($recordType)) $params['type'] = $recordType;
                if (!empty($recordName)) $params['name'] = $recordName;
            }
            
            $records = $this->api->get("/zones/{$zoneId}/dns_records", $params);
            
            if (!($records['success'] ?? false)) {
                $results[] = ['zone_id' => $zoneName, 'success' => false, 'message' => '获取记录失败'];
                $failCount++;
                continue;
            }
            
            if (empty($records['result'])) {
                $results[] = ['zone_id' => $zoneName, 'success' => true, 'message' => '无匹配记录'];
                continue;
            }
            
            foreach ($records['result'] as $record) {
                $result = $this->api->delete("/zones/{$zoneId}/dns_records/{$record['id']}");
                if ($result['success'] ?? false) $successCount++;
                else $failCount++;
                usleep(100000);
            }
            
            $results[] = [
                'zone_id' => $zoneName,
                'success' => true,
                'message' => '已删除 ' . count($records['result']) . ' 条记录',
            ];
        }
        
        $this->json([
            'success' => true,
            'message' => "完成: 成功删除 {$successCount} 条记录, 失败 {$failCount} 条",
            'data' => $results,
            'summary' => ['success' => $successCount, 'fail' => $failCount],
        ]);
    }
    
    /**
     * 批量操作（通用）
     */
    public function batch(): void
    {
        $data = $this->getPostData();
        $action = $data['action'] ?? '';
        
        switch ($action) {
            case 'add':
                $this->add();
                break;
            case 'update':
                $this->update();
                break;
            case 'delete':
                $this->delete();
                break;
            default:
                $this->json(['success' => false, 'message' => '未知操作']);
        }
    }
}
