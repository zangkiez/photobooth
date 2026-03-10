#!/bin/bash
set -e
cd /var/www/html

# สร้าง config จาก env เท่านั้น (ไม่ mount ไฟล์จาก host)
export PHOTOBOOTH_BASE_DIR=/var/www/html
export PHOTOBOOTH_FORCE_GENERATE_CONFIG=1
php /var/www/html/docker/generate-config.php

chown -R www-data:www-data data private var config 2>/dev/null || true

exec apache2-foreground
