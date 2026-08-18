<div class="page-header">
    <h2><i class="bi bi-lock"></i> HTTPS 边缘证书批量设置</h2>
    <p class="text-muted">设置网址的 HTTPS 加密模式、TLS 版本、自动重定向到 HTTPS 等</p>
</div>

<div class="row">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">选择域名</div>
            <div class="card-body">
                <div id="sslZoneSelector"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">SSL/TLS 设置</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">SSL 加密模式</label>
                    <select class="form-select" id="sslMode">
                        <option value="">不修改</option>
                        <option value="off">关闭 (Off)</option>
                        <option value="flexible">灵活 (Flexible)</option>
                        <option value="full">完全 (Full)</option>
                        <option value="strict">完全（严格）(Full Strict)</option>
                    </select>
                    <div class="form-text">推荐使用"完全（严格）"模式</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">最低 TLS 版本</label>
                    <select class="form-select" id="minTls">
                        <option value="">不修改</option>
                        <option value="1.0">TLS 1.0</option>
                        <option value="1.1">TLS 1.1</option>
                        <option value="1.2">TLS 1.2（推荐）</option>
                        <option value="1.3">TLS 1.3</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">TLS 1.3</label>
                    <select class="form-select" id="tls13">
                        <option value="">不修改</option>
                        <option value="on">开启</option>
                        <option value="off">关闭</option>
                        <option value="zrt">开启 (0-RTT)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">始终使用 HTTPS</label>
                    <select class="form-select" id="alwaysHttps">
                        <option value="">不修改</option>
                        <option value="on">开启</option>
                        <option value="off">关闭</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">自动 HTTPS 重写</label>
                    <select class="form-select" id="autoHttpsRewrites">
                        <option value="">不修改</option>
                        <option value="on">开启</option>
                        <option value="off">关闭</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">每批处理数量</label>
                    <select class="form-select" id="sslBatchSize">
                        <option value="5">5 个/批（稳定）</option>
                        <option value="10" selected>10 个/批（推荐）</option>
                        <option value="20">20 个/批（较快）</option>
                    </select>
                </div>
                <button class="btn btn-cf btn-lg" onclick="batchSsl()">
                    <i class="bi bi-check-circle"></i> 批量应用设置
                </button>
            </div>
        </div>
        
        <div class="card mt-3" id="sslResultCard" style="display:none;">
            <div class="card-header d-flex justify-content-between align-items-center">
                执行结果 <span id="sslProgress" class="badge bg-primary"></span>
            </div>
            <div class="card-body">
                <div class="result-log" id="sslResultLog"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    CF.renderZoneSelector('sslZoneSelector');
});

async function batchSsl() {
    const zoneIds = CF.getSelectedZoneIds('sslZoneSelector');
    if (zoneIds.length === 0) { CF.toast('请选择域名', 'error'); return; }
    
    const sslMode = document.getElementById('sslMode').value;
    const minTls = document.getElementById('minTls').value;
    const tls13 = document.getElementById('tls13').value;
    const alwaysHttps = document.getElementById('alwaysHttps').value;
    const autoRewrites = document.getElementById('autoHttpsRewrites').value;
    
    if (!sslMode && !minTls && !tls13 && !alwaysHttps && !autoRewrites) {
        CF.toast('请至少选择一项设置', 'error');
        return;
    }
    
    const extraData = {};
    if (sslMode) extraData.ssl_mode = sslMode;
    if (minTls) extraData.min_tls_version = minTls;
    if (tls13) extraData.tls_1_3 = tls13;
    if (alwaysHttps) extraData.always_use_https = alwaysHttps;
    if (autoRewrites) extraData.automatic_https_rewrites = autoRewrites;
    
    const batchSize = parseInt(document.getElementById('sslBatchSize').value);
    CF.showLoading('正在批量设置 SSL...');
    document.getElementById('sslResultCard').style.display = 'block';
    document.getElementById('sslProgress').textContent = '';
    CF.clearLog('sslResultLog');
    
    const result = await CF.batchRequest('/ssl/batch', zoneIds, extraData, batchSize, 'sslResultLog');
    
    CF.hideLoading();
    CF.log('sslResultLog', `\n${result.message}`, 'info');
    document.getElementById('sslProgress').textContent = result.message;
    CF.toast(result.message, result.summary.fail > 0 ? 'error' : 'success');
}
</script>
