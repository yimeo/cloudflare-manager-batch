<div class="page-header">
    <h2><i class="bi bi-robot"></i> AI 爬虫管理 <span class="badge bg-danger">NEW</span></h2>
    <p class="text-muted">管理所有与拦截 AI 爬虫和自动化训练程序相关的安全选项，支持批量和单独设置</p>
</div>

<!-- 快捷操作 -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-danger h-100">
            <div class="card-body text-center">
                <i class="bi bi-shield-x text-danger" style="font-size: 32px;"></i>
                <h6 class="mt-2">一键拦截所有 AI 爬虫</h6>
                <p class="small text-muted">拦截所有已知的 AI 训练爬虫和数据采集程序</p>
                <button class="btn btn-danger btn-sm" onclick="quickBlockAll()">立即拦截</button>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-success h-100">
            <div class="card-body text-center">
                <i class="bi bi-shield-check text-success" style="font-size: 32px;"></i>
                <h6 class="mt-2">一键允许所有 AI 爬虫</h6>
                <p class="small text-muted">移除所有 AI 爬虫拦截规则，允许访问</p>
                <button class="btn btn-success btn-sm" onclick="quickAllowAll()">允许全部</button>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-warning h-100">
            <div class="card-body text-center">
                <i class="bi bi-funnel text-warning" style="font-size: 32px;"></i>
                <h6 class="mt-2">按类别管理</h6>
                <p class="small text-muted">按 AI 训练、AI 搜索、AI 助手分类管理</p>
                <button class="btn btn-warning btn-sm" onclick="showCategoryManager()">分类管理</button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">选择域名</div>
            <div class="card-body">
                <div id="aiZoneSelector"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <!-- AI 爬虫列表 -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                AI 爬虫列表
                <div>
                    <button class="btn btn-sm btn-outline-danger" onclick="selectAllCrawlers()">全选拦截</button>
                    <button class="btn btn-sm btn-outline-success" onclick="deselectAllCrawlers()">全选允许</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="40"><input type="checkbox" class="form-check-input" id="crawlerSelectAll" onchange="toggleAllCrawlers(this)"></th>
                                <th>爬虫名称</th>
                                <th>运营商</th>
                                <th>类别</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody id="crawlerList">
                            <tr><td colspan="5" class="text-center py-3">加载中...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- 高级设置 -->
        <div class="card mt-3">
            <div class="card-header">高级设置</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">拦截方式</label>
                        <select class="form-select" id="aiBlockMode">
                            <option value="bot_management" selected>Bot Management（默认）</option>
                            <option value="waf">WAF 自定义规则</option>
                            <option value="both">两者同时启用</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">拦截动作</label>
                        <select class="form-select" id="aiAction">
                            <option value="block">拦截 (Block)</option>
                            <option value="challenge">质询 (Challenge)</option>
                            <option value="allow">允许 (Allow)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">响应状态码</label>
                        <select class="form-select" id="aiResponseCode">
                            <option value="403">403 Forbidden</option>
                            <option value="402">402 Payment Required</option>
                            <option value="429">429 Too Many Requests</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">每批处理数量</label>
                        <select class="form-select" id="aiBatchSize">
                            <option value="5">5 个/批（稳定）</option>
                            <option value="10" selected>10 个/批（推荐）</option>
                            <option value="20">20 个/批（较快）</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">自定义拦截响应内容</label>
                        <textarea class="form-control" id="aiResponseBody" rows="3" placeholder="Access denied. AI crawling is not permitted on this website."></textarea>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button class="btn btn-cf btn-lg" onclick="applyAiSettings()">
                        <i class="bi bi-check-circle"></i> 应用设置到选中域名
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 分类管理弹窗 -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">按类别管理 AI 爬虫</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">选择要拦截的 AI 爬虫类别：</p>
                <div class="list-group">
                    <label class="list-group-item d-flex align-items-center gap-3">
                        <input type="checkbox" class="form-check-input category-check" value="AI Training" checked>
                        <div>
                            <strong>AI 训练爬虫</strong>
                            <div class="small text-muted">GPTBot, CCBot, Google-Extended, Bytespider 等</div>
                        </div>
                        <span class="badge bg-danger ms-auto">高风险</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center gap-3">
                        <input type="checkbox" class="form-check-input category-check" value="AI Search">
                        <div>
                            <strong>AI 搜索爬虫</strong>
                            <div class="small text-muted">PerplexityBot, OAI-SearchBot 等</div>
                        </div>
                        <span class="badge bg-primary ms-auto">中风险</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center gap-3">
                        <input type="checkbox" class="form-check-input category-check" value="AI Assistant">
                        <div>
                            <strong>AI 助手爬虫</strong>
                            <div class="small text-muted">ChatGPT-User, ClaudeBot 等</div>
                        </div>
                        <span class="badge bg-warning text-dark ms-auto">低风险</span>
                    </label>
                    <label class="list-group-item d-flex align-items-center gap-3">
                        <input type="checkbox" class="form-check-input category-check" value="Scraper">
                        <div>
                            <strong>通用抓取工具</strong>
                            <div class="small text-muted">Scrapy, img2dataset 等</div>
                        </div>
                        <span class="badge bg-secondary ms-auto">通用</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-danger" onclick="applyCategoryBlock()">拦截选中类别</button>
            </div>
        </div>
    </div>
