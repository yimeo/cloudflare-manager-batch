<?php
namespace App\Controllers;

/**
 * AI 爬虫管理控制器
 * 管理所有与拦截 AI 爬虫和自动化训练程序相关的安全选项
 * 支持批量和单独设置
 */
class AiCrawlerController extends BaseController
{
    // 已知的 AI 爬虫列表
    private array $knownCrawlers = [
        'GPTBot' => ['operator' => 'OpenAI', 'category' => 'AI Training', 'user_agent' => 'GPTBot'],
        'ChatGPT-User' => ['operator' => 'OpenAI', 'category' => 'AI Assistant', 'user_agent' => 'ChatGPT-User'],
        'Google-Extended' => ['operator' => 'Google', 'category' => 'AI Training', 'user_agent' => 'Google-Extended'],
        'Googlebot-AI' => ['operator' => 'Google', 'category' => 'AI Search', 'user_agent' => 'Googlebot-AI'],
        'CCBot' => ['operator' => 'Common Crawl', 'category' => 'AI Training', 'user_agent' => 'CCBot'],
        'anthropic-ai' => ['operator' => 'Anthropic', 'category' => 'AI Training', 'user_agent' => 'anthropic-ai'],
        'ClaudeBot' => ['operator' => 'Anthropic', 'category' => 'AI Assistant', 'user_agent' => 'ClaudeBot'],
        'Bytespider' => ['operator' => 'ByteDance', 'category' => 'AI Training', 'user_agent' => 'Bytespider'],
        'Diffbot' => ['operator' => 'Diffbot', 'category' => 'AI Training', 'user_agent' => 'Diffbot'],
        'FacebookBot' => ['operator' => 'Meta', 'category' => 'AI Training', 'user_agent' => 'FacebookBot'],
        'Meta-ExternalAgent' => ['operator' => 'Meta', 'category' => 'AI Training', 'user_agent' => 'Meta-ExternalAgent'],
        'PerplexityBot' => ['operator' => 'Perplexity', 'category' => 'AI Search', 'user_agent' => 'PerplexityBot'],
        'Applebot-Extended' => ['operator' => 'Apple', 'category' => 'AI Training', 'user_agent' => 'Applebot-Extended'],
        'cohere-ai' => ['operator' => 'Cohere', 'category' => 'AI Training', 'user_agent' => 'cohere-ai'],
        'Amazonbot' => ['operator' => 'Amazon', 'category' => 'AI Training', 'user_agent' => 'Amazonbot'],
        'OAI-SearchBot' => ['operator' => 'OpenAI', 'category' => 'AI Search', 'user_agent' => 'OAI-SearchBot'],
        'YouBot' => ['operator' => 'You.com', 'category' => 'AI Search', 'user_agent' => 'YouBot'],
        'Scrapy' => ['operator' => 'Various', 'category' => 'Scraper', 'user_agent' => 'Scrapy'],
        'Timpibot' => ['operator' => 'Timpi', 'category' => 'AI Training', 'user_agent' => 'Timpibot'],
        'VelenPublicWebCrawler' => ['operator' => 'Velen', 'category' => 'AI Training', 'user_agent' => 'VelenPublicWebCrawler'],
        'omgili' => ['operator' => 'Webz.io', 'category' => 'AI Training', 'user_agent' => 'omgili'],
        'Kangaroo Bot' => ['operator' => 'Kangaroo', 'category' => 'AI Training', 'user_agent' => 'Kangaroo Bot'],
        'img2dataset' => ['operator' => 'Various', 'category' => 'AI Training', 'user_agent' => 'img2dataset'],
    ];
    
    // AI 爬虫分类
    private array $categories = [
        'AI Training' => '用于训练 AI 模型的爬虫',
        'AI Search' => '用于 AI 搜索引擎的爬虫',
        'AI Assistant' => '用于 AI 助手实时获取内容的爬虫',
        'Scraper' => '通用数据抓取工具',
    ];
    
    /**
     * AI 爬虫管理页面
     */
    public function index(): void
    {
        $this->render('ai_crawlers/index');
    }
    
