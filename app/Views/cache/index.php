<div class="page-header">
    <h2><i class="bi bi-hdd-stack"></i> 批量清除缓存 / 缓存设置</h2>
    <p class="text-muted">清除域名缓存、设置缓存级别、Always Online 等功能</p>
</div>

<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#cachePurge">清除缓存</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#cacheSettings">缓存设置</a></li>
</ul>

<div class="tab-content">
    <!-- 清除缓存 -->
    <div class="tab-pane fade show active" id="cachePurge">
        <div class="row">
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">选择域名</div>
                    <div class="card-body">
                        <div id="cachePurgeZoneSelector"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">清除选项</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="purgeType" id="purgeAll" value="all" checked>
                                <label class="form-check-label" for="purgeAll"><strong>清除所有缓存</strong></label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="purgeType" id="purgeFiles" value="files">
                                <label class="form-check-label" for="purgeFiles">按 URL 清除</label>
                            </div>
                        </div>
                        <div id="purgeFilesInput" style="display:none;">
                            <textarea class="form-control" id="purgeFilesList" rows="4" placeholder="每行一个 URL:&#10;https://example.com/style.css&#10;https://example.com/script.js"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">每批处理数量</label>
                            <select class="form-select" id="purgeBatchSize">
                                <option value="5">5 个/批</option>
                                <option value="10" selected>10 个/批</option>
                                <option value="20">20 个/批</option>
                            </select>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-cf btn-lg" onclick="batchPurge()">
                                <i class="bi bi-trash"></i> 批量清除缓存
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 缓存设置 -->
    <div class="tab-pane fade" id="cacheSettings">
        <div class="row">
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">选择域名</div>
                    <div class="card-body">
                        <div id="cacheSettingsZoneSelector"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">缓存设置项</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">缓存级别</label>
                            <select class="form-select" id="cacheLevel">
                                <option value="">不修改</option>
                                <option value="aggressive">标准 (Standard)</option>
                                <option value="basic">基本 (Basic)</option>
                                <option value="simplified">简化 (Simplified)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">浏览器缓存 TTL</label>
                            <select class="form-select" id="browserCacheTtl">
                                <option value="">不修改</option>
                                <option value="0">遵循源站</option>
                                <option value="1800">30 分钟</option>
                                <option value="3600">1 小时</option>
                                <option value="7200">2 小时</option>
                                <option value="14400">4 小时</option>
                                <option value="28800">8 小时</option>
                                <option value="86400">1 天</option>
                                <option value="172800">2 天</option>
                                <option value="604800">7 天</option>
                                <option value="2592000">30 天</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Always Online</label>
                            <select class="form-select" id="alwaysOnline">
                                <option value="">不修改</option>
                                <option value="on">开启</option>
                                <option value="off">关闭</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">开发模式</label>
                            <select class="form-select" id="devMode">
                                <option value="">不修改</option>
                                <option value="on">开启（3小时后自动关闭）</option>
                                <option value="off">关闭</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">每批处理数量</label>
                            <select class="form-select" id="cacheSettingsBatchSize">
                                <option value="5">5 个/批</option>
                                <option value="10" selected>10 个/批</option>
                                <option value="20">20 个/批</option>
                            </select>
                        </div>
                        <button class="btn btn-cf btn-lg" onclick="batchCacheSettings()">
                            <i class="bi bi-check-circle"></i> 批量应用设置
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3" id="cacheResultCard" style="display:none;">
    <div class="card-header d-flex justify-content-between align-items-center">
        执行结果 <span id="cacheProgress" class="badge bg-primary"></span>
    </div>
    <div class="card-body">
        <div class="result-log" id="cacheResultLog"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    CF.renderZoneSelector('cachePurgeZoneSelector');
    CF.renderZoneSelector('cacheSettingsZoneSelector');
    
    document.querySelectorAll('input[name="purgeType"]').forEach(radio => {
        radio.addEventListener('change', () => {
            document.getElementById('purgeFilesInput').style.display = 
                document.getElementById('purgeFiles').checked ? 'block' : 'none';
        });
    });
});

async function batchPurge() {
    const zoneIds = CF.getSelectedZoneIds('cachePurgeZoneSelector');
    if (zoneIds.length === 0) { CF.toast('请选择域名', 'error'); return; }
    
    const batchSize = parseInt(document.getElementById('purgeBatchSize').value);
    CF.showLoading('正在批量清除缓存...');
    showCacheResult();
    
    const result = await CF.batchRequest('/cache/batch', zoneIds, {
        action: 'purge'
    }, batchSize, 'cacheResultLog');
    
    CF.hideLoading();
    finishCache(result);
}

async function batchCacheSettings() {
    const zoneIds = CF.getSelectedZoneIds('cacheSettingsZoneSelector');
    if (zoneIds.length === 0) { CF.toast('请选择域名', 'error'); return; }
    
    const extraData = { action: 'settings' };
    const cacheLevel = document.getElementById('cacheLevel').value;
    const browserTtl = document.getElementById('browserCacheTtl').value;
    const alwaysOnline = document.getElementById('alwaysOnline').value;
    const devMode = document.getElementById('devMode').value;
    
    if (cacheLevel) extraData.cache_level = cacheLevel;
    if (browserTtl) extraData.browser_cache_ttl = browserTtl;
    if (alwaysOnline) extraData.always_online = alwaysOnline;
    if (devMode) extraData.development_mode = devMode;
    
    if (!cacheLevel && !browserTtl && !alwaysOnline && !devMode) {
        CF.toast('请至少选择一项设置', 'error');
        return;
    }
    
    const batchSize = parseInt(document.getElementById('cacheSettingsBatchSize').value);
    CF.showLoading('正在批量设置缓存...');
    showCacheResult();
    
    const result = await CF.batchRequest('/cache/batch', zoneIds, extraData, batchSize, 'cacheResultLog');
    
    CF.hideLoading();
    finishCache(result);
}

function showCacheResult() {
    document.getElementById('cacheResultCard').style.display = 'block';
    document.getElementById('cacheProgress').textContent = '';
    CF.clearLog('cacheResultLog');
}

function finishCache(result) {
    CF.log('cacheResultLog', `\n${result.message}`, 'info');
    document.getElementById('cacheProgress').textContent = result.message;
    CF.toast(result.message, result.summary?.fail > 0 ? 'error' : 'success');
}
</script>
