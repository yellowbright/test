# Nexora

仿企业服务站风格的响应式官网。**Laravel + Blade 服务端渲染**多页静态内容，联系表单通过 **AJAX** 提交并入库（可选邮件通知）。部署采用精简的 **nginx + php-fpm + mysql** Docker 方案。

## 技术栈

- 后端：Laravel 12（PHP 8.3）
- 前端：官网用 Blade 模板 + 手写响应式 CSS + 原生 JS；节日提醒模块（`/festival`）为独立 Vue 3 + Vite 应用
- 数据库：MySQL 8（本地开发用 SQLite）
- 部署：Docker Compose（nginx + php-fpm + mysql）
- CI：GitHub Actions（PR 检测 + 手动构建推送 GHCR）

## 页面

| 路由 | 页面 |
|------|------|
| `/` | 首页（Hero / 服务 / 技术栈 / 数据 / 评价 / CTA） |
| `/services` | 服务与开发流程 |
| `/about` | 关于我们 / 服务行业 |
| `/contact` | 联系表单（AJAX 提交） |
| `/festival` | 节日提醒（Vue SPA，日历选节日 + 登录保存 + 到期邮件提醒） |

表单提交 `POST /contact` → 校验 → 写入 `contacts` 表 → 若配置了 `CONTACT_NOTIFY_EMAIL` 则发送通知邮件。

## 本地开发

### 1. 初始化（首次）

```bash
cp .env.example .env        # 已默认 SQLite，开箱即用
composer install
php artisan key:generate
php artisan migrate          # 含 users / contacts / sanctum / festival_presets / reminders
php artisan db:seed          # 可选：导入节日预设数据
```

> Windows 本地 PHP 需启用 `mbstring`、`pdo_sqlite` 扩展（php.ini 取消对应行注释）。

### 2. 启动后端

```bash
php artisan serve            # http://127.0.0.1:8000
```

Blade 多页官网（`/`、`/services`、`/about`、`/contact`）改完模板刷新即可见，**无需** Vite。

### 3. 节日提醒前端（Vue，按需启动）

节日模块（`/festival`）是独立的 Vue 3 + Vite 应用，源码在 `frontend/`，构建产物输出到 `public/festival_app/`。

- **只看现状 / 调后端 API**：无需启动 Vite，直接访问 `http://127.0.0.1:8000/festival`（加载的是已构建的静态产物）。
- **要改前端 Vue 代码**：启动 Vite 开发服务器获得热更新（HMR），改完秒级生效，无需反复 build。

```bash
cd frontend
npm install                  # 首次
npm run dev                  # 开发：http://localhost:5173/festival_app/
npm run build                # 构建：产物写入 ../public/festival_app/
```

Vite 已配置代理，前端的 `/api`、`/sanctum` 请求自动转发到后端 `http://127.0.0.1:8000`，因此开发时需同时运行 `php artisan serve`。

> 改完 Vue 代码后，要在 `/festival` 页面看到更新，必须执行 `npm run build` 重新生成 `public/festival_app/` 产物。

### 4. 提醒邮件（本地验证）

`.env` 默认 `QUEUE_CONNECTION=sync`（任务同步执行，无需 queue worker）、`MAIL_MAILER=log`（邮件写入 `storage/logs/laravel.log` 而非真实发送）。

```bash
php artisan reminders:send-due   # 派发当天到期的提醒邮件任务
# 查看 storage/logs/laravel.log 确认邮件内容
```

## 生产部署（Docker）

```bash
cp docker/env.prod.example .env
# 修改 .env 中所有 CHANGE_ME，并生成 APP_KEY：
#   echo "base64:$(openssl rand -base64 32)"

# 本地构建并启动（nginx + php + mysql）
./deploy.sh

# 首次初始化数据库
docker compose -f docker-compose.prod.yml exec php php artisan migrate --force
```

访问 `http://<服务器IP>` 即可。生产环境默认 `APP_DEBUG=false`、MySQL 仅绑定 `127.0.0.1`。

镜像说明：同一份 `Dockerfile` 构建两个目标 —— `app`（php-fpm，含完整代码）与 `web`（nginx，含 `public/` 静态资源），避免静态文件与代码不同步的问题。

## CI / 镜像构建

`.github/workflows/build.yml`：

- **push / PR**：自动跑 `composer validate` + 安装依赖 + `pint --test` 代码风格检测。
- **手动构建**：在 GitHub → Actions → CI → **Run workflow**，勾选后构建并推送 `ghcr.io/<owner>/<repo>-app` 与 `-web` 镜像到 GHCR。

服务器用镜像部署：在 `.env` 填好 `REGISTRY_IMAGE_APP` / `REGISTRY_IMAGE_WEB`，执行 `./deploy.sh --pull`。

## 后续可加固（按需）

当前为最小可用栈。若后续有需要，可参照同仓 `lucas` 项目逐步增强：

- **Redis**：会话 / 缓存 / 队列异步化（邮件改为队列发送）。
- **CrowdSec**：nginx 接 openresty + lua bouncer，自动封禁恶意 IP。
- **Cloudflare**：橙色云 + Origin Certificate + 云防火墙只放行 CF IP 段，nginx 还原真实 IP。
