<?php if (!$isAuthed): ?>
<!-- 未认证 - 显示登录表单 -->
<div class="container" style="max-width: 600px; margin: 80px auto;">
    <div class="text-center mb-4">
        <h1 style="font-size: 36px; font-weight: 700;"><span style="color: var(--cf-orange);">CloudFlare</span> 批量管理工具</h1>
        <p class="text-muted">请输入您的 CloudFlare API 认证信息开始使用</p>
    </div>
    
    <div class="card">
        <div class="card-body p-4">
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tokenAuth">API Token（推荐）</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#keyAuth">Global API Key</a>
                </li>
            </ul>
            
            <div class="tab-content">
                <div class="tab-pane fade show active" id="tokenAuth">
                    <form id="tokenForm" onsubmit="return doAuth('token')">
                        <div class="mb-3">
                            <label class="form-label">API Token</label>
                            <input type="password" class="form-control" id="apiToken" placeholder="输入您的 API Token" required>
                            <div class="form-text">在 CloudFlare Dashboard > My Profile > API Tokens 中创建</div>
                        </div>
                        <button type="submit" class="btn btn-cf w-100">验证并登录</button>
                    </form>
                </div>
                <div class="tab-pane fade" id="keyAuth">
                    <form id="keyForm" onsubmit="return doAuth('key')">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="authEmail" placeholder="CloudFlare 账户邮箱" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Global API Key</label>
                            <input type="password" class="form-control" id="apiKey" placeholder="输入 Global API Key" required>
                            <div class="form-text">在 CloudFlare Dashboard > My Profile > API Tokens > Global API Key</div>
                        </div>
                        <button type="submit" class="btn btn-cf w-100">验证并登录</button>
                    </form>
                </div>
            </div>
            
            <div id="authMessage" class="mt-3" style="display:none;"></div>
        </div>
    </div>
    
    <div class="text-center mt-4 text-muted small">
        <p>支持 API Token 和 Global API Key 两种认证方式<br>
        推荐使用 API Token，可精确控制权限范围</p>
    </div>
</div>

<script>
async function doAuth(type) {
    event.preventDefault();
    const msgEl = document.getElementById('authMessage');
    msgEl.style.display = 'none';
    
    let data = { auth_type: type };
    
    if (type === 'token') {
        data.api_token = document.getElementById('apiToken').value;
    } else {
        data.email = document.getElementById('authEmail').value;
        data.api_key = document.getElementById('apiKey').value;
    }
    
    try {
        const resp = await fetch('/auth/save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await resp.json();
        
        if (result.success) {
            msgEl.className = 'mt-3 alert alert-success';
            msgEl.textContent = '认证成功，正在跳转...';
            msgEl.style.display = 'block';
            setTimeout(() => window.location.href = '/zones', 1000);
        } else {
            msgEl.className = 'mt-3 alert alert-danger';
            msgEl.textContent = result.message || '认证失败';
            msgEl.style.display = 'block';
        }
    } catch (e) {
        msgEl.className = 'mt-3 alert alert-danger';
        msgEl.textContent = '网络错误: ' + e.message;
        msgEl.style.display = 'block';
    }
    
    return false;
}
</script>

<?php else: ?>
<!-- 已认证 - 显示功能概览 -->
<div class="page-header">
    <h2>功能概览</h2>
    <p class="text-muted">CloudFlare 批量管理工具 - 快速管理您的所有域名</p>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <i class="bi bi-globe text-primary" style="font-size: 40px;"></i>
                <h5 class="mt-3">批量添加域名</h5>
                <p class="text-muted small">将域名解析权转移到 CloudFlare</p>
                <a href="/zones" class="btn btn-cf-outline btn-sm">进入管理</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <i class="bi bi-diagram-3 text-success" style="font-size: 40px;"></i>
                <h5 class="mt-3">域名批量解析</h5>
                <p class="text-muted small">批量添加或修改 DNS 解析记录</p>
                <a href="/dns" class="btn btn-cf-outline btn-sm">进入管理</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <i class="bi bi-shield-check text-info" style="font-size: 40px;"></i>
                <h5 class="mt-3">批量开关代理</h5>
                <p class="text-muted small">开启或关闭 CDN 代理加速</p>
                <a href="/proxy" class="btn btn-cf-outline btn-sm">进入管理</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <i class="bi bi-lock text-warning" style="font-size: 40px;"></i>
                <h5 class="mt-3">HTTPS/SSL 设置</h5>
                <p class="text-muted small">批量设置加密模式和 TLS 版本</p>
                <a href="/ssl" class="btn btn-cf-outline btn-sm">进入管理</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-danger">
            <div class="card-body text-center p-4">
                <i class="bi bi-robot text-danger" style="font-size: 40px;"></i>
                <h5 class="mt-3">AI 爬虫管理 <span class="badge bg-danger">NEW</span></h5>
                <p class="text-muted small">拦截 AI 爬虫和自动化训练程序</p>
                <a href="/ai-crawlers" class="btn btn-danger btn-sm">进入管理</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <i class="bi bi-hdd-stack text-secondary" style="font-size: 40px;"></i>
                <h5 class="mt-3">缓存管理</h5>
                <p class="text-muted small">清除缓存和缓存设置</p>
                <a href="/cache" class="btn btn-cf-outline btn-sm">进入管理</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <i class="bi bi-speedometer2 text-primary" style="font-size: 40px;"></i>
                <h5 class="mt-3">代码压缩/优化</h5>
                <p class="text-muted small">HTML/JS/CSS 压缩和网络优化</p>
                <a href="/optimize" class="btn btn-cf-outline btn-sm">进入管理</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <i class="bi bi-file-earmark-code text-success" style="font-size: 40px;"></i>
                <h5 class="mt-3">规则管理</h5>
                <p class="text-muted small">批量复制/删除 WAF 和页面规则</p>
                <a href="/rules" class="btn btn-cf-outline btn-sm">进入管理</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <i class="bi bi-download text-info" style="font-size: 40px;"></i>
                <h5 class="mt-3">批量导出</h5>
                <p class="text-muted small">导出域名列表和配置信息</p>
                <a href="/export" class="btn btn-cf-outline btn-sm">进入管理</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
