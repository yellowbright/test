#!/usr/bin/env bash
# =====================================================
# Nexora 站点 - 精简部署脚本
#   ./deploy.sh            本地构建并部署（适合服务器有代码）
#   ./deploy.sh --pull     从 GHCR 拉镜像部署（需先填好 .env 的 REGISTRY_IMAGE_*）
#   ./deploy.sh --migrate  部署后执行数据库迁移
# =====================================================
set -euo pipefail

cd "$(dirname "$0")"
COMPOSE="docker compose -f docker-compose.prod.yml"
PULL=false
MIGRATE=false

for arg in "$@"; do
  case "$arg" in
    --pull) PULL=true ;;
    --migrate) MIGRATE=true ;;
    *) echo "未知参数: $arg"; exit 1 ;;
  esac
done

[ -f .env ] || { echo "缺少 .env，请先 cp docker/env.prod.example .env 并填写"; exit 1; }

if $PULL; then
  echo ">> 拉取镜像并启动"
  $COMPOSE pull
  $COMPOSE up -d --no-build
else
  echo ">> 本地构建并启动"
  $COMPOSE up -d --build
fi

echo ">> 等待服务就绪 ..."
sleep 8
$COMPOSE ps

if $MIGRATE; then
  echo ">> 执行数据库迁移"
  $COMPOSE exec -T php php artisan migrate --force
fi

echo ">> 清理悬空镜像"
docker image prune -f >/dev/null 2>&1 || true
echo ">> 部署完成"
