#!/bin/bash
set -e
cd /var/www/html

# สร้าง config จาก env (มี default สำหรับ dev)
export PHOTOBOOTH_BASE_DIR=/var/www/html
php /var/www/html/docker/generate-config.php

# ติดตั้ง dependencies ครั้งแรก (หรือเมื่อยังไม่มี)
if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --ignore-platform-reqs
fi
if [ ! -d node_modules/.bin ]; then
    npm ci
fi

# Build frontend ครั้งแรก
if [ ! -f assets/js/core.js ] || [ ! -f resources/css/framework.css ]; then
    npm run build:gulp || true
    npm run build:head || true
fi

# สิทธิ์โฟลเดอร์ที่ต้องเขียน
chown -R www-data:www-data data private var 2>/dev/null || true

# เปิด watch ในพื้นหลัง (แก้โค้ดแล้ว rebuild อัตโนมัติ)
npm run watch:gulp &
WATCH_PID=$!

# เมื่อ container หยุด ให้ kill watch ด้วย
trap "kill $WATCH_PID 2>/dev/null || true" EXIT

exec apache2-foreground
