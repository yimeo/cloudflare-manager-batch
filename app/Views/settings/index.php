<div class="page-header">
    <h2><i class="bi bi-gear"></i> 批量修改设置项</h2>
    <p class="text-muted">CloudFlare 在线批量修改网站设置项，Crawler Hints 等设置</p>
</div>

<div class="row">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">选择域名</div>
            <div class="card-body">
                <div id="settingsZoneSelector"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">设置项</div>
            <div class="card-body">
                <p class="text-muted small mb-3">选择要修改的设置项，留空表示不修改该项</p>
                
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>设置项</th><th>值</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Security Level<br><small class="text-muted">安全级别</small></td>
                                <td>
                                    <select class="form-select form-select-sm setting-item" data-key="security_level">
                                        <option value="">不修改</option>
                                        <option value="off">Off</option>
                                        <option value="essentially_off">Essentially Off</option>
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="under_attack">I'm Under Attack!</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Browser Integrity Check<br><small class="text-muted">浏览器完整性检查</small></td>
                                <td>
                                    <select class="form-select form-select-sm setting-item" data-key="browser_check">
                                        <option value="">不修改</option>
                                        <option value="on">开启</option>
                                        <option value="off">关闭</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Email Obfuscation<br><small class="text-muted">邮箱地址混淆</small></td>
                                <td>
                                    <select class="form-select form-select-sm setting-item" data-key="email_obfuscation">
                                        <option value="">不修改</option>
                                        <option value="on">开启</option>
                                        <option value="off">关闭</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Hotlink Protection<br><small class="text-muted">防盗链</small></td>
                                <td>
                                    <select class="form-select form-select-sm setting-item" data-key="hotlink_protection">
                                        <option value="">不修改</option>
                                        <option value="on">开启</option>
                                        <option value="off">关闭</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>IP Geolocation<br><small class="text-muted">IP 地理定位</small></td>
                                <td>
                                    <select class="form-select form-select-sm setting-item" data-key="ip_geolocation">
                                        <option value="">不修改</option>
                                        <option value="on">开启</option>
                                        <option value="off">关闭</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Server Side Exclude<br><small class="text-muted">服务器端排除</small></td>
                                <td>
                                    <select class="form-select form-select-sm setting-item" data-key="server_side_exclude">
                                        <option value="">不修改</option>
                                        <option value="on">开启</option>
                                        <option value="off">关闭</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Challenge TTL<br><small class="text-muted">质询通过有效期</small></td>
                                <td>
                                    <select class="form-select form-select-sm setting-item" data-key="challenge_ttl">
                                        <option value="">不修改</option>
                                        <option value="300">5 分钟</option>
                                        <option value="900">15 分钟</option>
                                        <option value="1800">30 分钟</option>
                                        <option value="2700">45 分钟</option>
                                        <option value="3600">1 小时</option>
                                        <option value="7200">2 小时</option>
                                        <option value="10800">3 小时</option>
                                        <option value="86400">1 天</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Opportunistic Encryption<br><small class="text-muted">机会加密</small></td>
                                <td>
                                    <select class="form-select form-select-sm setting-item" data-key="opportunistic_encryption">
                                        <option value="">不修改</option>
                                        <option value="on">开启</option>
                                        <option value="off">关闭</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Onion Routing<br><small class="text-muted">Tor 洋葱路由</small></td>
                                <td>
                                    <select class="form-select form-select-sm setting-item" data-key="opportunistic_onion">
                                        <option value="">不修改</option>
                                        <option value="on">开启</option>
                                        <option value="off">关闭</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Pseudo IPv4<br><small class="text-muted">伪 IPv4</small></td>
                                <td>
                                    <select class="form-select form-select-sm setting-item" data-key="pseudo_ipv4">
                                        <option value="">不修改</option>
                                        <option value="off">关闭</option>
                                        <option value="add_header">添加头部</option>
                                        <option value="overwrite_header">覆盖头部</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="mb-3 mt-3">
                    <label class="form-label">每批处理数量</label>
                    <select class="form-select" id="settingsBatchSize">
                        <option value="5">5 个/批</option>
                        <option value="10" selected>10 个/批</option>
                        <option value="20">20 个/批</option>
                    </select>
                </div>
                <button class="btn btn-cf btn-lg" onclick="batchSettings()">
                    <i class="bi bi-check-circle"></i> 批量应用设置
                </button>
            </div>
        </div>
        
        <div class="card mt-3" id="settingsResultCard" style="display:none;">
            <div class="card-header d-flex justify-content-between align-items-center">
                执行结果 <span id="settingsProgress" class="badge bg-primary"></span>
            </div>
            <div class="card-body">
                <div class="result-log" id="settingsResultLog"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    CF.renderZoneSelector('settingsZoneSelector');
});

async function batchSettings() {
    const zoneIds = CF.getSelectedZoneIds('settingsZoneSelector');
    if (zoneIds.length === 0) { CF.toast('请选择域名', 'error'); return; }
    
    // 收集设置项
    const settings = {};
    document.querySelectorAll('.setting-item').forEach(el => {
        if (el.value) {
            const key = el.dataset.key;
            let value = el.value;
            if (!isNaN(value) && value !== 'on' && value !== 'off') {
                value = parseInt(value);
            }
            settings[key] = value;
        }
    });
    
    if (Object.keys(settings).length === 0) {
        CF.toast('请至少选择一项设置', 'error');
        return;
    }
    
    const batchSize = parseInt(document.getElementById('settingsBatchSize').value);
    CF.showLoading('正在批量修改设置...');
    document.getElementById('settingsResultCard').style.display = 'block';
    document.getElementById('settingsProgress').textContent = '';
    CF.clearLog('settingsResultLog');
    
    const result = await CF.batchRequest('/settings/batch', zoneIds, {
        settings: settings
    }, batchSize, 'settingsResultLog');
    
    CF.hideLoading();
    CF.log('settingsResultLog', `\n${result.message}`, 'info');
    document.getElementById('settingsProgress').textContent = result.message;
    CF.toast(result.message, result.summary?.fail > 0 ? 'error' : 'success');
}
</script>
