<?php
namespace App\Controllers;

class ZoneController extends BaseController
{
    /**
     * Zone 管理页面
     */
    public function index(): void
    {
        $this->render('zones/index');
    }
    
    /**
     * 获取 Zone 列表
     */
    public function list(): void
    {
        $data = $this->getPostData();
        $params = [];
        
        if (!empty($data['name'])) {
            $params['name'] = $data['name'];
        }
        if (!empty($data['status'])) {
            $params['status'] = $data['status'];
        }
        
        $result = $this->api->getAllZones($params);
        $this->json($result);
    }
    
    /**
     * 批量添加 Zone
     */
    public function add(): void
    {
        $data = $this->getPostData();
        $domains = array_filter(array_map('trim', explode("\n", $data['domains'] ?? '')));
        
        if (empty($domains)) {
            $this->json(['success' => false, 'message' => '请输入要添加的域名']);
            return;
        }
        
        // 获取账户信息
        $accountId = $data['account_id'] ?? '';
        if (empty($accountId)) {
            // 自动获取账户 ID
            $userInfo = $this->api->get('/accounts', ['page' => 1, 'per_page' => 1]);
            if (($userInfo['success'] ?? false) && !empty($userInfo['result'])) {
                $accountId = $userInfo['result'][0]['id'];
            } else {
                $this->json(['success' => false, 'message' => '无法获取账户信息']);
                return;
            }
        }
        
        $results = [];
        $successCount = 0;
        $failCount = 0;
        
        foreach ($domains as $domain) {
            $postData = [
                'name' => $domain,
                'account' => ['id' => $accountId],
                'type' => $data['zone_type'] ?? 'full',
            ];
            
            if (!empty($data['jump_start'])) {
                $postData['jump_start'] = true;
            }
            
            $result = $this->api->post('/zones', $postData);
            
            if ($result['success'] ?? false) {
                $successCount++;
                $ns = $result['result']['name_servers'] ?? [];
                $results[] = [
                    'domain' => $domain,
                    'success' => true,
                    'zone_id' => $result['result']['id'],
                    'name_servers' => $ns,
                    'message' => '添加成功，NS: ' . implode(', ', $ns),
                ];
            } else {
                $failCount++;
                $errorMsg = $result['errors'][0]['message'] ?? '未知错误';
                $results[] = [
                    'domain' => $domain,
                    'success' => false,
                    'message' => $errorMsg,
                ];
            }
            
            usleep(200000); // 200ms 间隔
        }
        
        $this->json([
            'success' => true,
            'message' => "完成: 成功 {$successCount} 个, 失败 {$failCount} 个",
            'data' => $results,
            'summary' => ['success' => $successCount, 'fail' => $failCount, 'total' => count($domains)],
        ]);
    }
    
    /**
     * 批量删除 Zone
     */
    public function delete(): void
    {
        $data = $this->getPostData();
        $zoneIds = $this->getSelectedZones($data);
        
        if (empty($zoneIds)) {
            $this->json(['success' => false, 'message' => '请选择要删除的域名']);
            return;
        }

        $zoneMap = $this->getZoneMap();
        
        $results = [];
        $successCount = 0;
        $failCount = 0;
        
        foreach ($zoneIds as $zoneId) {
            $zoneName = $zoneMap[$zoneId] ?? $zoneId;
            $result = $this->api->delete("/zones/{$zoneId}");
            
            if ($result['success'] ?? false) {
                $successCount++;
                $results[] = ['zone_id' => $zoneName, 'success' => true, 'message' => '删除成功'];
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
     * 导出域名列表
     */
    public function export(): void
    {
        $result = $this->api->getAllZones();
        
        if (!($result['success'] ?? false)) {
            $this->json($result);
            return;
        }
        
        $zones = $result['result'];
        $exportData = [];
        
        foreach ($zones as $zone) {
            $exportData[] = [
                'id' => $zone['id'],
                'name' => $zone['name'],
                'status' => $zone['status'],
                'name_servers' => implode(', ', $zone['name_servers'] ?? []),
                'plan' => $zone['plan']['name'] ?? '',
                'created_on' => $zone['created_on'] ?? '',
            ];
        }
        
        $this->json([
            'success' => true,
            'data' => $exportData,
            'total' => count($exportData),
        ]);
    }
}