</div>

<!-- 执行结果 -->
<div class="card mt-3" id="aiResultCard" style="display:none;">
    <div class="card-header d-flex justify-content-between align-items-center">
        执行结果
        <span id="aiProgress" class="badge bg-primary"></span>
    </div>
    <div class="card-body">
        <div class="result-log" id="aiResultLog"></div>
    </div>
</div>

<script>
const crawlerData = {
    'GPTBot': { operator: 'OpenAI', category: 'AI Training' },
    'ChatGPT-User': { operator: 'OpenAI', category: 'AI Assistant' },
    'Google-Extended': { operator: 'Google', category: 'AI Training' },
    'Googlebot-AI': { operator: 'Google', category: 'AI Search' },
    'CCBot': { operator: 'Common Crawl', category: 'AI Training' },
    'anthropic-ai': { operator: 'Anthropic', category: 'AI Training' },
    'ClaudeBot': { operator: 'Anthropic', category: 'AI Assistant' },
    'Bytespider': { operator: 'ByteDance', category: 'AI Training' },
    'Diffbot': { operator: 'Diffbot', category: 'AI Training' },
    'FacebookBot': { operator: 'Meta', category: 'AI Training' },
    'Meta-ExternalAgent': { operator: 'Meta', category: 'AI Training' },
    'PerplexityBot': { operator: 'Perplexity', category: 'AI Search' },
    'Applebot-Extended': { operator: 'Apple', category: 'AI Training' },
    'cohere-ai': { operator: 'Cohere', category: 'AI Training' },
    'Amazonbot': { operator: 'Amazon', category: 'AI Training' },
    'OAI-SearchBot': { operator: 'OpenAI', category: 'AI Search' },
    'YouBot': { operator: 'You.com', category: 'AI Search' },
    'Scrapy': { operator: 'Various', category: 'Scraper' },
    'Timpibot': { operator: 'Timpi', category: 'AI Training' },
    'VelenPublicWebCrawler': { operator: 'Velen', category: 'AI Training' },
    'omgili': { operator: 'Webz.io', category: 'AI Training' },
    'Kangaroo Bot': { operator: 'Kangaroo', category: 'AI Training' },
    'img2dataset': { operator: 'Various', category: 'AI Training' },
};

const categoryColors = {
    'AI Training': 'bg-danger',
    'AI Search': 'bg-primary',
    'AI Assistant': 'bg-warning text-dark',
    'Scraper': 'bg-secondary',
};

document.addEventListener('DOMContentLoaded', () => {
    CF.renderZoneSelector('aiZoneSelector');
    renderCrawlerList();
});

function renderCrawlerList() {
    const tbody = document.getElementById('crawlerList');
    let html = '';
    Object.entries(crawlerData).forEach(([name, info]) => {
        html += `<tr>
            <td><input type="checkbox" class="form-check-input crawler-check" value="${name}" checked></td>
            <td><strong>${name}</strong></td>
            <td>${info.operator}</td>
            <td><span class="badge ${categoryColors[info.category] || 'bg-secondary'}">${info.category}</span></td>
            <td><select class="form-select form-select-sm crawler-action" data-crawler="${name}" style="width:auto;">
                <option value="block">拦截</option>
                <option value="challenge">质询</option>
                <option value="allow">允许</option>
            </select></td>
        </tr>`;
    });
    tbody.innerHTML = html;
}

function toggleAllCrawlers(el) {
    document.querySelectorAll('.crawler-check').forEach(cb => cb.checked = el.checked);
}
function selectAllCrawlers() {
    document.querySelectorAll('.crawler-check').forEach(cb => cb.checked = true);
    document.querySelectorAll('.crawler-action').forEach(sel => sel.value = 'block');
}
function deselectAllCrawlers() {
    document.querySelectorAll('.crawler-check').forEach(cb => cb.checked = true);
    document.querySelectorAll('.crawler-action').forEach(sel => sel.value = 'allow');
}
function showCategoryManager() {
    new bootstrap.Modal(document.getElementById('categoryModal')).show();
}

