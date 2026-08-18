<div class="page-header">
    <h2><i class="bi bi-file-earmark-code"></i> 批量复制/删除规则</h2>
    <p class="text-muted">Configuration Rules、转换规则、重写URL、修改请求头/响应头、重定向规则、Origin Rules、Cache Rules、页面规则、WAF 自定义规则</p>
</div>

<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#rulesCopy">复制规则</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#rulesDelete">删除规则</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#rulesView">查看规则</a></li>
</ul>

<!-- 规则详情 Modal -->
<div class="modal fade" id="rulesDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-shield-lock"></i> <span id="rulesDetailTitle">域名规则详情</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="rulesDetailContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary"></div>
                        <div class="mt-2">正在获取规则列表...</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
            </div>
        </div>
    </div>
</div>

<div class="tab-content">
    <!-- 复制规则 -->
    <div class="tab-pane fade show active" id="rulesCopy">
        <div class="card mb-3">
            <div class="card-header">规则类型与源域名</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">规则类型</label>
                        <select class="form-select" id="copyRuleType">
                            <option value="waf_custom">WAF 自定义规则</option>
                            <option value="page_rules">页面规则 (Page Rules)</option>
                            <option value="redirect">重定向规则</option>
                            <option value="transform_request_header">转换规则 - 修改请求头</option>
                            <option value="transform_response_header">转换规则 - 修改响应头</option>
                            <option value="rewrite_url">转换规则 - 重写 URL</option>
                            <option value="origin">Origin Rules</option>
                            <option value="cache">Cache Rules</option>
                            <option value="config">Configuration Rules</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">源域名（从此域名复制规则）</label>
                        <select class="form-select" id="sourceZone">
                            <option value="">加载中...</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">目标域名（将规则复制到这些域名）</div>
            <div class="card-body">
                <div id="copyTargetZoneSelectorRules"></div>
                <div class="mt-3">
                    <button class="btn btn-cf btn-lg" onclick="copyRules()">
                        <i class="bi bi-files"></i> 批量复制规则
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 删除规则 -->
    <div class="tab-pane fade" id="rulesDelete">
        <div class="row">
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">选择域名</div>
                    <div class="card-body">
                        <div id="deleteRulesZoneSelectorRules"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">删除设置</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">要删除的规则类型</label>
                            <select class="form-select" id="deleteRuleType">
                                <option value="waf_custom">WAF 自定义规则</option>
                                <option value="page_rules">页面规则 (Page Rules)</option>
                                <option value="redirect">重定向规则</option>
                                <option value="transform_request_header">转换规则 - 修改请求头</option>
                                <option value="transform_response_header">转换规则 - 修改响应头</option>
                                <option value="rewrite_url">转换规则 - 重写 URL</option>
                                <option value="origin">Origin Rules</option>
                                <option value="cache">Cache Rules</option>
                                <option value="config">Configuration Rules</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">每批处理数量</label>
                            <select class="form-select" id="deleteBatchSize">
                                <option value="5">5 个/批（稳定）</option>
                                <option value="10" selected>10 个/批（推荐）</option>
                                <option value="20">20 个/批（较快）</option>
                            </select>
                        </div>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> 此操作将清空选中域名的所有该类型规则，不可恢复！
                        </div>
                        <button class="btn btn-danger btn-lg" onclick="deleteRules()">
                            <i class="bi bi-trash"></i> 批量删除规则
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
	    <!-- 查看规则 -->
	    <div class="tab-pane fade" id="rulesView">
	        <div class="card">
	            <div class="card-header d-flex justify-content-between align-items-center">
	                选择域名查看规则
	                <div>
	                    <button class="btn btn-sm btn-outline-primary" onclick="selectAllWithRules()">全选有规则域名</button>
	                </div>
	            </div>
	            <div class="card-body">
	                <div id="viewRulesZoneSelector"></div>
	                
	                <div class="mt-4 p-3 border rounded bg-light" id="batchActionArea" style="display:none;">
	                    <h5><i class="bi bi-lightning-charge"></i> 批量操作已选域名</h5>
	                    <div class="row g-3 align-items-end">
	                        <div class="col-md-3">
	                            <label class="form-label small">操作类型</label>
	                            <select class="form-select form-select-sm" id="batchActionType" onchange="toggleBatchActionFields()">
	                                <option value="copy">批量复制规则到这些域名</option>
	                                <option value="delete">批量删除这些域名的规则</option>
	                            </select>
	                        </div>
	                        <div class="col-md-3" id="batchSourceZoneCol">
	                            <label class="form-label small">源域名</label>
	                            <select class="form-select form-select-sm" id="batchSourceZone">
	                                <option value="">加载中...</option>
	                            </select>
	                        </div>
	                        <div class="col-md-3">
	                            <label class="form-label small">规则类型</label>
	                            <select class="form-select form-select-sm" id="batchRuleType">
	                                <option value="waf_custom">WAF 自定义规则</option>
	                                <option value="page_rules">页面规则 (Page Rules)</option>
	                                <option value="redirect">重定向规则</option>
	                                <option value="transform_request_header">转换规则 - 修改请求头</option>
	                                <option value="transform_response_header">转换规则 - 修改响应头</option>
	                                <option value="rewrite_url">转换规则 - 重写 URL</option>
	                                <option value="origin">Origin Rules</option>
	                                <option value="cache">Cache Rules</option>
	                                <option value="config">Configuration Rules</option>
	                            </select>
	                        </div>
	                        <div class="col-md-3">
	                            <button class="btn btn-primary btn-sm w-100" onclick="executeBatchAction()">
	                                <i class="bi bi-play-fill"></i> 执行批量操作
	                            </button>
	                        </div>
	                    </div>
	                </div>
	            </div>
	        </div>
	    </div>
