<div class="page-header">
    <h2><i class="bi bi-download"></i> 批量导出域名</h2>
    <p class="text-muted">批量查看或导出您在 CloudFlare 中的域名</p>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        域名列表
        <div class="btn-group">
            <button class="btn btn-sm btn-cf" onclick="exportZones('json')">
                <i class="bi bi-filetype-json"></i> 导出 JSON
            </button>
            <button class="btn btn-sm btn-cf-outline" onclick="exportZones('csv')">
                <i class="bi bi-filetype-csv"></i> 导出 CSV
            </button>
            <button class="btn btn-sm btn-cf-outline" onclick="exportZones('txt')">
                <i class="bi bi-filetype-txt"></i> 仅域名 TXT
            </button>
        </div>
    </div>
    <div class="card-body">
        <button class="btn btn-cf-outline mb-3" onclick="loadExportList()">
            <i class="bi bi-arrow-clockwise"></i> 加载域名列表
        </button>
        <div id="exportTable">
            <p class="text-muted">点击"加载域名列表"查看所有域名</p>
        </div>
    </div>
</div>

<script>
async function loadExportList() {
    CF.showLoading('加载域名列表...');
    
    const result = await CF.request('/zones/export', {});
    
    CF.hideLoading();
    
    if (result.success && result.data) {
        let html = `<div class="mb-2 text-muted">共 ${result.data.length} 个域名</div>`;
        html += '<table class="table table-hover table-sm"><thead><tr><th>#</th><th>域名</th><th>Zone ID</th><th>状态</th><th>套餐</th><th>NS 服务器</th><th>创建时间</th></tr></thead><tbody>';
        
        result.data.forEach((zone, i) => {
            const statusBadge = zone.status === 'active' ? 'bg-success' : 'bg-warning';
            html += `<tr>
                <td>${i + 1}</td>
                <td><strong>${zone.name}</strong></td>
                <td><code class="small">${zone.id}</code></td>
                <td><span class="badge ${statusBadge}">${zone.status}</span></td>
                <td>${zone.plan || '-'}</td>
                <td><small>${zone.name_servers || '-'}</small></td>
                <td><small>${zone.created_on ? new Date(zone.created_on).toLocaleDateString() : '-'}</small></td>
            </tr>`;
        });
        
        html += '</tbody></table>';
        document.getElementById('exportTable').innerHTML = html;
    } else {
        CF.toast('加载失败', 'error');
    }
}

async function exportZones(format) {
    if (format === 'json') {
        const result = await CF.request('/export/zones', { format: 'json' });
        if (result.success) {
            const blob = new Blob([JSON.stringify(result.data, null, 2)], { type: 'application/json' });
            downloadBlob(blob, `cloudflare_zones_${getDate()}.json`);
        }
    } else {
        // CSV 和 TXT 直接下载
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/export/zones';
        form.innerHTML = `<input type="hidden" name="format" value="${format}">`;
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
}

function downloadBlob(blob, filename) {
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    URL.revokeObjectURL(url);
}

function getDate() {
    return new Date().toISOString().split('T')[0];
}
</script>
