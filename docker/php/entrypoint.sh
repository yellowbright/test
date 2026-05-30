#!/bin/sh
set -e

# 等待 MySQL 就绪（仅当配置了 DB_HOST）
if [ -n "$DB_HOST" ]; then
  echo "等待数据库 ${DB_HOST}:${DB_PORT:-3306} ..."
  until nc -z "$DB_HOST" "${DB_PORT:-3306}"; do
    sleep 2
  done
  echo "数据库已就绪。"
fi

# 修正运行时挂载目录权限
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# 生产缓存（依赖运行时挂载的 .env，必须在容器启动时执行）
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link 2>/dev/null || true

# 可选自动迁移（默认关闭，生产数据库变更建议人工把控）
if [ "${AUTO_MIGRATE:-false}" = "true" ]; then
  php artisan migrate --force || true
fi

exec "$@"
