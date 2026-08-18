<?php
namespace App\Controllers;

use App\Services\CloudFlareApi;

class AuthController extends BaseController
{
    /**
     * 保存认证信息
     */
    public function save(): void
    {
        $data = $this->getPostData();
        
        $authType = $data['auth_type'] ?? 'token';
        
        if ($authType === 'token') {
            if (empty($data['api_token'])) {
                $this->json(['success' => false, 'message' => 'API Token 不能为空']);
                return;
            }
            $_SESSION['cf_auth'] = [
                'auth_type' => 'token',
                'api_token' => trim($data['api_token']),
            ];
        } else {
            if (empty($data['email']) || empty($data['api_key'])) {
                $this->json(['success' => false, 'message' => 'Email 和 API Key 不能为空']);
                return;
            }
            $_SESSION['cf_auth'] = [
                'auth_type' => 'key',
                'email' => trim($data['email']),
                'api_key' => trim($data['api_key']),
            ];
        }
        
        // 验证
        $api = new CloudFlareApi();
        $result = $api->verifyAuth();
        
        if ($result['success'] ?? false) {
            $this->json(['success' => true, 'message' => '认证成功']);
        } else {
            unset($_SESSION['cf_auth']);
            $errorMsg = $result['errors'][0]['message'] ?? '认证失败，请检查凭证';
            $this->json(['success' => false, 'message' => $errorMsg]);
        }
    }
    
    /**
     * 验证当前认证状态
     */
    public function verify(): void
    {
        if (empty($_SESSION['cf_auth'])) {
            $this->json(['success' => false, 'message' => '未认证']);
            return;
        }
        
        $api = new CloudFlareApi();
        $result = $api->verifyAuth();
        
        $this->json([
            'success' => $result['success'] ?? false,
            'message' => ($result['success'] ?? false) ? '认证有效' : '认证已失效',
        ]);
    }
    
    /**
     * 退出登录
     */
    public function logout(): void
    {
        unset($_SESSION['cf_auth']);
        session_destroy();
        header('Location: /');
        exit;
    }
}
