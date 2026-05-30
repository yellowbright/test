# =====================================================
# Nexora 站点 - 生产镜像（单 Dockerfile 两个目标）
#   target=app  -> php-fpm，含完整 Laravel 代码与依赖
#   target=web  -> nginx，含 public/ 静态资源 + 站点配置
# 两个镜像由同一份代码构建，避免「nginx 看不到新静态文件」的卷同步问题。
# =====================================================

# -----------------------------------------------------
# PHP 依赖与应用代码
# -----------------------------------------------------
FROM php:8.3-fpm-alpine AS app

RUN apk add --no-cache \
        git unzip libzip-dev oniguruma-dev icu-dev libpng-dev \
        netcat-openbsd \
    && docker-php-ext-install pdo_mysql mbstring bcmath gd intl zip opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 先装依赖，利用层缓存
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# 复制应用代码并生成 autoload
COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-custom.ini
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]

# -----------------------------------------------------
# Nginx 静态资源 + 反向代理
# -----------------------------------------------------
FROM nginx:1.27-alpine AS web

COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf
# 仅需静态文件；index.php 等 PHP 由 php 容器执行
COPY public /var/www/html/public
