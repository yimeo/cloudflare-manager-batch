<div class="page-header">
    <h2><i class="bi bi-diagram-3"></i> 域名批量解析</h2>
    <p class="text-muted">批量添加或修改 CloudFlare 中域名的解析值，支持每个域名解析到不同的 IP</p>
</div>

<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#dnsAdd">批量添加记录</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#dnsUpdate">批量修改记录</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#dnsDelete">批量删除记录</a></li>
</ul>

<div class="tab-content">
    <!-- 批量添加 -->
    <div class="tab-pane fade show active" id="dnsAdd">
        <div class="row">
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">选择域名</div>
                    <div class="card-body"><div id="dnsAddZoneSelector"></div></div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">解析设置</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">记录类型</label>
                                <select class="form-select" id="dnsAddType">
                                    <option value="A">A</option><option value="AAAA">AAAA</option>
                                    <option value="CNAME">CNAME</option><option value="MX">MX</option>
                                    <option value="TXT">TXT</option><option value="NS">NS</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">名称</label>
                                <input type="text" class="form-control" id="dnsAddName" value="@" placeholder="@ 或子域名">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">TTL</label>
                                <select class="form-select" id="dnsAddTtl">
                                    <option value="1">自动</option><option value="300">5 分钟</option>
                                    <option value="3600">1 小时</option><option value="86400">1 天</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">解析值</label>
                            <textarea class="form-control" id="dnsAddContent" rows="5" placeholder="统一解析到同一 IP:&#10;1.2.3.4&#10;&#10;或每个域名不同 IP（格式: 域名:IP）:&#10;example.com:1.1.1.1&#10;example.net:2.2.2.2"></textarea>
                        </div>
                        <div class="mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="dnsAddProxied">
                                <label class="form-check-label" for="dnsAddProxied">开启代理（CDN 加速）</label>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-cf" onclick="batchAddDns()"><i class="bi bi-plus-circle"></i> 批量添加解析</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 批量修改 -->
    <div class="tab-pane fade" id="dnsUpdate">
        <div class="row">
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">选择域名</div>
                    <div class="card-body"><div id="dnsUpdateZoneSelector"></div></div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">修改设置</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">记录类型</label>
                                <select class="form-select" id="dnsUpdateType">
                                    <option value="A">A</option><option value="AAAA">AAAA</option><option value="CNAME">CNAME</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">记录名称</label>
                                <input type="text" class="form-control" id="dnsUpdateName" value="@">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">新解析值</label>
                                <input type="text" class="form-control" id="dnsUpdateContent" placeholder="新的 IP 或值">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-cf" onclick="batchUpdateDns()"><i class="bi bi-pencil"></i> 批量修改</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 批量删除 -->
    <div class="tab-pane fade" id="dnsDelete">
        <div class="row">
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">选择域名</div>
                    <div class="card-body"><div id="dnsDeleteZoneSelector"></div></div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">删除设置</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">记录类型（留空删除所有类型）</label>
                                <select class="form-select" id="dnsDeleteType">
                                    <option value="">所有类型</option><option value="A">A</option>
                                    <option value="AAAA">AAAA</option><option value="CNAME">CNAME</option>
                                    <option value="MX">MX</option><option value="TXT">TXT</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">记录名称（留空匹配所有）</label>
                                <input type="text" class="form-control" id="dnsDeleteName" placeholder="留空匹配所有">
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="dnsClearAll">
                                <label class="form-check-label text-danger" for="dnsClearAll"><strong>清空所有解析记录</strong></label>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-danger" onclick="batchDeleteDns()"><i class="bi bi-trash"></i> 批量删除</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3" id="dnsResultCard" style="display:none;">
    <div class="card-header d-flex justify-content-between align-items-center">
        执行结果 <span id="dnsProgress" class="badge bg-primary"></span>
    </div>
    <div class="card-body"><div class="result-log" id="dnsResultLog"></div></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    CF.renderZoneSelector('dnsAddZoneSelector');
    CF.renderZoneSelector('dnsUpdateZoneSelector');
    CF.renderZoneSelector('dnsDeleteZoneSelector');
});

async function batchAddDns() {
    const zoneIds = CF.getSelectedZoneIds('dnsAddZoneSelector');
    if (zoneIds.length === 0) { CF.toast('请选择域名', 'error'); return; }
    CF.showLoading('正在批量添加解析记录...');
    showDnsResult();
    const result = await CF.batchRequest('/dns/add', zoneIds, {
        record_type: document.getElementById('dnsAddType').value,
        record_name: document.getElementById('dnsAddName').value,
        contents: document.getElementById('dnsAddContent').value,
        ttl: document.getElementById('dnsAddTtl').value,
        proxied: document.getElementById('dnsAddProxied').checked
    }, 10, 'dnsResultLog');
    CF.hideLoading();
    finishDns(result);
}

async function batchUpdateDns() {
    const zoneIds = CF.getSelectedZoneIds('dnsUpdateZoneSelector');
    if (zoneIds.length === 0) { CF.toast('请选择域名', 'error'); return; }
    CF.showLoading('正在批量修改解析记录...');
    showDnsResult();
    const result = await CF.batchRequest('/dns/update', zoneIds, {
        record_type: document.getElementById('dnsUpdateType').value,
        record_name: document.getElementById('dnsUpdateName').value,
        content: document.getElementById('dnsUpdateContent').value
    }, 10, 'dnsResultLog');
    CF.hideLoading();
    finishDns(result);
}

async function batchDeleteDns() {
    const zoneIds = CF.getSelectedZoneIds('dnsDeleteZoneSelector');
    if (zoneIds.length === 0) { CF.toast('请选择域名', 'error'); return; }
    if (!confirm('确定要删除选中域名的解析记录吗？')) return;
    CF.showLoading('正在批量删除解析记录...');
    showDnsResult();
    const result = await CF.batchRequest('/dns/delete', zoneIds, {
        record_type: document.getElementById('dnsDeleteType').value,
        record_name: document.getElementById('dnsDeleteName').value,
        clear_all: document.getElementById('dnsClearAll').checked
    }, 10, 'dnsResultLog');
    CF.hideLoading();
    finishDns(result);
}

function showDnsResult() {
    document.getElementById('dnsResultCard').style.display = 'block';
    document.getElementById('dnsProgress').textContent = '';
    CF.clearLog('dnsResultLog');
}
function finishDns(result) {
    CF.log('dnsResultLog', `\n${result.message}`, 'info');
    document.getElementById('dnsProgress').textContent = result.message;
    CF.toast(result.message, result.summary?.fail > 0 ? 'error' : 'success');
}
</script>
