<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CloudFlare 批量管理工具</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --cf-orange: #f6821f;
            --cf-dark: #1b1b1b;
            --cf-gray: #f4f4f4;
        }
        body { background: var(--cf-gray); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .sidebar {
            position: fixed; top: 0; left: 0; width: 260px; height: 100vh;
            background: var(--cf-dark); color: #fff; overflow-y: auto; z-index: 1000;
            transition: transform 0.3s;
        }
        .sidebar .logo { padding: 20px; font-size: 18px; font-weight: bold; border-bottom: 1px solid #333; }
        .sidebar .logo span { color: var(--cf-orange); }
        .sidebar .nav-link {
            color: #ccc; padding: 12px 20px; display: flex; align-items: center; gap: 10px;
            text-decoration: none; border-left: 3px solid transparent; transition: all 0.2s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff; background: rgba(246,130,31,0.1); border-left-color: var(--cf-orange);
        }
        .sidebar .nav-link i { font-size: 18px; width: 24px; text-align: center; }
        .main-content { margin-left: 260px; padding: 30px; min-height: 100vh; }
        .page-header { margin-bottom: 30px; }
        .page-header h2 { font-size: 24px; font-weight: 600; color: #333; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .card-header { background: #fff; border-bottom: 1px solid #eee; font-weight: 600; border-radius: 12px 12px 0 0 !important; }
        .btn-cf { background: var(--cf-orange); color: #fff; border: none; }
        .btn-cf:hover { background: #e5741a; color: #fff; }
        .btn-cf-outline { border: 1px solid var(--cf-orange); color: var(--cf-orange); background: transparent; }
        .btn-cf-outline:hover { background: var(--cf-orange); color: #fff; }
        .zone-selector { max-height: 300px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px; padding: 10px; }
        .zone-item { padding: 6px 10px; border-radius: 4px; cursor: pointer; display: flex; align-items: center; gap: 8px; }
        .zone-item:hover { background: #f0f0f0; }
        .result-log { max-height: 400px; overflow-y: auto; background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 13px; }
        .result-log .success { color: #4ec9b0; }
        .result-log .error { color: #f44747; }
        .result-log .info { color: #569cd6; }
        .badge-ai { background: #e74c3c; font-size: 11px; }
        .badge-search { background: #3498db; font-size: 11px; }
        .badge-assistant { background: #9b59b6; font-size: 11px; }
        .badge-scraper { background: #95a5a6; font-size: 11px; }
        .loading-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); display: none; justify-content: center;
            align-items: center; z-index: 9999;
        }
        .loading-overlay.show { display: flex; }
        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 10000; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }
        .nav-section { padding: 10px 20px 5px; font-size: 11px; text-transform: uppercase; color: #666; letter-spacing: 1px; }
    </style>
</head>
<body>
    <!-- 侧边栏 -->
    <nav class="sidebar" id="sidebar">
        <div class="logo">
            <span>CF</span> 批量管理工具
        </div>
        <div class="nav-section">域名管理</div>
        <a href="/zones" class="nav-link <?= (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/zones') ? 'active' : '' ?>">
            <i class="bi bi-globe"></i> 批量添加域名
        </a>
        <a href="/dns" class="nav-link <?= (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/dns') ? 'active' : '' ?>">
            <i class="bi bi-diagram-3"></i> 域名批量解析
        </a>
        <a href="/proxy" class="nav-link <?= (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/proxy') ? 'active' : '' ?>">
            <i class="bi bi-shield-check"></i> 批量开关代理
        </a>
        
        <div class="nav-section">安全与加密</div>
        <a href="/ssl" class="nav-link <?= (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/ssl') ? 'active' : '' ?>">
            <i class="bi bi-lock"></i> HTTPS/SSL 设置
        </a>
        <a href="/ai-crawlers" class="nav-link <?= (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/ai-crawlers') ? 'active' : '' ?>">
            <i class="bi bi-robot"></i> AI 爬虫管理
            <span class="badge bg-danger ms-auto">NEW</span>
        </a>
        
        <div class="nav-section">性能优化</div>
        <a href="/cache" class="nav-link <?= (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/cache') ? 'active' : '' ?>">
            <i class="bi bi-hdd-stack"></i> 缓存管理
        </a>
        <a href="/optimize" class="nav-link <?= (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/optimize') ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> 代码压缩/优化
        </a>
        
        <div class="nav-section">规则与配置</div>
        <a href="/rules" class="nav-link <?= (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/rules') ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-code"></i> 规则管理
        </a>
        <a href="/settings" class="nav-link <?= (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/settings') ? 'active' : '' ?>">
            <i class="bi bi-gear"></i> 批量修改设置
        </a>
        <a href="/export" class="nav-link <?= (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/export') ? 'active' : '' ?>">
            <i class="bi bi-download"></i> 批量导出域名
        </a>
        
        <div class="nav-section">账户</div>
        <a href="/auth/logout" class="nav-link">
            <i class="bi bi-box-arrow-right"></i> 退出登录
        </a>
    </nav>

    <!-- 主内容区 -->
    <div class="main-content">
        <!-- 移动端菜单按钮 -->
        <button class="btn btn-sm btn-outline-secondary d-md-none mb-3" onclick="document.getElementById('sidebar').classList.toggle('show')">
            <i class="bi bi-list"></i> 菜单
        </button>
        
        <?= $content ?>
    </div>

    <!-- 加载遮罩 -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="text-center text-white">
            <div class="spinner-border mb-3" role="status"></div>
            <div id="loadingText">正在处理中...</div>
        </div>
    </div>

    <!-- Toast 通知 -->
    <div class="toast-container" id="toastContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // 全局工具函数
    const CF = {
        // 显示加载
        showLoading(text = '正在处理中...') {
            document.getElementById('loadingText').textContent = text;
            document.getElementById('loadingOverlay').classList.add('show');
        },
        
        // 隐藏加载
        hideLoading() {
            document.getElementById('loadingOverlay').classList.remove('show');
        },
        
        // Toast 通知
        toast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const id = 'toast_' + Date.now();
            const bgClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-info';
            
            container.innerHTML += `
                <div id="${id}" class="toast show ${bgClass} text-white" role="alert">
                    <div class="toast-body d-flex justify-content-between align-items-center">
                        ${message}
                        <button type="button" class="btn-close btn-close-white ms-2" onclick="this.parentElement.parentElement.remove()"></button>
                    </div>
                </div>
            `;
            
            setTimeout(() => {
                const el = document.getElementById(id);
                if (el) el.remove();
            }, 5000);
        },
        
        // API 请求
        async request(url, data = {}) {
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                return await response.json();
            } catch (error) {
                return { success: false, message: error.message };
            }
        },
        
        // 加载 Zone 列表
        async loadZones() {
            const result = await this.request('/zones/list', {});
            if (result.success) {
                return result.result || [];
            }
            return [];
        },
        
        // 渲染 Zone 选择器
        async renderZoneSelector(containerId, options = {}) {
            const container = document.getElementById(containerId);
            if (!container) return;
            
            const viewMode = options.viewMode === true;
            const showCheckbox = options.showCheckbox === true;
            
            container.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div> 加载中...</div>';
            
            const zones = await this.loadZones();
            
            if (zones.length === 0) {
                container.innerHTML = '<div class="text-muted text-center py-3">暂无域名</div>';
                return;
            }
            
            let html = `
                <div class="mb-2 d-flex flex-wrap align-items-center gap-2">
                    <input type="text" class="form-control form-control-sm" style="width: 200px;" placeholder="搜索域名..." oninput="CF.filterZones(this, '${containerId}')">
            `;
            
            if (!viewMode || showCheckbox) {
                html += `
                    <button class="btn btn-sm btn-outline-secondary" onclick="CF.selectAllZones('${containerId}')">全选</button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="CF.deselectAllZones('${containerId}')">取消</button>
                `;
            }
            
            if (viewMode) {
                // 查看模式：添加"只显示有规则域名"的筛选框
                html += `
                    <label class="form-check form-check-inline mb-0 ms-2">
                        <input type="checkbox" class="form-check-input" id="${containerId}_filter_rules" onchange="CF.toggleRuleFilter('${containerId}')">
                        <span class="form-check-label">只显示有规则域名</span>
                    </label>
                `;
            }
            
            html += `
                </div>
                <div class="zone-selector" id="${containerId}_list">
            `;
            
            zones.forEach(zone => {
                if (viewMode && !showCheckbox) {
                    html += `
                        <div class="zone-item" data-zone-id="${zone.id}" style="padding: 0.75rem; border-bottom: 1px solid #e9ecef; display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span class="zone-name-text">${zone.name}</span>
                                <span class="zone-action-placeholder" id="action_${containerId}_${zone.id}"></span>
                            </div>
                            <small class="text-muted">${zone.status}</small>
                        </div>
                    `;
                } else {
                    html += `
                        <label class="zone-item" style="${viewMode ? 'padding: 0.75rem; border-bottom: 1px solid #e9ecef;' : ''}">
                            <input type="checkbox" class="form-check-input zone-checkbox" value="${zone.id}" data-name="${zone.name}">
                            <span class="zone-name-text">${zone.name}</span>
                            <span class="zone-action-placeholder" id="action_${containerId}_${zone.id}"></span>
                            <small class="text-muted ms-auto">${zone.status}</small>
                        </label>
                    `;
                }
            });
            
            html += '</div>';
            
            if (!viewMode) {
                html += `<div class="mt-2 text-muted small">共 ${zones.length} 个域名，已选 <span id="${containerId}_count">0</span> 个</div>`;
            } else {
                html += `<div class="mt-2 text-muted small">共 ${zones.length} 个域名，其中有规则域名 <span id="${containerId}_rules_count">0</span> 个${showCheckbox ? '，已选 <span id="' + containerId + '_count">0</span> 个' : ''}</div>`;
            }
            
            container.innerHTML = html;
            
            if (!viewMode || showCheckbox) {
                container.querySelectorAll('.zone-checkbox').forEach(cb => {
                    cb.addEventListener('change', () => {
                        const count = container.querySelectorAll('.zone-checkbox:checked').length;
                        const countEl = document.getElementById(`${containerId}_count`);
                        if (countEl) countEl.textContent = count;
                    });
                });
            }
            
            // 如果是查看模式，加载规则数量
            if (viewMode) {
                this.loadRuleCountsForView(containerId, zones);
            }
        },
        
        // 为查看规则页面加载规则数量（优化版 - 串行加载）
        async loadRuleCountsForView(containerId, zones) {
            // 串行加载每个域名的规则数量，避免并发触发 API 速率限制
            for (const zone of zones) {
                try {
                    const result = await this.request('/rules/count', { zone_id: zone.id });
                    if (result.success) {
                        const ruleCount = result.rule_count || 0;
                        // 只有有规则的域名才显示链接和徽章
                        if (ruleCount > 0) {
                            const placeholder = document.getElementById(`action_${containerId}_${zone.id}`);
                            if (placeholder) {
                                placeholder.innerHTML = `<a href="javascript:void(0)" class="small text-decoration-none" onclick="event.preventDefault(); event.stopPropagation(); CF.viewZoneRules('${zone.id}', '${zone.name}')"><i class="bi bi-eye"></i> 查看规则</a><span class="badge bg-info ms-2">${ruleCount}</span>`;
                            }
                        }
                    } else {
                        console.warn(`加载域名 ${zone.name} 的规则数量失败:`, result.message);
                    }
                } catch (e) {
                    console.error(`加载域名 ${zone.name} 的规则数量出错:`, e);
                }
                
                // 每个请求间隔 200ms，避免速率限制
                await new Promise(resolve => setTimeout(resolve, 200));
            }
            
            // 加载完成后更新统计
            this.updateRuleStats(containerId);
        },
        
        // 切换"只显示有规则域名"筛选
        toggleRuleFilter(containerId) {
            const filterCheckbox = document.getElementById(`${containerId}_filter_rules`);
            const zoneItems = document.querySelectorAll(`#${containerId}_list .zone-item`);
            
            zoneItems.forEach(item => {
                const placeholder = item.querySelector('.zone-action-placeholder');
                const hasRules = placeholder && placeholder.innerHTML.trim() !== '';
                
                if (filterCheckbox.checked) {
                    // 只显示有规则的域名
                    item.style.display = hasRules ? '' : 'none';
                } else {
                    // 显示所有域名
                    item.style.display = '';
                }
            });
        },
        
        // 更新有规则域名的统计数量
        updateRuleStats(containerId) {
            const zoneItems = document.querySelectorAll(`#${containerId}_list .zone-item`);
            let rulesCount = 0;
            
            zoneItems.forEach(item => {
                const placeholder = item.querySelector('.zone-action-placeholder');
                if (placeholder && placeholder.innerHTML.trim() !== '') {
                    rulesCount++;
                }
            });
            
            const statsElement = document.getElementById(`${containerId}_rules_count`);
            if (statsElement) {
                statsElement.textContent = rulesCount;
            }
        },
        
        // 查看域名规则详情
        async viewZoneRules(zoneId, zoneName) {
            if (typeof window.viewRulesDetail === 'function') {
                window.viewRulesDetail(zoneId, zoneName);
            } else {
                this.toast('查看规则功能未初始化', 'error');
            }
        },
        
        // 获取选中的 Zone IDs
        getSelectedZoneIds(containerId) {
            const container = document.getElementById(containerId);
            if (!container) return [];
            return Array.from(container.querySelectorAll('.zone-checkbox:checked')).map(cb => cb.value);
        },
        
        // 获取选中的域名
        getSelectedDomains(containerId) {
            const container = document.getElementById(containerId);
            if (!container) return [];
            return Array.from(container.querySelectorAll('.zone-checkbox:checked')).map(cb => cb.dataset.name);
        },
        
        // 搜索过滤
        filterZones(input, containerId) {
            const keyword = input.value.toLowerCase();
            const items = document.querySelectorAll(`#${containerId}_list .zone-item`);
            items.forEach(item => {
                const name = item.querySelector('span').textContent.toLowerCase();
                item.style.display = name.includes(keyword) ? '' : 'none';
            });
        },
        
        // 全选
        selectAllZones(containerId) {
            const container = document.getElementById(containerId);
            container.querySelectorAll('.zone-checkbox').forEach(cb => {
                if (cb.closest('.zone-item').style.display !== 'none') {
                    cb.checked = true;
                }
            });
            const count = container.querySelectorAll('.zone-checkbox:checked').length;
            document.getElementById(`${containerId}_count`).textContent = count;
        },
        
        // 取消全选
        deselectAllZones(containerId) {
            const container = document.getElementById(containerId);
            container.querySelectorAll('.zone-checkbox').forEach(cb => cb.checked = false);
            document.getElementById(`${containerId}_count`).textContent = '0';
        },
        
        // 输出日志
        log(containerId, message, type = 'info') {
            const container = document.getElementById(containerId);
            if (!container) return;
            const time = new Date().toLocaleTimeString();
            container.innerHTML += `<div class="${type}">[${time}] ${message}</div>`;
            container.scrollTop = container.scrollHeight;
        },
        
        // 清空日志
        clearLog(containerId) {
            const container = document.getElementById(containerId);
            if (container) container.innerHTML = '';
        },
        
        // 分批请求（核心：解决大量域名超时问题）
        // url: 接口地址, allZoneIds: 全部zone_id数组, extraData: 额外参数, batchSize: 每批数量, logId: 日志容器ID
        async batchRequest(url, allZoneIds, extraData = {}, batchSize = 10, logId = '') {
            const totalCount = allZoneIds.length;
            let successCount = 0;
            let failCount = 0;
            let allResults = [];
            
            // 分批
            const batches = [];
            for (let i = 0; i < totalCount; i += batchSize) {
                batches.push(allZoneIds.slice(i, i + batchSize));
            }
            
            if (logId) {
                CF.log(logId, `共 ${totalCount} 个域名，分 ${batches.length} 批处理（每批 ${batchSize} 个）`, 'info');
            }
            
            for (let i = 0; i < batches.length; i++) {
                const batch = batches[i];
                const batchNum = i + 1;
                
                // 更新加载提示
                document.getElementById('loadingText').textContent = `正在处理第 ${batchNum}/${batches.length} 批（已完成 ${successCount + failCount}/${totalCount}）`;
                
                const data = Object.assign({}, extraData, { zone_ids: batch });
                const result = await this.request(url, data);
                
                if (result.data) {
                    result.data.forEach(item => {
                        if (item.success) successCount++;
                        else failCount++;
                        allResults.push(item);
                        if (logId) {
                            CF.log(logId, `${item.success ? '✓' : '✗'} ${item.zone_id || ''} - ${item.message || ''}`, item.success ? 'success' : 'error');
                        }
                    });
                } else if (!result.success) {
                    // 整批失败
                    failCount += batch.length;
                    if (logId) {
                        CF.log(logId, `✗ 第 ${batchNum} 批失败: ${result.message || '未知错误'}`, 'error');
                    }
                }
                
                // 批次间等待 500ms 避免频率限制
                if (i < batches.length - 1) {
                    await new Promise(r => setTimeout(r, 500));
                }
            }
            
            return {
                success: true,
                message: `全部完成: 成功 ${successCount} 个, 失败 ${failCount} 个, 共 ${totalCount} 个`,
                data: allResults,
                summary: { success: successCount, fail: failCount, total: totalCount }
            };
        }
    };
    </script>
</body>
</html>
