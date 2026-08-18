<?php
namespace App\Controllers;

class RulesController extends BaseController
{
    // 规则集阶段映射
    private array $rulesetPhases = [
        'page_rules' => 'page_rules',
        'waf_custom' => 'http_request_firewall_custom',
        'transform_request_header' => 'http_request_late_transform',
        'transform_response_header' => 'http_response_headers_transform',
        'redirect' => 'http_request_dynamic_redirect',
        'origin' => 'http_request_origin',
        'cache' => 'http_request_cache_settings',
        'config' => 'http_config_settings',
        'rewrite_url' => 'http_request_transform',
    ];
    
    /**
     * 规则管理页面
     */
    public function index(): void
    {
        $this->render('rules/index');
    }
    
    /**
     * 获取规则列表
     */
    public function list(): void
    {
        $data = $this->getPostData();
        $zoneId = $data['zone_id'] ?? '';
        $ruleType = $data['rule_type'] ?? 'waf_custom';
        
        if (empty($zoneId)) {
            $this->json(['success' => false, 'message' => '请指定域名']);
            return;
        }
        
        if ($ruleType === 'page_rules') {
            $result = $this->api->get("/zones/{$zoneId}/pagerules");
        } else {
            $phase = $this->rulesetPhases[$ruleType] ?? $ruleType;
            $result = $this->api->get("/zones/{$zoneId}/rulesets/phases/{$phase}/entrypoint");
        }
        
        $this->json($result);
    }

    /**
     * 计算域名的规则总数（优化版 - 轻量级检查）
     */
    public function count(): void
    {
        $data = $this->getPostData();
        $zoneId = $data['zone_id'] ?? '';
        
        if (empty($zoneId)) {
            $this->json(['success' => false, 'message' => '请指定域名']);
            return;
        }

        $totalRuleCount = 0;
        
        // 1. 计算页面规则数
        $pageRules = $this->api->get("/zones/{$zoneId}/pagerules");
        if ($pageRules['success'] ?? false) {
            $pageRuleCount = count($pageRules['result'] ?? []);
            $totalRuleCount += $pageRuleCount;
        }
        
        // 2. 计算其他规则集数量
        foreach ($this->rulesetPhases as $phase) {
            if ($phase === 'page_rules') continue;
            $rulesetResult = $this->api->get("/zones/{$zoneId}/rulesets/phases/{$phase}/entrypoint");
            if ($rulesetResult['success'] ?? false) {
                $rulesetCount = count($rulesetResult['result']['rules'] ?? []);
                $totalRuleCount += $rulesetCount;
            }
        }
        
        $this->json([
            'success' => true,
            'zone_id' => $zoneId,
            'rule_count' => $totalRuleCount
        ]);
    }
    
    /**
     * 检查域名是否有规则（轻量级）
     */
    public function hasRules(): void
    {
        $data = $this->getPostData();
        $zoneId = $data['zone_id'] ?? '';
        
        if (empty($zoneId)) {
            $this->json(['success' => false, 'message' => '请指定域名']);
            return;
        }

        $hasRules = false;
        
        // 1. 检查页面规则
        $pageRules = $this->api->get("/zones/{$zoneId}/pagerules");
        if ($pageRules['success'] ?? false) {
            if (count($pageRules['result'] ?? []) > 0) {
                $hasRules = true;
            }
        }
        
        // 2. 检查其他规则集（仅检查第一个阶段）
        if (!$hasRules) {
            foreach ($this->rulesetPhases as $phase) {
                if ($phase === 'page_rules') continue;
                $rulesetResult = $this->api->get("/zones/{$zoneId}/rulesets/phases/{$phase}/entrypoint");
                if ($rulesetResult['success'] ?? false) {
                    if (count($rulesetResult['result']['rules'] ?? []) > 0) {
                        $hasRules = true;
                        break;
                    }
                }
            }
        }
        
        $this->json([
            'success' => true,
            'zone_id' => $zoneId,
            'has_rules' => $hasRules
        ]);
    }
    