    /**
     * 获取 AI 爬虫状态列表
     */
    public function list(): void
    {
        $data = $this->getPostData();
        $zoneId = $data['zone_id'] ?? '';
        
        if (empty($zoneId)) {
            // 返回已知爬虫列表
            $this->json([
                'success' => true,
                'data' => [
                    'crawlers' => $this->knownCrawlers,
                    'categories' => $this->categories,
                ],
            ]);
            return;
        }
        
        // 获取该 Zone 的 Bot Management 设置
        $botSettings = $this->getBotSettings($zoneId);
        
        // 获取 WAF 自定义规则中与 AI 爬虫相关的规则
        $wafRules = $this->getAiWafRules($zoneId);
        
        // 获取 Zone 设置中的 AI 相关配置
        $zoneSettings = $this->api->get("/zones/{$zoneId}/settings");
        
        $this->json([
            'success' => true,
            'data' => [
                'crawlers' => $this->knownCrawlers,
                'categories' => $this->categories,
                'bot_settings' => $botSettings,
                'waf_rules' => $wafRules,
                'zone_id' => $zoneId,
            ],
        ]);
    }
    
    /**
     * 更新单个域名的 AI 爬虫设置
     */
    public function update(): void
    {
        $data = $this->getPostData();
        $zoneId = $data['zone_id'] ?? '';
        $action = $data['crawler_action'] ?? 'block'; // block, allow, challenge
        $crawlers = $data['crawlers'] ?? []; // 选中的爬虫列表
        $blockMode = $data['block_mode'] ?? 'waf'; // waf, bot_management
        
        if (empty($zoneId)) {
            $this->json(['success' => false, 'message' => '请指定域名']);
            return;
        }
        
        $results = [];
        
        if ($blockMode === 'bot_management') {
            // 使用 Bot Management 的 Block AI Bots 功能
            $result = $this->updateBotManagement($zoneId, $action, $data);
            $results['bot_management'] = $result;
        }
        
        if ($blockMode === 'waf' || $blockMode === 'both') {
            // 使用 WAF 自定义规则
            $result = $this->updateWafRules($zoneId, $action, $crawlers, $data);
            $results['waf_rules'] = $result;
        }
        
        // 更新 robots.txt 管理设置（如果支持）
        if (!empty($data['enforce_robots_txt'])) {
            $result = $this->updateRobotsTxt($zoneId, $data);
            $results['robots_txt'] = $result;
        }
        
        $this->json([
            'success' => true,
            'message' => '设置已更新',
            'data' => $results,
        ]);
    }
    
    /**
     * 批量设置 AI 爬虫管理
     */
    public function batch(): void
    {
        $data = $this->getPostData();
        $zoneIds = $this->getSelectedZones($data);
        $action = $data['crawler_action'] ?? 'block';
        $crawlers = $data['crawlers'] ?? [];
        $blockMode = $data['block_mode'] ?? 'waf';
        $responseCode = $data['response_code'] ?? '403';
        $responseBody = $data['response_body'] ?? '';
        
        if (empty($zoneIds)) {
            $this->json(['success' => false, 'message' => '请选择域名']);
            return;
        }
        
        // 获取域名映射
        $zoneMap = $this->getZoneMap();
        
        $results = [];
        $successCount = 0;
        $failCount = 0;
        
        foreach ($zoneIds as $zoneId) {
            $zoneName = $zoneMap[$zoneId] ?? $zoneId;
            $zoneResult = $this->applyAiCrawlerSettings($zoneId, [
                'crawler_action' => $action,
                'crawlers' => $crawlers,
                'block_mode' => $blockMode,
                'response_code' => $responseCode,
                'response_body' => $responseBody,
                'block_all_ai' => $data['block_all_ai'] ?? false,
                'block_categories' => $data['block_categories'] ?? [],
            ]);
            
            if ($zoneResult['success']) {
                $successCount++;
                $results[] = ['zone_id' => $zoneName, 'success' => true, 'message' => '设置成功'];
            } else {
                $failCount++;
                $results[] = ['zone_id' => $zoneName, 'success' => false, 'message' => $zoneResult['message'] ?? '设置失败'];
            }
            
            usleep(300000);
        }
        
        $this->json([
            'success' => true,
            'message' => "完成: 成功 {$successCount} 个, 失败 {$failCount} 个",
            'data' => $results,
            'summary' => ['success' => $successCount, 'fail' => $failCount, 'total' => count($zoneIds)],
        ]);
    }
    
