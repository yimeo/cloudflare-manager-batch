<div class="page-header">
    <h2><i class="bi bi-shield-check"></i> 批量开关代理</h2>
    <p class="text-muted">将域名解析值中的代理加速开启或关闭，开启代理后将获得 CDN 缓存功能</p>
</div>

<div class="row">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">选择域名</div>
            <div class="card-body">
                <div id="proxyZoneSelector"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">代理设置</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">操作</label>
                        <select class="form-select" id="proxyAction">
                            <option value="1">开启代理（橙色云朵）</option>
                            <option value="0">关闭代理（灰色云朵）</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">筛选记录类型</label>
                        <select class="form-select" id="proxyRecordType">
                            <option value="">所有支持的类型 (A/AAAA/CNAME)</option>
                            <option value="A">仅 A 记录</option>
                            <option value="AAAA">仅 AAAA 记录</option>
                            <option value="CNAME">仅 CNAME 记录</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">筛选记录名称（可选）</label>
                        <input type="text" class="form-control" id="proxyRecordName" placeholder="留空处理所有记录，或输入如 www、@ 等">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">每批处理数量</label>
                        <select class="form-select" id="proxyBatchSize">
                            <option value="5">5 个/批（稳定）</option>
                            <option value="10" selected>10 个/批（推荐）</option>
                            <option value="20">20 个/批（较快）</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <button class="btn btn-cf btn-lg" onclick="batchProxy()">
                        <i class="bi bi-lightning"></i> 批量执行
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card mt-3" id="proxyResultCard" style="display:none;">
            <div class="card-header d-flex justify-content-between align-items-center">
                执行结果 <span id="proxyProgress" class="badge bg-primary"></span>
            </div>
            <div class="card-body">
                <div class="result-log" id="proxyResultLog"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    CF.renderZoneSelector('proxyZoneSelector');
});

async function batchProxy() {
    const zoneIds = CF.getSelectedZoneIds('proxyZoneSelector');
    if (zoneIds.length === 0) { CF.toast('请选择域名', 'error'); return; }
    
    const proxied = document.getElementById('proxyAction').value === '1';
    const action = proxied ? '开启' : '关闭';
    const batchSize = parseInt(document.getElementById('proxyBatchSize').value);
    
    CF.showLoading(`正在批量${action}代理...`);
    document.getElementById('proxyResultCard').style.display = 'block';
    document.getElementById('proxyProgress').textContent = '';
    CF.clearLog('proxyResultLog');
    
    const result = await CF.batchRequest('/proxy/batch', zoneIds, {
        proxied: proxied,
        record_type: document.getElementById('proxyRecordType').value,
        record_name: document.getElementById('proxyRecordName').value
    }, batchSize, 'proxyResultLog');
    
    CF.hideLoading();
    CF.log('proxyResultLog', `\n${result.message}`, 'info');
    document.getElementById('proxyProgress').textContent = result.message;
    CF.toast(result.message, result.summary.fail > 0 ? 'error' : 'success');
}
</script>
