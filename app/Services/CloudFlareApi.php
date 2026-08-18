<?php
namespace App\Services;

/**
 * CloudFlare API v4 封装类
 * 支持 API Token 和 API Key 两种认证方式
 */
class CloudFlareApi
{
    private string $baseUrl = 'https://api.cloudflare.com/client/v4';
    private string $authType;
    private string $apiToken;
    private string $apiKey;
    private string $email;
    
    public function __construct()
    {
        $auth = $_SESSION['cf_auth'] ?? [];
        $this->authType = $auth['auth_type'] ?? 'token';
        $this->apiToken = $auth['api_token'] ?? '';
        $this->apiKey = $auth['api_key'] ?? '';
        $this->email = $auth['email'] ?? '';
    }
    
    /**
     * 发送 API 请求
     */
    public function request(string $method, string $endpoint, array $data = [], array $query = []): array
    {
        $url = $this->baseUrl . $endpoint;
        
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $headers = $this->getAuthHeaders();
        $headers[] = 'Content-Type: application/json';
        
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'PATCH':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($data)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['success' => false, 'errors' => [['message' => "cURL Error: $error"]]];
        }
        
        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'errors' => [['message' => "JSON解析错误: $response"]]];
        }
        
        return $result;
    }
    
    /**
     * GET 请求
     */
    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, [], $query);
    }
    
    /**
     * POST 请求
     */
    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, $data);
    }
    
    /**
     * PUT 请求
     */
    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, $data);
    }
    
    /**
     * PATCH 请求
     */
    public function patch(string $endpoint, array $data = []): array
    {
        return $this->request('PATCH', $endpoint, $data);
    }
    
    /**
     * DELETE 请求
     */
    public function delete(string $endpoint, array $data = []): array
    {
        return $this->request('DELETE', $endpoint, $data);
    }
    
    /**
     * 批量执行操作
     */
    public function batchExecute(array $operations, int $delayMs = 200): array
    {
        $results = [];
        foreach ($operations as $index => $op) {
            $method = $op['method'] ?? 'GET';
            $endpoint = $op['endpoint'] ?? '';
            $data = $op['data'] ?? [];
            $query = $op['query'] ?? [];
            
            $results[] = [
                'index' => $index,
                'params' => $op,
                'result' => $this->request($method, $endpoint, $data, $query),
            ];
            
            // 请求间隔，避免触发速率限制
            if ($delayMs > 0 && $index < count($operations) - 1) {
                usleep($delayMs * 1000);
            }
        }
        return $results;
    }
    
    /**
     * 获取所有 Zone（自动分页）
     */
    public function getAllZones(array $params = []): array
    {
        $allZones = [];
        $page = 1;
        $perPage = 50;
        
        do {
            $query = array_merge($params, ['page' => $page, 'per_page' => $perPage]);
            $response = $this->get('/zones', $query);
            
            if (!($response['success'] ?? false)) {
                return $response;
            }
            
            $zones = $response['result'] ?? [];
            $allZones = array_merge($allZones, $zones);
            
            $totalPages = $response['result_info']['total_pages'] ?? 1;
            $page++;
        } while ($page <= $totalPages);
        
        return ['success' => true, 'result' => $allZones, 'result_info' => ['total_count' => count($allZones)]];
    }
    
    /**
     * 获取认证头
     */
    private function getAuthHeaders(): array
    {
        if ($this->authType === 'token') {
            return ["Authorization: Bearer {$this->apiToken}"];
        } else {
            return [
                "X-Auth-Email: {$this->email}",
                "X-Auth-Key: {$this->apiKey}",
            ];
        }
    }
    
    /**
     * 验证认证信息是否有效
     */
    public function verifyAuth(): array
    {
        if ($this->authType === 'token') {
            return $this->get('/user/tokens/verify');
        } else {
            return $this->get('/user');
        }
    }
}