    /**
     * 一键拦截所有 AI 爬虫
     */
    public function blockAll(): void
    {
        $data = $this->getPostData();
        $zoneIds = $this->getSelectedZones($data);
        $responseCode = $data['response_code'] ?? '403';
        $responseBody = $data['response_body'] ?? 'Access denied. AI crawling is not permitted on this website.';
        
        if (empty($zoneIds)) {
            $this->json(['success' => false, 'message' => '请选择域名']);
            return;
        }

        // 获取域名映射
        $zoneMap = $this->getZoneMap();
        
        $results = [];
        $successCount = 0;
        $failCount = 0;
        
        foreach ($zoneIds as $zoneId) {
            $zoneName = $zoneMap[$zoneId] ?? $zoneId;
            $result = $this->applyAiCrawlerSettings($zoneId, [
                'crawler_action' => 'block',
                'crawlers' => array_keys($this->knownCrawlers),
                'block_mode' => 'both',
                'block_all_ai' => true,
                'response_code' => $responseCode,
                'response_body' => $responseBody,
            ]);
            
            if ($result['success']) {
                $successCount++;
                $results[] = ['zone_id' => $zoneName, 'success' => true, 'message' => '已拦截所有 AI 爬虫'];
            } else {
                $failCount++;
                $results[] = ['zone_id' => $zoneName, 'success' => false, 'message' => $result['message'] ?? '设置失败'];
            }
            
            usleep(300000);
        }
        
        $this->json([
            'success' => true,
            'message' => "一键拦截完成: 成功 {$successCount} 个, 失败 {$failCount} 个",
            'data' => $results,
            'summary' => ['success' => $successCount, 'fail' => $failCount],
        ]);
    }
    
    /**
     * 一键允许所有 AI 爬虫
     */
    public function allowAll(): void
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
            // 删除 AI 爬虫相关的 WAF 规则
            $wafResult = $this->removeAiWafRules($zoneId);
            
            // 关闭 Block AI Bots
            $botResult = $this->updateBotManagement($zoneId, 'allow', []);
            
            $success = ($wafResult['success'] ?? false) || ($botResult['success'] ?? false);
            
            if ($success) {
                $successCount++;
                $results[] = ['zone_id' => $zoneName, 'success' => true, 'message' => '已允许所有 AI 爬虫'];
            } else {
                $failCount++;
                $results[] = ['zone_id' => $zoneName, 'success' => false, 'message' => '设置失败'];
            }
            