    /**
     * 获取指定域名的所有规则详情
     */
    public function getAllRules(): void
    {
        $data = $this->getPostData();
        $zoneId = $data['zone_id'] ?? '';
        
        if (empty($zoneId)) {
            $this->json(['success' => false, 'message' => '请指定域名']);
            return;
        }

        $allRules = [];
        
        // 1. 获取页面规则
        $pageRules = $this->api->get("/zones/{$zoneId}/pagerules");
        if ($pageRules['success'] ?? false) {
            foreach ($pageRules['result'] ?? [] as $rule) {
                $targets = [];
                foreach ($rule['targets'] ?? [] as $t) $targets[] = $t['constraint']['value'] ?? '';
                $actions = [];
                foreach ($rule['actions'] ?? [] as $a) $actions[] = $a['id'];
                
                $allRules[] = [
                    'type' => '页面规则 (Page Rules)',
                    'description' => implode(', ', $targets),
                    'action' => implode(', ', $actions),
                    'status' => $rule['status'] ?? 'active'
                ];
            }
        }
        
        // 2. 获取其他规则集
        $typeNames = [
            'waf_custom' => 'WAF 自定义规则',
            'transform_request_header' => '修改请求头',
            'transform_response_header' => '修改响应头',
            'redirect' => '重定向规则',
            'origin' => 'Origin Rules',
            'cache' => 'Cache Rules',
            'config' => 'Configuration Rules',
            'rewrite_url' => '重写 URL',
        ];

        foreach ($this->rulesetPhases as $key => $phase) {
            if ($phase === 'page_rules') continue;
            $rulesetResult = $this->api->get("/zones/{$zoneId}/rulesets/phases/{$phase}/entrypoint");
            if ($rulesetResult['success'] ?? false) {
                foreach ($rulesetResult['result']['rules'] ?? [] as $rule) {
                    $allRules[] = [
                        'type' => $typeNames[$key] ?? $key,
                        'description' => $rule['description'] ?? $rule['expression'] ?? '',
                        'action' => $rule['action'] ?? '',
                        'status' => ($rule['enabled'] ?? true) ? 'active' : 'disabled'
                    ];
                }
            }
        }
        
        $this->json([
            'success' => true,
            'zone_id' => $zoneId,
            'rules' => $allRules
        ]);
    }
    
    /**
     * 复制规则到其他域名
     */
    public function copy(): void
    {
        $data = $this->getPostData();
        $sourceZoneId = $data['source_zone_id'] ?? '';
        $targetZoneIds = $this->getSelectedZones($data);
        $ruleType = $data['rule_type'] ?? 'waf_custom';
        
        if (empty($sourceZoneId)) {
            $this->json(['success' => false, 'message' => '请指定源域名']);
            return;
        }
        
        if (empty($targetZoneIds)) {
            $this->json(['success' => false, 'message' => '请选择目标域名']);
            return;
        }
        
        // 获取源规则
        if ($ruleType === 'page_rules') {
            $sourceRules = $this->api->get("/zones/{$sourceZoneId}/pagerules");
        } else {
            $phase = $this->rulesetPhases[$ruleType] ?? $ruleType;
            $sourceRules = $this->api->get("/zones/{$sourceZoneId}/rulesets/phases/{$phase}/entrypoint");
        }
        
        if (!($sourceRules['success'] ?? false)) {
            $this->json(['success' => false, 'message' => '获取源规则失败']);
            return;
        }
        
        // 获取 Zone ID 到域名的映射
        $zoneMap = $this->getZoneMap();
        
        $results = [];
        $successCount = 0;
        $failCount = 0;
        
        foreach ($targetZoneIds as $targetZoneId) {
            if ($targetZoneId === $sourceZoneId) continue;
            
            if ($ruleType === 'page_rules') {
                $zoneSuccess = $this->copyPageRules($sourceRules['result'] ?? [], $targetZoneId);
            } else {
                $zoneSuccess = $this->copyRulesetRules($sourceRules['result'] ?? [], $targetZoneId, $phase);
            }
            
            // 获取目标域名
            $zoneName = $zoneMap[$targetZoneId] ?? $targetZoneId;
            
            if ($zoneSuccess) {
                $successCount++;
                $results[] = ['zone_id' => $zoneName, 'success' => true, 'message' => '复制成功'];
            } else {
                $failCount++;
                $results[] = ['zone_id' => $zoneName, 'success' => false, 'message' => '复制失败'];
            }
            
            usleep(300000);
        }
        
        $this->json([
            'success' => true,
            'message' => "完成: 成功 {$successCount} 个, 失败 {$failCount} 个",
            'data' => $results,
            'summary' => ['success' => $successCount, 'fail' => $failCount],
        ]);
    }
    
