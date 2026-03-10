# Deploy Photobooth บน Ubuntu และ Raspberry Pi (Production)

> คู่มือ deploy แบบละเอียดสำหรับ **รันจริงมีผู้ใช้ทั้งวัน** โดยเน้นให้ **ระบบไม่ค้าง** — ใช้ได้ทั้ง Ubuntu Server และ Raspberry Pi

**เอกสารที่เกี่ยวข้อง**
- [DEPLOY.md](DEPLOY.md) — Dev (DDEV), Print Relay, Cloudflare Tunnel
- [docs/print-relay-cloudflare-tunnel.md](print-relay-cloudflare-tunnel.md) — Photobooth บน cloud ส่งงานพิมพ์ไป local ผ่าน HTTPS

---

## สารบัญ

1. [ภาพรวมและเป้าหมาย](#1-ภาพรวมและเป้าหมาย)
2. [ความต้องการระบบร่วม](#2-ความต้องการระบบร่วม)
3. [Deploy ด้วย Docker (Production) — แยก 2 เวอร์ชัน](#3-deploy-ด้วย-docker-production--แยก-2-เวอร์ชัน)
4. [Deploy บน Ubuntu (ติดตั้งตรง)](#4-deploy-บน-ubuntu-ติดตั้งตรง)
5. [Deploy บน Raspberry Pi (ติดตั้งตรง)](#5-deploy-บน-raspberry-pi-ติดตั้งตรง)
6. [ตั้งค่าเพื่อความเสถียร — ระบบไม่ค้าง](#6-ตั้งค่าเพื่อความเสถียร--ระบบไม่ค้าง)
7. [ความปลอดภัย](#7-ความปลอดภัย)
8. [การอัปเดตและดูแล](#8-การอัปเดตและดูแล)
9. [Troubleshooting](#9-troubleshooting)

---

## 1. ภาพรวมและเป้าหมาย

| เป้าหมาย | รายละเอียด |
|----------|-------------|
| **รัน production** | มีผู้ใช้จริง (แขก) เข้าเว็บถ่ายรูป/พิมพ์ทั้งวัน |
| **ระบบไม่ค้าง** | ปรับ PHP, เว็บเซิร์ฟเวอร์, หน่วยความจำ, อุณหภูมิ (Pi) และ I/O ให้เหมาะสม |
| **Ubuntu** | เหมาะกับเซิร์ฟเวอร์หรือ PC ที่ใช้เป็นสถานี Photobooth |
| **Raspberry Pi** | เหมาะกับสถานีขนาดเล็ก ต่อกล้อง/เครื่องพิมพ์ USB โดยตรง |

**สถาปัตยกรรม Production (โดยทั่วไป)**

```
ผู้ใช้ (มือถือ/แท็บเล็ต) ──LAN──► เซิร์ฟเวอร์ (Ubuntu หรือ Raspberry Pi)
                                    │
                                    ├── Apache + PHP (Photobooth)
                                    ├── กล้อง (gphoto2 / Pi Camera / USB)
                                    └── เครื่องพิมพ์ (CUPS, USB)
```

---

## 2. ความต้องการระบบร่วม

### 2.1 ซอฟต์แวร์ที่ต้องมี

| ซอฟต์แวร์ | เวอร์ชันขั้นต่ำ | หมายเหตุ |
|-----------|-----------------|----------|
| PHP | 8.4 | รวม ext-gd, ext-mbstring, ext-xml, ext-zip, ext-fileinfo, ext-curl |
| Node.js | 20.x | ใช้เฉพาะตอน build frontend (ไม่ต้องรันตลอด) |
| npm | 10.x | คู่กับ Node 20 |
| Apache | 2.4 | หรือ Nginx + PHP-FPM |
| Composer | 2.x | ติดตั้ง PHP dependencies |
| ffmpeg | - | สำหรับ GIF/MP4 ใน collage slideshow |
| CUPS | - | ถ้าใช้ฟีเจอร์พิมพ์ |
| gphoto2 / libcamera | - | ตามประเภทกล้อง (DSLR/USB/Pi Camera) |

### 2.2 โฟลเดอร์ที่ต้องให้เว็บเซิร์ฟเวอร์เขียนได้

| โฟลเดอร์/ไฟล์ | วัตถุประสงค์ |
|----------------|--------------|
| `data/` | รูปภาพ, thumbnail, print, QR, ไฟล์ฐานข้อมูลรูป/อีเมล/พิมพ์, temp |
| `private/` | รูป/ฟอนต์/วิดีโอที่อัปโหลด (backgrounds, frames, fonts) |
| `var/` | log, cache, lock files |

เจ้าของแนะนำ: `www-data` (Apache) หรือ user ที่รัน PHP-FPM

---

## 3. Deploy ด้วย Docker (Production) — แยก 2 เวอร์ชัน

แนะนำให้ใช้ **Docker** สำหรับ production เพื่อความสม่ำเสมอของ environment และแยก 2 เวอร์ชันตามแพลตฟอร์ม:

| เวอร์ชัน | ใช้กับ | Compose file | Platform |
|----------|--------|----------------|----------|
| **Ubuntu** | PC/เซิร์ฟเวอร์ x86_64 (Intel/AMD) | `docker-compose.production.ubuntu.yml` | `linux/amd64` |
| **Raspberry Pi** | Raspberry Pi 4/5 (ARM 64-bit) | `docker-compose.production.pi.yml` | `linux/arm64` |

ภาพรวมไฟล์ Docker:

- **`Dockerfile.production`** — Multi-stage build (builder: Composer + npm build → runner: PHP + Apache + ffmpeg + CUPS client). รองรับทั้ง amd64 และ arm64 จาก image เดียว
- **`docker-compose.production.ubuntu.yml`** — รันบน Ubuntu (amd64), จำกัด memory 512M
- **`docker-compose.production.pi.yml`** — รันบน Raspberry Pi (arm64), จำกัด memory 384M
- **`.dockerignore`** — ลด build context (ไม่เอา node_modules, .git, data ขึ้น image)

### 3.1 สิ่งที่ต้องมีบน host

- Docker Engine + Docker Compose (v2)
- **Ubuntu:** Docker ติดตั้งจาก repo หรือ [docker.com](https://docs.docker.com/engine/install/ubuntu/)
- **Raspberry Pi:** Raspberry Pi OS 64-bit + Docker ([ติดตั้ง Docker บน Pi](https://docs.docker.com/engine/install/debian/))

บน host ต้องมี **CUPS** รันอยู่และเครื่องพิมพ์เพิ่มแล้ว ถ้าต้องการให้ container ส่งงานพิมพ์ผ่าน host:

```bash
sudo apt install -y cups
sudo systemctl enable cups && sudo systemctl start cups
# เพิ่มเครื่องพิมพ์ผ่าน http://localhost:631 หรือ lpadmin
```

### 3.2 Build และรัน — Ubuntu (amd64)

```bash
cd /path/to/photobooth

# สร้าง config ก่อน (ถ้ายังไม่มี)
cp -n config/my.config.inc.php config/my.config.inc.php.bak 2>/dev/null || true
# แก้ไข config/my.config.inc.php ตามต้องการ (webserver.url, print, ฯลฯ)

# Build และรัน
docker compose -f docker-compose.production.ubuntu.yml build --no-cache
docker compose -f docker-compose.production.ubuntu.yml up -d

# ดู log
docker compose -f docker-compose.production.ubuntu.yml logs -f
```

เปิดเบราว์เซอร์: `http://<IP ของ host>`

### 3.3 Build และรัน — Raspberry Pi (arm64)

```bash
cd /path/to/photobooth

# สร้าง config ก่อน
cp -n config/my.config.inc.php config/my.config.inc.php.bak 2>/dev/null || true
# แก้ config/my.config.inc.php (webserver.url = http://<IP ของ Pi>)

# Build (บน Pi จะใช้เวลานานกว่าบน PC)
docker compose -f docker-compose.production.pi.yml build --no-cache
docker compose -f docker-compose.production.pi.yml up -d

docker compose -f docker-compose.production.pi.yml logs -f
```

### 3.4 Volume และ Config

ทั้งสอง compose ใช้ volume เดียวกัน:

| Volume | เก็บอะไร |
|--------|----------|
| `photobooth_data` | รูปภาพ, thumbnail, ฐานข้อมูลรูป, print, qr, temp |
| `photobooth_private` | ไฟล์ส่วนตัว (frames, backgrounds, fonts) |
| `photobooth_var` | log, cache, lock |

Config บน host: ต้องมีไฟล์ `config/my.config.inc.php` แล้ว mount เข้า container (อ่านอย่างเดียว). ถ้าไม่มี ให้สร้างจาก Admin Panel แล้ว copy ออกมา หรือเขียน PHP return array ตามตัวอย่างในหัวข้อ 4 (Deploy Ubuntu ติดตั้งตรง)

### 3.5 เครื่องพิมพ์ (CUPS บน host)

ทั้ง Ubuntu และ Pi compose **mount socket ของ CUPS** จาก host:

```yaml
volumes:
  - /var/run/cups/cups.sock:/var/run/cups/cups.sock
```

ดังนั้นบน host ต้อง:

1. ติดตั้งและรัน CUPS
2. เพิ่มเครื่องพิมพ์ (lpadmin หรือเว็บ :631)
3. ใน `config/my.config.inc.php` ตั้ง `commands.print` เป็น `lp -d <ชื่อปริ้น> -o fit-to-page %s` (ชื่อจาก `lpstat -p`)

ถ้าไม่ใช้ฟีเจอร์พิมพ์ สามารถลบบรรทัด mount socket ออกจาก compose ได้ และปิด `print.enabled` ใน config

### 3.6 อัปเดต image

```bash
cd /path/to/photobooth
git pull

# Ubuntu
docker compose -f docker-compose.production.ubuntu.yml build --no-cache
docker compose -f docker-compose.production.ubuntu.yml up -d

# หรือ Pi
docker compose -f docker-compose.production.pi.yml build --no-cache
docker compose -f docker-compose.production.pi.yml up -d
```

ข้อมูลใน volume ไม่หาย — เฉพาะ code ใน image เปลี่ยน

---

## 4. Deploy บน Ubuntu (ติดตั้งตรง)

### 4.1 สิ่งที่เตรียม

- **ฮาร์ดแวร์:** PC หรือเซิร์ฟเวอร์ที่รัน Ubuntu 24.04 LTS (หรือ 22.04 LTS)
- **เครือข่าย:** LAN ให้อุปกรณ์ผู้ใช้เข้าได้ (หรือ WiFi)
- **กล้อง:** USB webcam หรือ gphoto2 (DSLR)
- **เครื่องพิมพ์ (ถ้าใช้):** ต่อ USB กับเครื่อง Ubuntu

### 4.2 อัปเดตระบบและติดตั้งแพ็กเกจฐาน

```bash
sudo apt update && sudo apt full-upgrade -y
sudo apt install -y git curl wget software-properties-common ca-certificates
```

### 4.3 ติดตั้ง PHP 8.4

Ubuntu 24.04 มี PHP 8.3 ใน repo หลัก ถ้าต้องการ 8.4 ใช้ PPA หรือ Sury:

```bash
# ตัวเลือก A: ใช้ PHP จาก Ubuntu (ถ้าเวอร์ชันใน repo เพียงพอ เช่น 8.3)
sudo apt install -y php php-cli php-gd php-xml php-zip php-mbstring php-curl php-fileinfo libapache2-mod-php

# ตัวเลือก B: PHP 8.4 จาก ondrej/php (ถ้าต้องการ 8.4 แน่นอน)
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update
sudo apt install -y php8.4 php8.4-cli php8.4-gd php8.4-xml php8.4-zip php8.4-mbstring php8.4-curl php8.4-fpm libapache2-mod-php8.4
```

ตรวจสอบ:

```bash
php -v   # ต้อง >= 8.1 แนะนำ 8.4
php -m | grep -E "gd|mbstring|xml|zip|curl|fileinfo"
```

### 3.4 ติดตั้ง Apache และเปิด mod_rewrite

```bash
sudo apt install -y apache2
sudo a2enmod rewrite headers
sudo systemctl enable apache2
sudo systemctl start apache2
```

### 3.5 ติดตั้ง Node.js 20 (สำหรับ build เท่านั้น)

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
node -v   # v20.x
npm -v
```

### 3.6 ติดตั้ง Composer, ffmpeg, CUPS และเครื่องมืออื่นๆ

```bash
# Composer (รันในฐานะผู้ใช้ที่ใช้ deploy)
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Dependencies สำหรับ Photobooth
sudo apt install -y ffmpeg cups gphoto2 libimage-exiftool-perl rsync
sudo systemctl enable cups
sudo systemctl start cups
```

### 3.7 โคลนโปรเจกต์และ build

```bash
# สมมติใช้ /var/www/html เป็น root ของเว็บ
sudo mkdir -p /var/www
cd /var/www
sudo git clone <repo-url> html
cd /var/www/html
```

ถ้า repo ต้องการ branch เฉพาะ:

```bash
sudo git checkout main
# หรือ sudo git checkout <branch-name>
```

ติดตั้ง PHP dependencies และ build frontend (รันด้วย user ที่จะเป็นเจ้าของไฟล์ เช่น `www-data`):

```bash
sudo chown -R www-data:www-data /var/www/html
cd /var/www/html

sudo -u www-data composer install --no-dev --no-interaction
sudo -u www-data npm ci
sudo -u www-data npm run build
```

### 3.8 สิทธิ์โฟลเดอร์ data, private, var

```bash
cd /var/www/html
sudo mkdir -p data/images data/thumbs data/print data/qr data/temp private var
sudo chown -R www-data:www-data data private var
sudo chmod -R 775 data private var
```

### 3.9 กำหนดค่า Apache VirtualHost

```bash
sudo nano /etc/apache2/sites-available/photobooth.conf
```

เนื้อหาแบบย่อ (ปรับ path ถ้าไม่ใช้ `/var/www/html`):

```apache
<VirtualHost *:80>
    ServerName photobooth.local
    DocumentRoot /var/www/html

    <Directory /var/www/html>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/photobooth_error.log
    CustomLog ${APACHE_LOG_DIR}/photobooth_access.log combined
</VirtualHost>
```

เปิดใช้ site และปิด default:

```bash
sudo a2ensite photobooth.conf
sudo a2dissite 000-default.conf
sudo systemctl reload apache2
```

### 3.10 กลุ่มผู้ใช้สำหรับกล้องและเครื่องพิมพ์

```bash
sudo gpasswd -a www-data plugdev
sudo gpasswd -a www-data video
sudo gpasswd -a www-data lp
sudo gpasswd -a www-data lpadmin
```

ถ้าใช้ gphoto2 กับ DSLR อาจต้อง rule udev หรือสิทธิ์เพิ่ม — ดูเอกสารของ distro/กล้อง

### 3.11 เพิ่มเครื่องพิมพ์ใน CUPS (ถ้าใช้พิมพ์)

```bash
# เปิดเว็บ CUPS (จากเครื่องเดียวกัน)
# http://localhost:631 → Administration → Add Printer
# หรือใช้คำสั่ง:
lpinfo -v
sudo lpadmin -p PRINTER_NAME -E -v <uri> -m everywhere
lpstat -p
```

### 3.12 ไฟล์ config ของ Photobooth

สร้างหรือแก้ไข `config/my.config.inc.php` (ไม่ commit ไฟล์นี้):

```bash
sudo -u www-data cp config/my.config.inc.php config/my.config.inc.php.bak 2>/dev/null || true
sudo nano /var/www/html/config/my.config.inc.php
```

ตัวอย่างขั้นต่ำสำหรับ production บน Ubuntu:

```php
<?php
return [
    'webserver' => [
        'url' => 'http://192.168.1.100',  // ใช้ IP หรือ domain จริง
    ],
    'qr' => [
        'url' => 'http://192.168.1.100/view.php?image=',
    ],
    'preview' => [
        'mode' => 'device_cam',  // หรือ 'gphoto2' ถ้าใช้ DSLR
    ],
    'print' => [
        'enabled' => true,
        'from_result' => true,
        'from_gallery' => true,
    ],
    'commands' => [
        'print' => 'lp -d PRINTER_NAME -o fit-to-page %s',  // ใช้ชื่อจาก lpstat -p
    ],
];
```

บันทึกแล้วตรวจสอบสิทธิ์:

```bash
sudo chown www-data:www-data /var/www/html/config/my.config.inc.php
```

### 3.13 ตรวจสอบครั้งสุดท้ายและรีสตาร์ต

```bash
php -m | grep -E "gd|zip|mbstring|xml|curl"
ffmpeg -version | head -1
lpstat -p
sudo systemctl status apache2
sudo systemctl status cups
```

เปิดเบราว์เซอร์จากอุปกรณ์ใน LAN: `http://<IP ของ Ubuntu>`

---

## 4. Deploy บน Raspberry Pi (ละเอียด)

### 4.1 ฮาร์ดแวร์และ OS ที่แนะนำ

| รายการ | แนะนำ |
|--------|--------|
| **บอร์ด** | Raspberry Pi 5 (4GB RAM ขึ้นไป) หรือ Pi 4 (4GB/8GB) |
| **OS** | Raspberry Pi OS 64-bit (Bookworm หรือใหม่กว่า) — แบบ Lite หรือ Desktop ตามต้องการ |
| **Storage** | **USB SSD หรือ NVMe** (แนะนำสำหรับรันทั้งวัน ลดการเขียน SD และเพิ่มความเร็ว) หรือ SD Card Class 10/A2 ขนาด 32GB ขึ้นไป |
| **การระบายความร้อน** | **สำคัญมาก** —  heatsink + พัดลม หรือเคสที่มีพัดลม เพื่อไม่ให้ thermal throttle ตลอดวัน |
| **แหล่งจ่าย** | Pi 5: **5V 5A (USB-C)**; Pi 4: 5V 3A อย่างน้อย |
| **กล้อง** | Pi Camera Module 3 / 3+ หรือ USB webcam หรือ gphoto2 (DSLR) |
| **เครื่องพิมพ์** | USB ต่อกับ Pi โดยตรง |

### 4.2 Boot จาก USB (แนะนำสำหรับรันทั้งวัน)

ลดการเขียน SD และยืดอายุการใช้งาน:

1. อัปเดต EEPROM และ firmware ล่าสุด
2. ใช้ Raspberry Pi Imager เลือก OS แล้วเขียนลง **USB SSD** (ไม่ใช่ SD)
3. ใส่ USB SSD แล้ว boot — Pi 5/4 รองรับ boot จาก USB โดยตรง

หรือใช้ SD เป็น boot แล้ว rootfs อยู่บน USB — ดูเอกสาร Raspberry Pi

### 4.3 อัปเดตระบบและติดตั้งแพ็กเกจ

```bash
sudo apt update && sudo apt full-upgrade -y
sudo apt install -y git curl wget
```

### 4.4 ติดตั้ง PHP, Apache และ extensions

Raspberry Pi OS Bookworm มี PHP ใน repo:

```bash
sudo apt install -y apache2 libapache2-mod-php php php-cli \
  php-gd php-xml php-zip php-mbstring php-curl php-fileinfo
```

ถ้าต้องการ PHP 8.4 อาจต้องใช้ Sury PPA (ทดสอบความเข้ากันได้กับ Pi):

```bash
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update
sudo apt install -y php8.4 php8.4-cli php8.4-gd php8.4-xml php8.4-zip php8.4-mbstring php8.4-curl libapache2-mod-php8.4
```

ตรวจสอบ:

```bash
php -v
php -m | grep -E "gd|mbstring|xml|zip|curl"
```

### 4.5 Apache mod_rewrite และเปิดใช้

```bash
sudo a2enmod rewrite headers
sudo systemctl enable apache2
sudo systemctl start apache2
```

### 4.6 Node.js 20 สำหรับ build

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### 4.7 Composer, ffmpeg, CUPS, gphoto2

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

sudo apt install -y ffmpeg cups gphoto2 libimage-exiftool-perl rsync
sudo systemctl enable cups
sudo systemctl start cups
```

### 4.8 โคลนและ build (เหมือน Ubuntu)

```bash
cd /var/www
sudo git clone <repo-url> html
cd /var/www/html
sudo chown -R www-data:www-data /var/www/html

sudo -u www-data composer install --no-dev --no-interaction
sudo -u www-data npm ci
sudo -u www-data npm run build
```

### 4.9 โฟลเดอร์ data, private, var

```bash
cd /var/www/html
sudo mkdir -p data/images data/thumbs data/print data/qr data/temp private var
sudo chown -R www-data:www-data data private var
sudo chmod -R 775 data private var
```

### 4.10 VirtualHost และกลุ่มผู้ใช้

ทำเหมือน Ubuntu: สร้าง `sites-available/photobooth.conf`, a2ensite, a2dissite 000-default, reload

กลุ่มผู้ใช้สำหรับกล้องและเครื่องพิมพ์:

```bash
sudo gpasswd -a www-data plugdev
sudo gpasswd -a www-data video
sudo gpasswd -a www-data lp
sudo gpasswd -a www-data lpadmin
```

### 4.11 Config Photobooth บน Pi

ใช้ IP ของ Pi ใน LAN (หรือ hostname):

```php
'webserver' => [ 'url' => 'http://192.168.1.101' ],
'qr' => [ 'url' => 'http://192.168.1.101/view.php?image=' ],
'preview' => [ 'mode' => 'libcamera' ],  // หรือ device_cam / gphoto2
'print' => [ 'enabled' => true, 'from_result' => true, 'from_gallery' => true ],
'commands' => [ 'print' => 'lp -d PRINTER_NAME -o fit-to-page %s' ],
```

### 4.12 ตรวจอุณหภูมิและ Thermal Throttling (สำคัญสำหรับรันทั้งวัน)

```bash
# ดูอุณหภูมิ (ต้องมี vcgencmd บน Pi)
vcgencmd measure_temp

# ดูสถานะ throttle (0x0 = ไม่ถูก throttle)
vcgencmd get_throttled
```

ถ้า `get_throttled` ไม่ใช่ 0x0 แปลว่ามีการ throttle (ร้อนหรือไฟไม่พอ) — ต้องแก้การระบายความร้อนหรือเปลี่ยนอะแดปเตอร์

### 4.13 ปิดบริการที่ไม่ใช้ (ลด RAM/CPU)

ถ้าไม่ใช้ Bluetooth/Wi-Fi สามารถปิดได้:

```bash
sudo systemctl disable bluetooth
sudo systemctl disable hciuart
# ถ้าใช้ Ethernet อย่างเดียว และไม่ต้องการ WiFi:
# sudo rfkill block wifi
```

---

## 5. ตั้งค่าเพื่อความเสถียร — ระบบไม่ค้าง

### 5.1 PHP (ทั้ง Ubuntu และ Pi)

แก้ไข `php.ini` ที่ Apache/PHP ใช้ (มักอยู่ที่ `/etc/php/8.4/apache2/php.ini` หรือ `/etc/php/8.3/apache2/php.ini`):

```bash
PHP_INI=$(php -r "echo PHP_CONFIG_FILE_SCAN_DIR;")
# หรือหาด้วย: php --ini
sudo nano /etc/php/8.4/apache2/php.ini
```

ค่าที่แนะนำสำหรับ production (ปรับตาม RAM จริง):

```ini
memory_limit = 256M
max_execution_time = 120
max_input_time = 120
upload_max_filesize = 20M
post_max_size = 24M
max_file_uploads = 10

; ลด I/O จาก realpath cache
realpath_cache_size = 4096K
realpath_cache_ttl = 600
```

บน Pi ถ้า RAM น้อย (เช่น 2GB) อาจใช้ `memory_limit = 128M` และลดการเปิดแท็บ/คอลลาจที่ใช้หน่วยความจำสูง

หลังแก้:

```bash
sudo systemctl reload apache2
```

### 5.2 Apache — จำนวน process/thread

ถ้าใช้ MPM prefork (ค่าเริ่มต้นของ Apache กับ mod_php):

```bash
sudo nano /etc/apache2/mods-available/mpm_prefork.conf
```

ตัวอย่าง (ปรับตาม RAM):

```apache
<IfModule mpm_prefork_module>
    StartServers             2
    MinSpareServers          2
    MaxSpareServers          5
    MaxRequestWorkers        50
    MaxConnectionsPerChild   3000
</IfModule>
```

บน Pi (RAM 4GB) อาจใช้ MaxRequestWorkers ประมาณ 20–30 เพื่อไม่ให้ memory เต็ม

รีสตาร์ต Apache หลังแก้ MPM:

```bash
sudo systemctl restart apache2
```

### 5.3 Swap (สำคัญโดยเฉพาะบน Pi)

ป้องกัน Out of Memory เมื่อมีผู้ใช้พร้อมกันหรือสร้าง collage/GIF:

**Ubuntu:**

```bash
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
```

**Raspberry Pi:**

```bash
sudo dphys-swapfile swapoff
sudo nano /etc/dphys-swapfile
# ตั้ง CONF_SWAPSIZE=2048 (MB)
sudo dphys-swapfile setup
sudo dphys-swapfile swapon
```

### 5.4 Log rotation — จำกัดขนาด log

ป้องกัน disk เต็มจาก log:

```bash
sudo nano /etc/logrotate.d/apache2-photobooth
```

ตัวอย่าง:

```
/var/log/apache2/photobooth_*.log {
    daily
    rotate 7
    compress
    delaycompress
    missingok
    notifempty
}
```

### 5.5 ไฟล์ lock การพิมพ์

ถ้ากดพิมพ์แล้วค้าง (ไม่ error แต่ไม่พิมพ์) มักเป็นเพราะ `data/print.lock` ไม่ถูกลบ:

```bash
sudo -u www-data rm -f /var/www/html/data/print.lock
```

สามารถเพิ่ม cron หรือสคริปต์ตรวจลบ lock เก่าที่ค้างเกิน X นาที (ถ้าต้องการ)

### 5.6 ฐานข้อมูลรูป (แกลเลอรี่)

ฐานข้อมูลรูปเป็นไฟล์ JSON ใน `data/` — ถ้ามีรูปจำนวนมากการ rebuild อาจใช้เวลาสักครู่ ทำได้จาก **Admin Panel** (ปุ่ม "สร้างฐานข้อมูลรูปใหม่") หรือเรียก API (ต้อง login ก่อน):

```bash
curl -b cookies.txt "http://localhost/api/rebuildImageDB.php"
```

---

## 6. ความปลอดภัย

### 6.1 Firewall (UFW) — Ubuntu / Pi

```bash
sudo apt install -y ufw
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 80/tcp
sudo ufw allow 22/tcp
sudo ufw enable
sudo ufw status
```

### 6.2 จำกัดการเข้าถึง Admin

- เปิดใช้ Login ใน Photobooth (Admin Panel → Login) และตั้งรหัสผ่านแข็งแรง
- ถ้าไม่ต้องการให้คนนอก LAN เข้า Admin อาจใช้ firewall จำกัดว่าเฉพาะ IP ภายในเท่านั้นที่เข้าพอร์ต 80 ได้ หรือใช้ VPN

### 6.3 อัปเดตความปลอดภัยอัตโนมัติ (Ubuntu)

```bash
sudo apt install -y unattended-upgrades
sudo dpkg-reconfigure -plow unattended-upgrades
```

---

## 7. การอัปเดตและดูแล

### 7.1 อัปเดตโค้ด Photobooth

```bash
cd /var/www/html
sudo -u www-data git fetch origin
sudo -u www-data git pull
sudo -u www-data composer install --no-dev --no-interaction
sudo -u www-data npm ci
sudo -u www-data npm run build
sudo systemctl reload apache2
```

### 7.2 Backup ข้อมูล

โฟลเดอร์ที่ควร backup เป็นประจำ: `data/`, `config/my.config.inc.php`, `private/` (ถ้ามีการอัปโหลด)

```bash
tar -czvf photobooth-data-$(date +%Y%m%d).tar.gz -C /var/www/html data config/my.config.inc.php private
# ย้ายไปที่เก็บ backup (NAS, USB, cloud)
```

### 7.3 ตรวจสอบ disk และ log

```bash
df -h
du -sh /var/www/html/data
tail -100 /var/log/apache2/photobooth_error.log
```

---

## 8. Troubleshooting

### 8.1 ระบบค้างหรือตอบช้า

- **หน่วยความจำ:** `free -h` — ดูใช้ swap เยอะหรือไม่; ปรับ `memory_limit` หรือลด MaxRequestWorkers
- **Raspberry Pi:** `vcgencmd measure_temp` และ `vcgencmd get_throttled` — ถ้าร้อนหรือ throttle ให้เพิ่มการระบายความร้อน
- **Disk I/O:** ใช้ `iostat` หรือ `iotop` ดูว่า disk เต็มหรือช้า; พิจารณาใช้ USB SSD/NVMe แทน SD

### 8.2 พิมพ์ไม่ออก

- ตรวจ CUPS: `lpstat -p`, `lpstat -t`
- ตรวจ lock: `ls -la /var/www/html/data/print.lock` แล้วลบถ้าค้าง: `sudo -u www-data rm /var/www/html/data/print.lock`
- ตรวจคำสั่งใน config: `commands.print` ต้องใช้ชื่อเครื่องพิมพ์จาก `lpstat -p`

### 8.3 กล้องไม่ทำงาน

- ตรวจสิทธิ์กลุ่ม: `groups www-data` ต้องมี `video`, `plugdev`
- USB: ต่อกล้องแล้วรัน `lsusb`, `gphoto2 --auto-detect`
- Pi Camera: เปิด camera ใน `raspi-config` และตรวจว่าใช้ `libcamera` หรือ `device_cam` ตามที่ config

### 8.4 หลังอัปเดตแล้วหน้าเว็บผิดปกติ

- Build frontend ใหม่: `cd /var/www/html && sudo -u www-data npm run build`
- ล้าง cache เบราว์เซอร์หรือเปิดใน private window
- ตรวจ error log: `tail -50 /var/log/apache2/photobooth_error.log`

---

*เอกสารฉบับนี้ออกแบบสำหรับการ deploy Photobooth แบบ production บน Ubuntu และ Raspberry Pi ให้รันทั้งวันโดยระบบไม่ค้าง*