async function quickBlockAll() {
    const zoneIds = CF.getSelectedZoneIds('aiZoneSelector');
    if (zoneIds.length === 0) { CF.toast('请先选择域名', 'error'); return; }
    if (!confirm(`确定要为选中的 ${zoneIds.length} 个域名拦截所有 AI 爬虫吗？`)) return;
    
    const batchSize = parseInt(document.getElementById('aiBatchSize').value);
    CF.showLoading('正在一键拦截所有 AI 爬虫...');
    showAiResult();
    
    const result = await CF.batchRequest('/ai-crawlers/block-all', zoneIds, {
        response_code: document.getElementById('aiResponseCode').value,
        response_body: document.getElementById('aiResponseBody').value || 'Access denied. AI crawling is not permitted.'
    }, batchSize, 'aiResultLog');
    
    CF.hideLoading();
    CF.log('aiResultLog', `\n${result.message}`, 'info');
    document.getElementById('aiProgress').textContent = result.message;
    CF.toast(result.message, result.summary.fail > 0 ? 'error' : 'success');
}

async function quickAllowAll() {
    const zoneIds = CF.getSelectedZoneIds('aiZoneSelector');
    if (zoneIds.length === 0) { CF.toast('请先选择域名', 'error'); return; }
    if (!confirm(`确定要为选中的 ${zoneIds.length} 个域名允许所有 AI 爬虫吗？`)) return;
    
    const batchSize = parseInt(document.getElementById('aiBatchSize').value);
    CF.showLoading('正在移除 AI 爬虫拦截规则...');
    showAiResult();
    
    const result = await CF.batchRequest('/ai-crawlers/allow-all', zoneIds, {}, batchSize, 'aiResultLog');
    
    CF.hideLoading();
    CF.log('aiResultLog', `\n${result.message}`, 'info');
    document.getElementById('aiProgress').textContent = result.message;
    CF.toast(result.message, result.summary.fail > 0 ? 'error' : 'success');
}

async function applyAiSettings() {
    const zoneIds = CF.getSelectedZoneIds('aiZoneSelector');
    if (zoneIds.length === 0) { CF.toast('请先选择域名', 'error'); return; }
    
    const crawlers = Array.from(document.querySelectorAll('.crawler-check:checked')).map(cb => cb.value);
    if (crawlers.length === 0) { CF.toast('请至少选择一个爬虫', 'error'); return; }
    
    const batchSize = parseInt(document.getElementById('aiBatchSize').value);
    CF.showLoading('正在应用 AI 爬虫设置...');
    showAiResult();
    
    const result = await CF.batchRequest('/ai-crawlers/batch', zoneIds, {
        crawlers: crawlers,
        crawler_action: document.getElementById('aiAction').value,
        block_mode: document.getElementById('aiBlockMode').value,
        response_code: document.getElementById('aiResponseCode').value,
        response_body: document.getElementById('aiResponseBody').value
    }, batchSize, 'aiResultLog');
    
    CF.hideLoading();
    CF.log('aiResultLog', `\n${result.message}`, 'info');
    document.getElementById('aiProgress').textContent = result.message;
    CF.toast(result.message, result.summary.fail > 0 ? 'error' : 'success');
}

async function applyCategoryBlock() {
    const zoneIds = CF.getSelectedZoneIds('aiZoneSelector');
    if (zoneIds.length === 0) { CF.toast('请先选择域名', 'error'); return; }
    
    const categories = Array.from(document.querySelectorAll('.category-check:checked')).map(cb => cb.value);
    if (categories.length === 0) { CF.toast('请至少选择一个类别', 'error'); return; }
    
    bootstrap.Modal.getInstance(document.getElementById('categoryModal')).hide();
    
    const batchSize = parseInt(document.getElementById('aiBatchSize').value);
    CF.showLoading('正在按类别拦截 AI 爬虫...');
    showAiResult();
    
    const result = await CF.batchRequest('/ai-crawlers/batch', zoneIds, {
        block_categories: categories,
        crawler_action: 'block',
        block_mode: 'waf',
        response_code: '403',
        response_body: 'Access denied. AI crawling is not permitted on this website.'
    }, batchSize, 'aiResultLog');
    
    CF.hideLoading();
    CF.log('aiResultLog', `\n${result.message}`, 'info');
    document.getElementById('aiProgress').textContent = result.message;
    CF.toast(result.message, result.summary.fail > 0 ? 'error' : 'success');
}

function showAiResult() {
    document.getElementById('aiResultCard').style.display = 'block';
    document.getElementById('aiProgress').textContent = '';
    CF.clearLog('aiResultLog');
}
</script>