</div>

<div class="card mt-3" id="rulesResultCard" style="display:none;">
    <div class="card-header d-flex justify-content-between align-items-center">
        执行结果
        <span id="rulesProgress" class="badge bg-primary"></span>
    </div>
    <div class="card-body">
        <div class="result-log" id="rulesResultLog"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    // 复制规则页面的域名选择器
    CF.renderZoneSelector('copyTargetZoneSelectorRules', { showRuleCount: false });
    // 删除规则页面的域名选择器
    CF.renderZoneSelector('deleteRulesZoneSelectorRules', { showRuleCount: false });
    
    // 加载源域名选择器
    const zones = await CF.loadZones();
    const select = document.getElementById('sourceZone');
    select.innerHTML = '<option value="">请选择源域名</option>';
    zones.forEach(zone => {
        select.innerHTML += `<option value="${zone.id}">${zone.name}</option>`;
    });
    
	    // 监听标签页切换，当切换到查看规则时加载
	    const viewRulesTab = document.querySelector('a[href="#rulesView"]');
	    if (viewRulesTab) {
	        viewRulesTab.addEventListener('shown.bs.tab', async () => {
	            const container = document.getElementById('viewRulesZoneSelector');
	            if (!container.innerHTML || container.innerHTML.trim() === '' || container.innerHTML.includes('加载中')) {
	                await CF.renderZoneSelector('viewRulesZoneSelector', { viewMode: true, showCheckbox: true });
	                
	                // 初始化批量操作区域的源域名下拉框
	                const batchSelect = document.getElementById('batchSourceZone');
	                batchSelect.innerHTML = '<option value="">请选择源域名</option>';
	                zones.forEach(zone => {
	                    batchSelect.innerHTML += `<option value="${zone.id}">${zone.name}</option>`;
	                });
	                
	                // 监听选中变化显示批量操作区域
	                container.querySelectorAll('.zone-checkbox').forEach(cb => {
	                    cb.addEventListener('change', () => {
	                        const selectedCount = container.querySelectorAll('.zone-checkbox:checked').length;
	                        document.getElementById('batchActionArea').style.display = selectedCount > 0 ? 'block' : 'none';
	                    });
	                });
	            }
	        });
	    }
	});

	function selectAllWithRules() {
	    const container = document.getElementById('viewRulesZoneSelector');
	    const items = container.querySelectorAll('.zone-item');
	    let count = 0;
	    items.forEach(item => {
	        const placeholder = item.querySelector('.zone-action-placeholder');
	        const hasRules = placeholder && placeholder.innerHTML.trim() !== '';
	        const checkbox = item.querySelector('.zone-checkbox');
	        if (hasRules && checkbox) {
	            checkbox.checked = true;
	            count++;
	        }
	    });
	    
	    const countEl = document.getElementById('viewRulesZoneSelector_count');
	    if (countEl) countEl.textContent = container.querySelectorAll('.zone-checkbox:checked').length;
	    document.getElementById('batchActionArea').style.display = count > 0 ? 'block' : 'none';
	    CF.toast(`已选中 ${count} 个有规则的域名`, 'info');
	}

	function toggleBatchActionFields() {
	    const type = document.getElementById('batchActionType').value;
	    document.getElementById('batchSourceZoneCol').style.display = type === 'copy' ? 'block' : 'none';
	}

	async function executeBatchAction() {
	    const actionType = document.getElementById('batchActionType').value;
	    const zoneIds = CF.getSelectedZoneIds('viewRulesZoneSelector');
	    const ruleType = document.getElementById('batchRuleType').value;
	    
	    if (zoneIds.length === 0) {
	        CF.toast('请先选择域名', 'error');
	        return;
	    }
	    
	    if (actionType === 'copy') {
	        const sourceZoneId = document.getElementById('batchSourceZone').value;
	        if (!sourceZoneId) {
	            CF.toast('请选择源域名', 'error');
	            return;
	        }
	        
	        CF.showLoading('正在批量复制规则...');
	        showRulesResult();
	        
	        const result = await CF.batchRequest('/rules/copy', zoneIds, {
	            source_zone_id: sourceZoneId,
	            rule_type: ruleType
	        }, 10, 'rulesResultLog');
	        
	        CF.hideLoading();
	        CF.log('rulesResultLog', `\n${result.message}`, 'info');
	        document.getElementById('rulesProgress').textContent = result.message;
	        CF.toast(result.message, result.summary.fail > 0 ? 'error' : 'success');
	    } else if (actionType === 'delete') {
	        if (!confirm(`确定要删除选中 ${zoneIds.length} 个域名的 [${ruleType}] 规则吗？此操作不可恢复！`)) return;
	        
	        CF.showLoading('正在批量删除规则...');
	        showRulesResult();
	        
	        const result = await CF.batchRequest('/rules/delete', zoneIds, {
	            rule_type: ruleType
	        }, 10, 'rulesResultLog');
	        
	        CF.hideLoading();
	        CF.log('rulesResultLog', `\n${result.message}`, 'info');
	        document.getElementById('rulesProgress').textContent = result.message;
	        CF.toast(result.message, result.summary.fail > 0 ? 'error' : 'success');
	    }
	}