            usleep(300000);
        }
        
        $this->json([
            'success' => true,
            'message' => "一键允许完成: 成功 {$successCount} 个, 失败 {$failCount} 个",
            'data' => $results,
            'summary' => ['success' => $successCount, 'fail' => $failCount],
        ]);
    }
    
    /**
     * AI 爬虫高级设置
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
        
        // 设置 Block AI Bots 模式
        if (isset($data['block_ai_bots_mode'])) {
            // off, all_pages, ads_only
            $results['block_mode'] = $this->updateBotManagement($zoneId, $data['block_ai_bots_mode'], $data);
        }
        
        // 设置自定义拦截响应
        if (isset($data['response_code']) || isset($data['response_body'])) {
            $results['response'] = $this->updateBlockResponse($zoneId, $data);
        }
        
        // 设置 robots.txt 强制执行
        if (isset($data['enforce_robots_txt'])) {
            $results['robots_txt'] = $this->updateRobotsTxt($zoneId, $data);
        }
        
        // 设置按类别拦截
        if (!empty($data['block_categories'])) {
            $results['categories'] = $this->updateCategoryBlocking($zoneId, $data['block_categories'], $data);
        }
        
        $this->json(['success' => true, 'data' => $results]);
    }
    
    /**
     * 应用 AI 爬虫设置到指定 Zone
     */
    private function applyAiCrawlerSettings(string $zoneId, array $config): array
    {
        $action = $config['crawler_action'] ?? 'block';
        $crawlers = $config['crawlers'] ?? [];
        $blockMode = $config['block_mode'] ?? 'waf';
        $blockAllAi = $config['block_all_ai'] ?? false;
        $responseCode = $config['response_code'] ?? '403';
        $responseBody = $config['response_body'] ?? '';
        $blockCategories = $config['block_categories'] ?? [];
        
        $success = true;
        $messages = [];
        
        // 1. 通过 Bot Management 设置
        if (in_array($blockMode, ['bot_management', 'both'])) {
            if ($blockAllAi && $action === 'block') {
                $result = $this->updateBotManagement($zoneId, 'block', $config);
            } elseif ($action === 'allow') {
                $result = $this->updateBotManagement($zoneId, 'allow', $config);
            }
            if (!($result['success'] ?? true)) {
                $messages[] = 'Bot Management 设置失败';
            }
        }
        
        // 2. 通过 WAF 规则设置
        if (in_array($blockMode, ['waf', 'both'])) {
            $targetCrawlers = $blockAllAi ? array_keys($this->knownCrawlers) : $crawlers;
            
            if (!empty($blockCategories) && !$blockAllAi) {
                $targetCrawlers = [];
                foreach ($this->knownCrawlers as $name => $info) {
                    if (in_array($info['category'], $blockCategories)) {
                        $targetCrawlers[] = $name;
                    }
                }
            }
            
            if (!empty($targetCrawlers)) {
                $result = $this->updateWafRules($zoneId, $action, $targetCrawlers, $config);
                // 即使 success 为 false，如果有 result 说明接口是通的，可能只是逻辑错误
                if (!($result['success'] ?? false)) {
                    // 检查是否是因为规则已存在或其它非致命错误
                    $errorMsg = '';
                    if (!empty($result['errors'])) {
                        $errorMsg = $result['errors'][0]['message'] ?? '';
                    }
                    
                    if (stripos($errorMsg, 'duplicate') !== false) {
                        $messages[] = '规则已存在，无需更新';
                    } else {
                        $success = false;
                        $messages[] = 'WAF 设置失败: ' . ($errorMsg ?: ($result['message'] ?? '未知错误'));
                    }
                } else {
                    $messages[] = 'WAF 设置成功';
                }
            }
        }
        
        return [
            'success' => $success,
            'message' => $success ? '设置成功' : implode('; ', $messages),
        ];
    }
    
    /**
     * 获取 Bot Management 设置
     */
    private function getBotSettings(string $zoneId): array
    {
        $result = $this->api->get("/zones/{$zoneId}/bot_management");
        return $result['result'] ?? [];
    }
    
    /**
     * 更新 Bot Management 设置
     */
    private function updateBotManagement(string $zoneId, string $action, array $config): array
    {
        $data = [];
        
        switch ($action) {
            case 'block':
                $data['ai_bots_protection'] = 'block';
                break;
            case 'allow':
            case 'off':
                $data['ai_bots_protection'] = 'disabled';
                break;
            default:
                $data['ai_bots_protection'] = $action;
        }
        
        return $this->api->put("/zones/{$zoneId}/bot_management", $data);
    }
    
    /**
     * 获取 AI 相关的 WAF 规则
     */
    private function getAiWafRules(string $zoneId): array
    {
        $result = $this->api->get("/zones/{$zoneId}/rulesets/phases/http_request_firewall_custom/entrypoint");
        
        if (!($result['success'] ?? false)) {
            return [];
        }
        
        $aiRules = [];
        foreach ($result['result']['rules'] ?? [] as $rule) {
            $desc = $rule['description'] ?? '';
            if (stripos($desc, 'AI') !== false || stripos($desc, 'crawler') !== false || stripos($desc, 'bot') !== false) {
                $aiRules[] = $rule;
            }
        }
        
        return $aiRules;
    }
    
    /**
     * 更新 WAF 规则来拦截/允许 AI 爬虫
     */
    private function updateWafRules(string $zoneId, string $action, array $crawlers, array $config): array
    {
        if (empty($crawlers)) {
            return ['success' => true, 'message' => '无需更新'];
        }
        
        $userAgentConditions = [];
        foreach ($crawlers as $crawler) {
            $ua = $this->knownCrawlers[$crawler]['user_agent'] ?? $crawler;
            $userAgentConditions[] = "http.user_agent contains \"{$ua}\"";
        }
        
        $expression = '(' . implode(' or ', $userAgentConditions) . ')';
        
        $wafAction = 'block';
        $actionParams = [];
        
        switch ($action) {
            case 'block':
                $wafAction = 'block';
                $responseCode = intval($config['response_code'] ?? 403);
                $responseBody = $config['response_body'] ?? '';
                if ($responseBody) {
                    $actionParams = [
                        'response' => [
                            'status_code' => $responseCode,
                            'content' => $responseBody,
                            'content_type' => 'text/plain',
                        ],
                    ];
                }
                break;
            case 'challenge':
                $wafAction = 'managed_challenge';
                break;
            case 'allow':
                $wafAction = 'skip';
                $actionParams = ['ruleset' => 'current'];
                break;
        }
        
        // 获取 Zone 信息以确定 Account ID
        $zoneInfo = $this->api->get("/zones/{$zoneId}");
        $accountId = $zoneInfo['result']['account']['id'] ?? '';
        
        if (!$accountId) {
            return ['success' => false, 'message' => '无法获取 Account ID'];
        }

        // 1. 获取自定义规则集 (Custom Rulesets)
        $rulesets = $this->api->get("/zones/{$zoneId}/rulesets");
        $customRulesetId = '';
        
        if ($rulesets['success'] ?? false) {
            foreach ($rulesets['result'] as $rs) {
                if ($rs['phase'] === 'http_request_firewall_custom') {
                    $customRulesetId = $rs['id'];
                    break;
                }
            }
        }
        
        // 2. 如果不存在自定义规则集，需要先为该 Zone 创建一个（通常 CF 默认有，但 API 操作有时需要显式处理）
        // 实际上大部分情况是直接向入口点 PUT
        
        $rules = [];
        $aiRuleFound = false;
        
        // 获取现有规则
        $existing = $this->api->get("/zones/{$zoneId}/rulesets/phases/http_request_firewall_custom/entrypoint");
        
        if (($existing['success'] ?? false) && !empty($existing['result']['rules'])) {
            foreach ($existing['result']['rules'] as $rule) {
                $desc = $rule['description'] ?? '';
                if ($desc === 'Block AI Crawlers [Auto-Generated]') {
                    $aiRuleFound = true;
                    if ($action !== 'allow') {
                        $newRule = [
                            'expression' => $expression,
                            'action' => $wafAction,
                            'description' => 'Block AI Crawlers [Auto-Generated]',
                            'enabled' => true,
                        ];
                        if (!empty($actionParams)) $newRule['action_parameters'] = $actionParams;
                        $rules[] = $newRule;
                    }
                } else {
                    $cleanRule = [
                        'expression' => $rule['expression'],
                        'action' => $rule['action'],
                        'description' => $rule['description'] ?? '',
                        'enabled' => $rule['enabled'] ?? true,
                    ];
                    if (!empty($rule['action_parameters'])) $cleanRule['action_parameters'] = $rule['action_parameters'];
                    $rules[] = $cleanRule;
                }
            }
        }
        
        if (!$aiRuleFound && $action !== 'allow') {
            $newRule = [
                'expression' => $expression,
                'action' => $wafAction,
                'description' => 'Block AI Crawlers [Auto-Generated]',
                'enabled' => true,
            ];
            if (!empty($actionParams)) $newRule['action_parameters'] = $actionParams;
            array_unshift($rules, $newRule);
        }
        
        // 尝试更新。如果入口点不存在，CF API 会报错，此时可能需要针对 ruleset_id 更新
        $result = $this->api->put("/zones/{$zoneId}/rulesets/phases/http_request_firewall_custom/entrypoint", [
            'rules' => $rules,
        ]);
        
        // 如果因为 "not entitled to use a custom response" 失败（通常是免费版），去掉自定义响应重试
        if (!($result['success'] ?? false) && !empty($result['errors'])) {
            $errorMsg = $result['errors'][0]['message'] ?? '';
            if (stripos($errorMsg, 'custom response') !== false || stripos($errorMsg, 'not entitled') !== false) {
                // 降级处理：去掉所有规则中的 action_parameters (自定义响应)
                foreach ($rules as &$rule) {
                    unset($rule['action_parameters']);
                }
                $result = $this->api->put("/zones/{$zoneId}/rulesets/phases/http_request_firewall_custom/entrypoint", [
                    'rules' => $rules,
                ]);
            }
        }

        // 如果入口点更新失败，尝试通过 ruleset ID 更新
        if (!($result['success'] ?? false) && $customRulesetId) {
            $result = $this->api->put("/zones/{$zoneId}/rulesets/{$customRulesetId}", [
                'rules' => $rules,
            ]);
            
            // 同样处理降级
            if (!($result['success'] ?? false) && !empty($result['errors'])) {
                $errorMsg = $result['errors'][0]['message'] ?? '';
                if (stripos($errorMsg, 'custom response') !== false || stripos($errorMsg, 'not entitled') !== false) {
                    foreach ($rules as &$rule) {
                        unset($rule['action_parameters']);
                    }
                    $result = $this->api->put("/zones/{$zoneId}/rulesets/{$customRulesetId}", [
                        'rules' => $rules,
                    ]);
                }
            }
        }
        
        return $result;
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
     * 移除 AI 爬虫相关的 WAF 规则
     */
    private function removeAiWafRules(string $zoneId): array
    {
        $existing = $this->api->get("/zones/{$zoneId}/rulesets/phases/http_request_firewall_custom/entrypoint");
        
        if (!($existing['success'] ?? false)) {
            return ['success' => true]; // 没有规则集，视为成功
        }
        
        $rules = [];
        foreach ($existing['result']['rules'] ?? [] as $rule) {
            $desc = $rule['description'] ?? '';
            if ($desc !== 'Block AI Crawlers [Auto-Generated]') {
                $cleanRule = [
                    'expression' => $rule['expression'],
                    'action' => $rule['action'],
                    'description' => $rule['description'] ?? '',
                    'enabled' => $rule['enabled'] ?? true,
                ];
                if (!empty($rule['action_parameters'])) {
                    $cleanRule['action_parameters'] = $rule['action_parameters'];
                }
                $rules[] = $cleanRule;
            }
        }
        
        return $this->api->put("/zones/{$zoneId}/rulesets/phases/http_request_firewall_custom/entrypoint", [
            'rules' => $rules,
        ]);
    }
    
    /**
     * 更新拦截响应设置
     */
    private function updateBlockResponse(string $zoneId, array $data): array
    {
        // 通过更新 WAF 规则的 action_parameters 来设置自定义响应
        return $this->updateWafRules($zoneId, 'block', array_keys($this->knownCrawlers), $data);
    }
    
    /**
     * 更新 robots.txt 设置
     */
    private function updateRobotsTxt(string $zoneId, array $data): array
    {
        $enforce = $data['enforce_robots_txt'] ?? false;
        
        // 通过 Bot Management 设置 robots.txt 强制执行
        return $this->api->put("/zones/{$zoneId}/bot_management", [
            'enable_robots_txt' => (bool)$enforce,
        ]);
    }
    
    /**
     * 按类别更新拦截设置
     */
    private function updateCategoryBlocking(string $zoneId, array $categories, array $config): array
    {
        $targetCrawlers = [];
        foreach ($this->knownCrawlers as $name => $info) {
            if (in_array($info['category'], $categories)) {
                $targetCrawlers[] = $name;
            }
        }
        
        if (empty($targetCrawlers)) {
            return ['success' => true, 'message' => '无匹配的爬虫'];
        }
        
        return $this->updateWafRules($zoneId, 'block', $targetCrawlers, $config);
    }
}
