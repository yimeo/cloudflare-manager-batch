# CloudFlare 批量管理工具

基于 Nginx + PHP 开发的 CloudFlare 批量管理工具，提供全套域名批量管理功能，并额外增加了 **AI 爬虫拦截管理** 模块。

## 功能特性

1. **域名管理**
   - 批量添加域名 (Zone)
   - 批量删除域名 (Zone)
   - 批量 DNS 解析管理（添加、修改、删除）
   - 批量开关代理 (CDN 加速)
   - 批量导出域名列表

2. **安全与优化**
   - HTTPS 边缘证书批量设置
   - 批量代码压缩 / 网络优化
   - 批量清除缓存 / 缓存设置
   - 批量规则管理（复制、删除 WAF/页面规则等）
   - 批量修改设置项

3. **AI 爬虫管理 (独家新增)**
   - 一键拦截所有已知 AI 训练爬虫和数据采集程序
   - 一键允许所有 AI 爬虫
   - 按类别拦截（AI 训练、AI 搜索、AI 助手、通用抓取）
   - 细粒度控制响应动作（拦截、质询、允许）和自定义响应信息
   - 整合 WAF 自定义规则与 Bot Management

## 环境要求

- PHP 8.0+ (需要 `curl`, `json` 扩展)
- Nginx 或 Apache
- 无需数据库（基于 API 实时获取数据）

## 部署说明

### Nginx 配置示例

本项目使用单一入口 `public/index.php`，请配置 URL 重写：

```nginx
server {
    listen 80;
    server_name cf.yourdomain.com;
    root /path/to/cloudflare-manager/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock; # 根据实际情况修改
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 安装步骤

1. 将源码上传到服务器目录。
2. 配置 Nginx 虚拟主机并重启 Nginx。
3. 确保 `config` 和 `app` 目录的权限正确，`public` 目录对外公开。
4. 访问配置的域名，输入 CloudFlare API Token 即可使用。
5. 伪静态
```
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```
## 认证说明

系统支持两种认证方式：
1. **API Token (推荐)**：在 CloudFlare Dashboard -> My Profile -> API Tokens 中创建。需要的权限包括：Zone (Read/Edit), DNS (Read/Edit), Cache Purge (Purge), SSL and Certificates (Read/Edit), WAF (Read/Edit) 等。
2. **Global API Key**：需要提供 CloudFlare 账户邮箱和 Global API Key（拥有所有权限）。

认证信息仅保存在当前浏览器的 Session 中，不会上传到任何第三方服务器。

## 目录结构

- `/app` - 核心代码目录
  - `/Controllers` - 控制器
  - `/Services` - 服务类 (API, Router)
  - `/Views` - 视图模板
- `/config` - 配置文件
- `/public` - Web 根目录，存放入口文件和静态资源