async function copyRules() {
    const sourceZoneId = document.getElementById('sourceZone').value;
    if (!sourceZoneId) { CF.toast('请选择源域名', 'error'); return; }
    
    const targetZoneIds = CF.getSelectedZoneIds('copyTargetZoneSelectorRules');
    if (targetZoneIds.length === 0) { CF.toast('请选择目标域名', 'error'); return; }
    
    const ruleType = document.getElementById('copyRuleType').value;
    
    CF.showLoading('正在批量复制规则...');
    showRulesResult();
    
    // 使用分批请求
    const result = await CF.batchRequest('/rules/copy', targetZoneIds, {
        source_zone_id: sourceZoneId,
        rule_type: ruleType
    }, 10, 'rulesResultLog');
    
    CF.hideLoading();
    CF.log('rulesResultLog', `\n${result.message}`, 'info');
    document.getElementById('rulesProgress').textContent = result.message;
    CF.toast(result.message, result.summary.fail > 0 ? 'error' : 'success');
}

async function deleteRules() {
    const zoneIds = CF.getSelectedZoneIds('deleteRulesZoneSelectorRules');
    if (zoneIds.length === 0) { CF.toast('请选择域名', 'error'); return; }
    if (!confirm(`确定要删除选中 ${zoneIds.length} 个域名的规则吗？此操作不可恢复！`)) return;
    
    const ruleType = document.getElementById('deleteRuleType').value;
    const batchSize = parseInt(document.getElementById('deleteBatchSize').value);
    
    CF.showLoading('正在批量删除规则...');
    showRulesResult();
    
    // 使用分批请求
    const result = await CF.batchRequest('/rules/delete', zoneIds, {
        rule_type: ruleType
    }, batchSize, 'rulesResultLog');
    
    CF.hideLoading();
    CF.log('rulesResultLog', `\n${result.message}`, 'info');
    document.getElementById('rulesProgress').textContent = result.message;
    CF.toast(result.message, result.summary.fail > 0 ? 'error' : 'success');
}

function showRulesResult() {
    document.getElementById('rulesResultCard').style.display = 'block';
    document.getElementById('rulesProgress').textContent = '';
    CF.clearLog('rulesResultLog');
}

// 全局函数：查看域名规则详情
async function viewRulesDetail(zoneId, zoneName) {
    const modal = new bootstrap.Modal(document.getElementById('rulesDetailModal'));
    document.getElementById('rulesDetailTitle').textContent = `[${zoneName}] 规则详情`;
    document.getElementById('rulesDetailContent').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary"></div>
            <div class="mt-2">正在获取规则列表...</div>
        </div>
    `;
    modal.show();
    
    try {
        const result = await CF.request('/rules/get-all', { zone_id: zoneId });
        if (result.success) {
            if (result.rules.length === 0) {
                document.getElementById('rulesDetailContent').innerHTML = '<div class="alert alert-info">该域名暂无任何规则。</div>';
                return;
            }
            
            let html = `
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>规则类型</th>
                                <th>描述/匹配条件</th>
                                <th>动作</th>
                                <th>状态</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            result.rules.forEach(rule => {
                const statusClass = rule.status === 'active' ? 'bg-success' : 'bg-secondary';
                html += `
                    <tr>
                        <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle">${rule.type}</span></td>
                        <td class="small text-break">${rule.description}</td>
                        <td><code>${rule.action}</code></td>
                        <td><span class="badge ${statusClass}">${rule.status}</span></td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            document.getElementById('rulesDetailContent').innerHTML = html;
        } else {
            document.getElementById('rulesDetailContent').innerHTML = `<div class="alert alert-danger">加载失败: ${result.message}</div>`;
        }
    } catch (error) {
        document.getElementById('rulesDetailContent').innerHTML = `<div class="alert alert-danger">请求出错: ${error.message}</div>`;
    }
}
</script>