    /**
     * 批量删除规则
     */
    public function delete(): void
    {
        $data = $this->getPostData();
        $zoneIds = $this->getSelectedZones($data);
        $ruleType = $data['rule_type'] ?? 'waf_custom';
        
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
            $success = $this->deleteRules($zoneId, $ruleType);
            
            if ($success) {
                $successCount++;
                $results[] = ['zone_id' => $zoneName, 'success' => true, 'message' => '删除成功'];
            } else {
                $failCount++;
                $results[] = ['zone_id' => $zoneName, 'success' => false, 'message' => '删除失败'];
            }
            usleep(200000);
        }
        
        $this->json([
            'success' => true,
            'message' => "完成: 成功 {$successCount} 个, 失败 {$failCount} 个",
            'data' => $results,
            'summary' => ['success' => $successCount, 'fail' => $failCount],
        ]);
    }
    
    /**
     * 批量删除（别名）
     */
    public function batchDelete(): void
    {
        $this->delete();
    }
    
    /**
     * 复制页面规则
     */
    private function copyPageRules(array $rules, string $targetZoneId): bool
    {
        if (empty($rules)) return true;
        
        $success = true;
        foreach ($rules as $rule) {
            $newRule = [
                'targets' => $rule['targets'] ?? [],
                'actions' => $rule['actions'] ?? [],
                'priority' => $rule['priority'] ?? 1,
                'status' => $rule['status'] ?? 'active',
            ];
            
            $result = $this->api->post("/zones/{$targetZoneId}/pagerules", $newRule);
            if (!($result['success'] ?? false)) {
                $success = false;
            }
            usleep(100000);
        }
        
        return $success;
    }
    
    /**
     * 复制规则集规则
     */
    private function copyRulesetRules(array $ruleset, string $targetZoneId, string $phase): bool
    {
        $rules = $ruleset['rules'] ?? [];
        if (empty($rules)) return true;
        
        // 清理规则数据（移除 ID 等）
        $cleanRules = [];
        foreach ($rules as $rule) {
            $cleanRule = [
                'expression' => $rule['expression'] ?? '',
                'action' => $rule['action'] ?? '',
                'description' => $rule['description'] ?? '',
                'enabled' => $rule['enabled'] ?? true,
            ];
            
            if (!empty($rule['action_parameters'])) {
                $cleanRule['action_parameters'] = $rule['action_parameters'];
            }
            
            $cleanRules[] = $cleanRule;
        }
        
        // 使用 PUT 更新整个规则集
        $result = $this->api->put("/zones/{$targetZoneId}/rulesets/phases/{$phase}/entrypoint", [
            'rules' => $cleanRules,
        ]);
        
        return $result['success'] ?? false;
    }
    
    /**
     * 删除指定 Zone 的规则
     */
    private function deleteRules(string $zoneId, string $ruleType): bool
    {
        if ($ruleType === 'page_rules') {
            // 获取所有页面规则
            $rules = $this->api->get("/zones/{$zoneId}/pagerules");
            if (!($rules['success'] ?? false)) return false;
            
            foreach ($rules['result'] ?? [] as $rule) {
                $this->api->delete("/zones/{$zoneId}/pagerules/{$rule['id']}");
                usleep(100000);
            }
            return true;
        } else {
            $phase = $this->rulesetPhases[$ruleType] ?? $ruleType;
            // 清空规则集
            $result = $this->api->put("/zones/{$zoneId}/rulesets/phases/{$phase}/entrypoint", [
                'rules' => [],
            ]);
            return $result['success'] ?? false;
        }
    }
}
