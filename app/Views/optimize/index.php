<div class="page-header">
    <h2><i class="bi bi-speedometer2"></i> 批量代码压缩 / 网络优化</h2>
    <p class="text-muted">压缩 HTML、JS、CSS 代码，Brotli 压缩，图像压缩，资源预加载等</p>
</div>

<div class="row">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">选择域名</div>
            <div class="card-body">
                <div id="optimizeZoneSelector"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">压缩设置</div>
            <div class="card-body">
                <h6 class="text-muted mb-3">代码压缩 (Minify)</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">HTML 压缩</label>
                        <select class="form-select" id="minifyHtml">
                            <option value="">不修改</option>
                            <option value="on">开启</option>
                            <option value="off">关闭</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">CSS 压缩</label>
                        <select class="form-select" id="minifyCss">
                            <option value="">不修改</option>
                            <option value="on">开启</option>
                            <option value="off">关闭</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">JS 压缩</label>
                        <select class="form-select" id="minifyJs">
                            <option value="">不修改</option>
                            <option value="on">开启</option>
                            <option value="off">关闭</option>
                        </select>
                    </div>
                </div>
                
                <h6 class="text-muted mb-3">传输压缩</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Brotli 压缩</label>
                        <select class="form-select" id="brotli">
                            <option value="">不修改</option>
                            <option value="on">开启</option>
                            <option value="off">关闭</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">图像优化 (Polish)</label>
                        <select class="form-select" id="polish">
                            <option value="">不修改</option>
                            <option value="lossless">无损压缩</option>
                            <option value="lossy">有损压缩</option>
                            <option value="off">关闭</option>
                        </select>
                    </div>
                </div>
                
                <h6 class="text-muted mb-3">网络协议</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">HTTP/2</label>
                        <select class="form-select" id="http2">
                            <option value="">不修改</option>
                            <option value="on">开启</option>
                            <option value="off">关闭</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">HTTP/3</label>
                        <select class="form-select" id="http3">
                            <option value="">不修改</option>
                            <option value="on">开启</option>
                            <option value="off">关闭</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">0-RTT</label>
                        <select class="form-select" id="zeroRtt">
                            <option value="">不修改</option>
                            <option value="on">开启</option>
                            <option value="off">关闭</option>
                        </select>
                    </div>
                </div>
                
                <h6 class="text-muted mb-3">其他优化</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Early Hints</label>
                        <select class="form-select" id="earlyHints">
                            <option value="">不修改</option>
                            <option value="on">开启</option>
                            <option value="off">关闭</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">IPv6</label>
                        <select class="form-select" id="ipv6">
                            <option value="">不修改</option>
                            <option value="on">开启</option>
                            <option value="off">关闭</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">WebSockets</label>
                        <select class="form-select" id="websockets">
                            <option value="">不修改</option>
                            <option value="on">开启</option>
                            <option value="off">关闭</option>
                        </select>
                    </div>
                </div>
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">资源预加载 (Prefetch)</label>
                        <select class="form-select" id="prefetch">
                            <option value="">不修改</option>
                            <option value="on">开启</option>
                            <option value="off">关闭</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">每批处理数量</label>
                    <select class="form-select" id="optimizeBatchSize">
                        <option value="5">5 个/批</option>
                        <option value="10" selected>10 个/批</option>
                        <option value="20">20 个/批</option>
                    </select>
                </div>
                <button class="btn btn-cf btn-lg" onclick="batchOptimize()">
                    <i class="bi bi-check-circle"></i> 批量应用优化设置
                </button>
            </div>
        </div>
        
        <div class="card mt-3" id="optimizeResultCard" style="display:none;">
            <div class="card-header d-flex justify-content-between align-items-center">
                执行结果 <span id="optimizeProgress" class="badge bg-primary"></span>
            </div>
            <div class="card-body">
                <div class="result-log" id="optimizeResultLog"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    CF.renderZoneSelector('optimizeZoneSelector');
});

async function batchOptimize() {
    const zoneIds = CF.getSelectedZoneIds('optimizeZoneSelector');
    if (zoneIds.length === 0) { CF.toast('请选择域名', 'error'); return; }
    
    const extraData = {};
    
    // 代码压缩
    const html = document.getElementById('minifyHtml').value;
    const css = document.getElementById('minifyCss').value;
    const js = document.getElementById('minifyJs').value;
    if (html || css || js) {
        extraData.minify = JSON.stringify({ html: html || 'off', css: css || 'off', js: js || 'off' });
    }
    
    const brotli = document.getElementById('brotli').value;
    const polish = document.getElementById('polish').value;
    const http2 = document.getElementById('http2').value;
    const http3 = document.getElementById('http3').value;
    const zeroRtt = document.getElementById('zeroRtt').value;
    const earlyHints = document.getElementById('earlyHints').value;
    const ipv6 = document.getElementById('ipv6').value;
    const websockets = document.getElementById('websockets').value;
    const prefetch = document.getElementById('prefetch').value;
    
    if (brotli) extraData.brotli = brotli;
    if (polish) extraData.polish = polish;
    if (http2) extraData.http2 = http2;
    if (http3) extraData.http3 = http3;
    if (zeroRtt) extraData['0rtt'] = zeroRtt;
    if (earlyHints) extraData.early_hints = earlyHints;
    if (ipv6) extraData.ipv6 = ipv6;
    if (websockets) extraData.websockets = websockets;
    if (prefetch) extraData.prefetch_preload = prefetch;
    
    const batchSize = parseInt(document.getElementById('optimizeBatchSize').value);
    CF.showLoading('正在批量应用优化设置...');
    document.getElementById('optimizeResultCard').style.display = 'block';
    document.getElementById('optimizeProgress').textContent = '';
    CF.clearLog('optimizeResultLog');
    
    const result = await CF.batchRequest('/optimize/batch', zoneIds, extraData, batchSize, 'optimizeResultLog');
    
    CF.hideLoading();
    CF.log('optimizeResultLog', `\n${result.message}`, 'info');
    document.getElementById('optimizeProgress').textContent = result.message;
    CF.toast(result.message, result.summary?.fail > 0 ? 'error' : 'success');
}
</script>
