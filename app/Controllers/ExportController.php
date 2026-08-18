<?php
namespace App\Controllers;

class ExportController extends BaseController
{
    /**
     * 导出页面
     */
    public function index(): void
    {
        $this->render('export/index');
    }
    
    /**
     * 导出域名列表
     */
    public function zones(): void
    {
        $data = $this->getPostData();
        $format = $data['format'] ?? 'json'; // json, csv, txt
        
        $result = $this->api->getAllZones();
        
        if (!($result['success'] ?? false)) {
            $this->json($result);
            return;
        }
        
        $zones = $result['result'];
        
        switch ($format) {
            case 'csv':
                $this->exportCsv($zones);
                break;
            case 'txt':
                $this->exportTxt($zones);
                break;
            default:
                $this->json([
                    'success' => true,
                    'data' => array_map(function ($zone) {
                        return [
                            'id' => $zone['id'],
                            'name' => $zone['name'],
                            'status' => $zone['status'],
                            'plan' => $zone['plan']['name'] ?? '',
                            'name_servers' => $zone['name_servers'] ?? [],
                            'created_on' => $zone['created_on'] ?? '',
                            'modified_on' => $zone['modified_on'] ?? '',
                        ];
                    }, $zones),
                    'total' => count($zones),
                ]);
        }
    }
    
    /**
     * 导出为 CSV
     */
    private function exportCsv(array $zones): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="cloudflare_zones_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['域名', 'Zone ID', '状态', '套餐', 'NS服务器', '创建时间']);
        
        foreach ($zones as $zone) {
            fputcsv($output, [
                $zone['name'],
                $zone['id'],
                $zone['status'],
                $zone['plan']['name'] ?? '',
                implode(' | ', $zone['name_servers'] ?? []),
                $zone['created_on'] ?? '',
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * 导出为纯文本（仅域名）
     */
    private function exportTxt(array $zones): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="cloudflare_domains_' . date('Y-m-d') . '.txt"');
        
        foreach ($zones as $zone) {
            echo $zone['name'] . "\n";
        }
        exit;
    }
}
