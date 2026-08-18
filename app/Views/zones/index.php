<div class="page-header">
    <h2><i class="bi bi-globe"></i> 批量添加/删除域名 (Zone)</h2>
    <p class="text-muted">将域名解析权转移到 CloudFlare，需要在域名注册商处更改域名 NS 为 CloudFlare 的 NS</p>
</div>

<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#addZones">批量添加</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#deleteZones">批量删除</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#listZones">域名列表</a></li>
</ul>

<div class="tab-content">
    <!-- 批量添加 -->
    <div class="tab-pane fade show active" id="addZones">
        <div class="card">
            <div class="card-header">批量添加域名到 CloudFlare</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">域名列表（每行一个）</label>
                    <textarea class="form-control" id="addDomainList" rows="8" placeholder="example.com&#10;example.net&#10;example.org"></textarea>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Zone 类型</label>
                        <select class="form-select" id="zoneType">
                            <option value="full">Full（完整接入，推荐）</option>
                            <option value="partial">Partial（CNAME 接入）</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">选项</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="jumpStart" checked>
                            <label class="form-check-label" for="jumpStart">自动扫描 DNS 记录</label>
                        </div>
                    </div>
                </div>
                <button class="btn btn-cf" onclick="addZones()">
                    <i class="bi bi-plus-circle"></i> 批量添加
                </button>
            </div>
        </div>
        <div class="card mt-3" id="addResultCard" style="display:none;">
            <div class="card-header">执行结果</div>
            <div class="card-body">
                <div class="result-log" id="addResultLog"></div>
            </div>
        </div>
    </div>
    
    <!-- 批量删除 -->
    <div class="tab-pane fade" id="deleteZones">
        <div class="card">
            <div class="card-header">批量删除域名</div>
            <div class="card-body">
                <div id="deleteZoneSelector"></div>
                <div class="mt-3">
                    <button class="btn btn-danger" onclick="deleteZones()">
                        <i class="bi bi-trash"></i> 删除选中域名
                    </button>
                </div>
            </div>
        </div>
        <div class="card mt-3" id="deleteResultCard" style="display:none;">
            <div class="card-header">执行结果</div>
            <div class="card-body">
                <div class="result-log" id="deleteResultLog"></div>
            </div>
        </div>
    </div>
    
    <!-- 域名列表 -->
    <div class="tab-pane fade" id="listZones">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                域名列表
                <button class="btn btn-sm btn-cf-outline" onclick="refreshZoneList()">
                    <i class="bi bi-arrow-clockwise"></i> 刷新
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive" id="zoneListTable">
                    <p class="text-muted">点击"刷新"加载域名列表</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    CF.renderZoneSelector('deleteZoneSelector');
});

async function addZones() {
    const domains = document.getElementById('addDomainList').value.trim();
    if (!domains) { CF.toast('请输入域名', 'error'); return; }
    
    CF.showLoading('正在批量添加域名...');
    document.getElementById('addResultCard').style.display = 'block';
    CF.clearLog('addResultLog');
    
    const result = await CF.request('/zones/add', {
        domains: domains,
        zone_type: document.getElementById('zoneType').value,
        jump_start: document.getElementById('jumpStart').checked
    });
    
    CF.hideLoading();
    
    if (result.data) {
        result.data.forEach(item => {
            if (item.success) {
                CF.log('addResultLog', `✓ ${item.domain} - ${item.message}`, 'success');
            } else {
                CF.log('addResultLog', `✗ ${item.domain} - ${item.message}`, 'error');
            }
        });
    }
    
    CF.log('addResultLog', `\n${result.message}`, 'info');
    CF.toast(result.message);
}

async function deleteZones() {
    const zoneIds = CF.getSelectedZoneIds('deleteZoneSelector');
    if (zoneIds.length === 0) { CF.toast('请选择要删除的域名', 'error'); return; }
    
    if (!confirm(`确定要删除选中的 ${zoneIds.length} 个域名吗？此操作不可恢复！`)) return;
    
    CF.showLoading('正在批量删除域名...');
    document.getElementById('deleteResultCard').style.display = 'block';
    CF.clearLog('deleteResultLog');
    
    const result = await CF.batchRequest('/zones/delete', zoneIds, {}, 10, 'deleteResultLog');
    
    CF.hideLoading();
    CF.log('deleteResultLog', `\n${result.message}`, 'info');
    CF.toast(result.message, result.summary?.fail > 0 ? 'error' : 'success');
    CF.renderZoneSelector('deleteZoneSelector');
}

async function refreshZoneList() {
    CF.showLoading('加载域名列表...');
    const result = await CF.request('/zones/list', {});
    CF.hideLoading();
    
    if (result.success && result.result) {
        let html = '<table class="table table-hover"><thead><tr><th>域名</th><th>状态</th><th>套餐</th><th>NS 服务器</th></tr></thead><tbody>';
        result.result.forEach(zone => {
            const statusBadge = zone.status === 'active' ? 'bg-success' : 'bg-warning';
            html += `<tr>
                <td><strong>${zone.name}</strong></td>
                <td><span class="badge ${statusBadge}">${zone.status}</span></td>
                <td>${zone.plan?.name || '-'}</td>
                <td><small>${(zone.name_servers || []).join(', ')}</small></td>
            </tr>`;
        });
        html += '</tbody></table>';
        document.getElementById('zoneListTable').innerHTML = html;
    }
}
</script>
