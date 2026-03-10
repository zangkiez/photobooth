# Photobooth v4 - ระบบถ่ายรูปอัตโนมัติ

![Photobooth Banner](resources/img/logo/banner.png)

ระบบ Photobooth (ฟhotobooth) เป็นเว็บแอปพลิเคชันสำหรับถ่ายรูปอัตโนมัติ รองรับทั้ง Linux และ Windows พัฒนาโดยชุมชน Photobooth Project เหมาะสำหรับใช้ในงานอีเวนต์ต่างๆ เช่น งานแต่งงาน งานวันเกิด หรืองานปาร์ตี้

---

## 📋 สารบัญ

1. [ภาพรวมระบบ](#ภาพรวมระบบ)
2. [ความต้องการของระบบ](#ความต้องการของระบบ)
3. [โครงสร้างโปรเจค](#โครงสร้างโปรเจค)
4. [การตั้งค่าและการใช้งาน](#การตั้งค่าและการใช้งาน)
5. [คำอธิบายไฟล์ตั้งค่า](#คำอธิบายไฟล์ตั้งค่า)
6. [การติดตั้ง](#การติดตั้ง)
7. [การ Deploy: DDEV / Docker Compose / Production](#การ-deploy-ddev--docker-compose--production)
8. [API และการทำงาน](#api-และการทำงาน)
9. [ฟีเจอร์หลัก](#ฟีเจอร์หลัก)

---

## 🎯 ภาพรวมระบบ

Photobooth เป็นระบบถ่ายรูปอัตโนมัติแบบครบวงจร ที่ช่วยให้ผู้ใช้สามารถ:
- ถ่ายรูปเดี่ยวหรือคอลลาจ (หลายรูปในเฟรมเดียว)
- ใส่กรอบรูปและฟิลเตอร์
- พิมพ์รูปทันที
- แชร์รูปผ่าน QR Code
- ส่งรูปทางอีเมล
- ใช้งาน Chroma Key (พื้นหลังสีเขียว)

### ผู้พัฒนา
- **ผู้เริ่มต้น:** Andre Rinas ([GitHub](https://github.com/andreknieriem/photobooth))
- **ผู้พัฒนาต่อ:** Andreas Blaesius (ตั้งแต่ปี 2019)
- **เวอร์ชันปัจจุบัน:** v4.99.0

---

## 💻 ความต้องการของระบบ

### ฮาร์ดแวร์ที่รองรับ

| แพลตฟอร์มฮาร์ดแวร์ | ระบบปฏิบัติการ | กล้องที่รองรับ |
|-------------------|---------------|---------------|
| Raspberry Pi 3/4/5 | Raspberry Pi OS 64-bit (Bookworm/Trixie) | Camera Modules, gphoto2 DSLR, webcam |
| PC ทั่วไป | Debian/Ubuntu | gphoto2 DSLR, webcam |
| PC ทั่วไป | Windows | digiCamControl, webcam |

### ซอฟต์แวร์ที่ต้องการ

| ซอฟต์แวร์ | เวอร์ชันขั้นต่ำ | หมายเหตุ |
|-----------|----------------|----------|
| Node.js | >= v20.15.0 | ทดสอบบน v20 เท่านั้น |
| npm | >= v10.7.0 | - |
| PHP | >= v8.4 | - |
| Web Server | - | Apache หรือ Nginx |

### ส่วนขยาย PHP ที่จำเป็น
- `ext-gd` - ประมวลผลรูปภาพ
- `ext-mbstring` - จัดการข้อความ multibyte
- `ext-xml` - ประมวลผล XML
- `ext-zip` - จัดการไฟล์ ZIP
- `ext-fileinfo` - ตรวจสอบข้อมูลไฟล์

---

## 📁 โครงสร้างโปรเจค

```
photobooth/
│
├── 📁 admin/                   # แผงผู้ดูแลระบบ
│   ├── index.php              # หน้าหลักแผงผู้ดูแล
│   ├── admin_boot.php         # ระบบเริ่มต้นสำหรับแผงผู้ดูแล
│   ├── captureconfig.php      # การตั้งค่าการถ่ายรูป
│   └── wgetcaptureconfig.php  # ดาวน์โหลดการตั้งค่า
│
├── 📁 api/                     # API Endpoints
│   ├── capture.php            # ถ่ายรูป
│   ├── applyEffects.php       # ใส่เอฟเฟกต์
│   ├── collage.php            # สร้างคอลลาจ
│   ├── print.php              # พิมพ์รูป
│   ├── deletePhoto.php        # ลบรูป
│   ├── gallery.php            # จัดการแกลเลอรี่
│   ├── qrcode.php             # สร้าง QR Code
│   ├── sendPic.php            # ส่งรูปทางอีเมล
│   └── ...                    # API อื่นๆ
│
├── 📁 assets/                  # ไฟล์ต้นฉบับ (Source Assets)
│   ├── 📁 js/                 # JavaScript ต้นฉบับ
│   │   ├── core.js           # ระบบหลัก
│   │   ├── gallery.js        # ระบบแกลเลอรี่
│   │   ├── preview.js        # พรีวิวกล้อง
│   │   ├── chromakeying.js   # ระบบ Chroma Key
│   │   └── admin/            # JS สำหรับแผงผู้ดูแล
│   └── 📁 sass/               # SCSS สำหรับสไตล์
│       ├── framework.scss    # กรอบงานหลัก
│       ├── fonts.scss        # ฟอนต์
│       └── tailwind.admin.scss # สไตล์แผงผู้ดูแล
│
├── 📁 config/                  # ไฟล์การตั้งค่า
│   ├── my.config.inc.php     # การตั้งค่าผู้ใช้ (สำคัญ!)
│   └── tailwind.admin.config.mjs # การตั้งค่า Tailwind CSS
│
├── 📁 data/                    # ข้อมูลรูปภาพ (ต้องมีสิทธิ์เขียน)
│   ├── 📁 images/             # รูปภาพที่ถ่าย
│   ├── 📁 thumbs/             # รูปย่อ
│   ├── 📁 print/              # รูปสำหรับพิมพ์
│   ├── 📁 qr/                 # QR Codes
│   └── 📁 temp/               # ไฟล์ชั่วคราว
│
├── 📁 gallery/                 # แกลเลอรี่แบบสแตนด์อโลน
├── 📁 login/                   # ระบบเข้าสู่ระบบ
├── 📁 manual/                  # คู่มือการใช้งาน
├── 📁 private/                 # ไฟล์ส่วนตัว (รูปกำหนดเอง)
│   ├── 📁 images/
│   │   ├── backgrounds/      # พื้นหลัง
│   │   ├── frames/           # กรอบรูป
│   │   └── keyingBackgrounds/ # พื้นหลัง Chroma Key
│   ├── 📁 fonts/              # ฟอนต์กำหนดเอง
│   └── 📁 videos/             # วิดีโอพื้นหลัง
│
├── 📁 resources/               # ไฟล์ที่คอมไพล์แล้ว
│   ├── 📁 css/                # CSS ที่คอมไพล์
│   ├── 📁 js/                 # JS ที่คอมไพล์
│   ├── 📁 img/                # รูปภาพระบบ
│   └── 📁 lang/               # ไฟล์ภาษา (JSON)
│       ├── th.json           # ภาษาไทย
│       ├── en.json           # ภาษาอังกฤษ
│       └── ...               # ภาษาอื่นๆ
│
├── 📁 src/                     # โค้ด PHP (PSR-4 Autoloading)
│   ├── 📁 Configuration/      # การตั้งค่าและ Validation
│   ├── 📁 Service/            # บริการต่างๆ
│   ├── 📁 Utility/            # เครื่องมือช่วยเหลือ
│   ├── 📁 Enum/               # Enumerations
│   └── 📁 Command/            # คำสั่ง CLI
│
├── 📁 template/                # เทมเพลต PHP
│   └── 📁 components/         # ส่วนประกอบ UI
│       ├── stage.start.php   # หน้าจอเริ่มต้น
│       ├── gallery.php       # แกลเลอรี่
│       └── ...
│
├── 📁 tools/                   # เครื่องมือสำหรับนักพัฒนา
│   ├── php-cs-fixer/         # จัดรูปแบบโค้ด
│   ├── phplint/              # ตรวจสอบ syntax
│   ├── phpstan/              # Static analysis
│   └── phpunit/              # Unit testing
│
├── 📁 var/                     # ไฟล์ระบบ (ล็อก, รัน)
├── 📁 vendor/                  # Dependencies PHP (Composer)
├── 📁 node_modules/            # Dependencies Node.js
│
├── 📄 index.php               # หน้าหลัก
├── 📄 view.php                # หน้าดูรูป
├── 📄 composer.json           # Dependencies PHP
├── 📄 package.json            # Dependencies Node.js
├── 📄 gulpfile.mjs            # สคริปต์สร้าง (Build)
├── 📄 docker-compose.yml      # Docker Compose
├── 📄 Dockerfile              # Docker Image
└── 📄 install-photobooth.sh   # สคริปต์ติดตั้ง
```

---

## ⚙️ การตั้งค่าและการใช้งาน

### ไฟล์การตั้งค่าหลัก

#### 1. `config/my.config.inc.php` - การตั้งค่าผู้ใช้

ไฟล์นี้เป็นการตั้งค่าหลักที่ผู้ใช้สามารถปรับแต่งได้ ประกอบด้วย:

##### 🔧 การตั้งค่าทั่วไป (General)
```php
'ui' => [
    'language' => 'th',              // ภาษาอินเตอร์เฟซ (th = ไทย)
    'local_timezone' => 'Asia/Bangkok', // เขตเวลา
],
'adminpanel' => [
    'view' => 'expert',              // โหมดแผงผู้ดูแล (basic/expert)
],
```

##### 🎨 การตั้งค่าหน้าจอเริ่มต้น (Start Screen)
```php
'start_screen' => [
    'title' => 'Photobooth',         // หัวข้อหน้าจอเริ่มต้น
    'subtitle' => 'ถ่ายรูปกันเถอะ!', // คำบรรยาย
],
'logo' => [
    'enabled' => true,               // แสดงโลโก้
    'path' => 'resources/img/logo/logo.png', // เส้นทางโลโก้
    'position' => 'center',          // ตำแหน่งโลโก้
],
'event' => [
    'enabled' => false,              // โหมดอีเวนต์
    'textLeft' => 'งาน',             // ข้อความซ้าย
    'textRight' => 'แต่งงาน',        // ข้อความขวา
    'symbol' => 'fa-heart',          // สัญลักษณ์ตรงกลาง
],
```

##### 📸 การตั้งค่าการถ่ายรูป (Pictures)
```php
'picture' => [
    'enabled' => true,               // เปิดใช้ถ่ายรูป
    'cntdwn_time' => 5,              // เวลานับถอยหลัง (วินาที)
    'cheese_time' => 1,              // เวลาแสดง "ยิ้มหน่อย!"
    'frame' => 'resources/img/frames/frame.png', // กรอบรูป
    'take_frame' => true,            // ใช้กรอบรูป
    'polaroid_effect' => false,      // เอฟเฟกต์โพลารอยด์
    'rotation' => 0,                 // หมุนรูป (องศา)
    'flip' => 'off',                 // พลิกรูป (off/horizontal/vertical)
],
```

##### 🎞️ การตั้งค่าคอลลาจ (Collage)
```php
'collage' => [
    'enabled' => true,               // เปิดใช้คอลลาจ
    'layout' => '2+2-1',             // รูปแบบคอลลาจ
    'cntdwn_time' => 3,              // เวลานับถอยหลังระหว่างรูป
    'continuous' => false,           // ถ่ายต่อเนื่องไม่หยุด
    'allow_selection' => true,       // อนุญาตเลือกเลย์เอาต์
    'layouts_enabled' => [           // เลย์เอาต์ที่เลือกได้
        '2+2-1', '2+2-2', '1+3-1', '2x4-1'
    ],
    'limit' => 4,                    // จำนวนรูปสูงสุดในคอลลาจ
    'frame' => 'resources/img/frames/collage-frame.png',
    'background' => 'resources/img/background.png',
    'polaroid_effect' => true,       // เอฟเฟกต์โพลารอยด์
],
```

##### 🎨 การตั้งค่าสี (Colors)
```php
'colors' => [
    'primary' => '#106a37',          // สีหลัก
    'secondary' => '#529146',        // สีรอง
    'background' => '#ffffff',       // สีพื้นหลัง
    'button' => '#106a37',           // สีปุ่ม
    'font' => '#333333',             // สีตัวอักษร
],
```

##### 🔐 การตั้งค่าความปลอดภัย (Security)
```php
'login' => [
    'enabled' => true,               // เปิดใช้ระบบล็อกอิน
    'username' => 'admin',           // ชื่อผู้ใช้
    'password' => '...',             // รหัสผ่าน (แฮช)
    'pin' => '1234',                 // PIN (4 หลัก)
    'keypad' => true,                // ใช้คีย์แพด
],
'protect' => [
    'admin' => true,                 // ป้องกันแผงผู้ดูแล
    'index' => false,                // ป้องกันหน้าจอเริ่มต้น
    'manual' => false,               // ป้องกันคู่มือ
],
```

##### 🖨️ การตั้งค่าการพิมพ์ (Print)
```php
'print' => [
    'enabled' => true,               // เปิดใช้การพิมพ์
    'auto' => false,                 // พิมพ์อัตโนมัติ
    'auto_delay' => 1000,            // ดีเลย์พิมพ์อัตโนมัติ (ms)
    'frame' => 'resources/img/frames/print-frame.png',
    'qrcode' => true,                // พิมพ์ QR Code
    'limit' => 0,                    // จำกัดจำนวนพิมพ์ (0 = ไม่จำกัด)
    'cmd' => 'lp -o landscape -o fit-to-page %s', // คำสั่งพิมพ์
],
```

##### 📧 การตั้งค่าอีเมล (Mail)
```php
'mail' => [
    'enabled' => true,               // เปิดใช้อีเมล
    'host' => 'smtp.gmail.com',      // SMTP Host
    'port' => 587,                   // SMTP Port
    'secure' => 'tls',               // การเข้ารหัส (tls/ssl)
    'username' => 'your@email.com',
    'password' => 'your-password',
    'fromAddress' => 'photobooth@example.com',
    'fromName' => 'Photobooth',
    'subject' => 'รูปของคุณจาก Photobooth',
],
```

##### 📱 การตั้งค่า QR Code
```php
'qr' => [
    'enabled' => true,               // เปิดใช้ QR Code
    'url' => 'view.php?image=',      // URL สำหรับดูรูป
    'ecLevel' => 'M',                // ระดับ Error Correction (L/M/Q/H)
    'position' => 'bottom-left',     // ตำแหน่ง QR Code
],
```

##### 🎥 การตั้งค่าพรีวิว (Preview)
```php
'preview' => [
    'mode' => 'device_cam',          // โหมดพรีวิว (none/device_cam/url)
    'camera_mode' => 'user',         // กล้องหน้า/หลัง (user/environment)
    'flip' => 'off',                 // พลิกภาพ
    'rotation' => 0,                 // หมุนภาพ
    'showFrame' => true,             // แสดงกรอบบนพรีวิว
    'camTakesPic' => false,          // ใช้กล้องถ่ายแทนคำสั่ง
],
```

##### 🌈 การตั้งค่า Chroma Key (Green Screen)
```php
'keying' => [
    'enabled' => true,               // เปิดใช้ Chroma Key
    'variant' => 'seriouslyjs',      // อัลกอริทึม (seriouslyjs/marvinj)
    'size' => 'M',                   // ขนาด (S/M/L/XL)
    'private_backgrounds' => true,   // ใช้พื้นหลังส่วนตัว
],
'chromaCapture' => [
    'enabled' => false,              // ใช้ Chroma Capture เป็นหน้าเริ่มต้น
],
```

##### 🔊 การตั้งค่าเสียง (Sound)
```php
'sound' => [
    'enabled' => true,               // เปิดใช้เสียง
    'voice' => 'th',                 // ภาษาเสียง
    'countdown' => true,             // เสียงนับถอยหลัง
    'cheese' => true,                // เสียง "ยิ้มหน่อย!"
],
```

##### 📺 การตั้งค่า Screensaver
```php
'screensaver' => [
    'enabled' => true,               // เปิดใช้ Screensaver
    'timeout' => 5,                  // หน่วง (นาที) ก่อนเปิด
    'mode' => 'gallery',             // โหมด (image/video/gallery)
    'text' => 'แตะหน้าจอเพื่อเริ่ม', // ข้อความ
],
```

##### 🎨 การตั้งค่าฟิลเตอร์ (Filters)
```php
'filters' => [
    'enabled' => true,               // เปิดใช้ฟิลเตอร์
    'defaults' => 'plain',           // ฟิลเตอร์เริ่มต้น
    'disabled' => ['antique'],       // ฟิลเตอร์ที่ปิดใช้งาน
],
```

---

## 🔧 คำอธิบายไฟล์ตั้งค่า

### ไฟล์ตั้งค่า Build และ Development

#### `package.json` - Dependencies Node.js
```json
{
  "name": "photobooth",
  "version": "4.99.0",
  "engines": {
    "node": ">=20.15.0",
    "npm": ">=9.6.5"
  }
}
```

**คำสั่งสำคัญ:**
- `npm run build` - สร้างโปรเจคสำหรับ production
- `npm run build:gulp` - คอมไพล์ Sass และ JS
- `npm run watch:gulp` - ติดตามการเปลี่ยนแปลงไฟล์
- `npm run eslint` - ตรวจสอบโค้ด JS
- `npm run composer:install` - ติดตั้ง dependencies PHP

#### `composer.json` - Dependencies PHP
```json
{
  "require": {
    "php": "^8.4",
    "endroid/qr-code": "^6.0",      // สร้าง QR Code
    "phpmailer/phpmailer": "^7.0",  // ส่งอีเมล
    "league/flysystem": "^3.29",    // จัดการไฟล์
    "monolog/monolog": "^3.5",      // บันทึกล็อก
    "symfony/console": "^8.0"       // คำสั่ง CLI
  }
}
```

#### `gulpfile.mjs` - สคริปต์สร้าง (Build)

| Task | คำอธิบาย |
|------|---------|
| `sass` | คอมไพล์ SCSS เป็น CSS |
| `tailwind-admin` | สร้าง CSS สำหรับแผงผู้ดูแล |
| `js` | คอมไพล์ JavaScript |
| `js-admin` | รวมไฟล์ JS สำหรับแผงผู้ดูแล |
| `default` | รันทุก task พร้อมสร้าง revisions |
| `watch` | ติดตามการเปลี่ยนแปลง |

---

## 🚀 การติดตั้ง

### วิธีที่ 1: ใช้สคริปต์ติดตั้ง (แนะนำ)

```bash
# ดาวน์โหลดสคริปต์ติดตั้ง
wget https://raw.githubusercontent.com/PhotoboothProject/photobooth/main/install-photobooth.sh

# ให้สิทธิ์และรัน
chmod +x install-photobooth.sh
sudo ./install-photobooth.sh
```

### วิธีที่ 2: ใช้ Docker

```bash
# สร้างและรัน container
docker compose up --build

# หรือใช้ DDEV
ddev start
ddev build
```

### วิธีที่ 3: ติดตั้งด้วยตนเอง

```bash
# 1. โคลนโปรเจค
git clone https://github.com/PhotoboothProject/photobooth.git
cd photobooth

# 2. ติดตั้ง PHP dependencies
composer install

# 3. ติดตั้ง Node.js dependencies
npm install

# 4. สร้าง assets
npm run build

# 5. ตั้งค่าสิทธิ์โฟลเดอร์
sudo chown -R www-data:www-data data/
sudo chown -R www-data:www-data private/
sudo chown -R www-data:www-data var/
```

---

## 🚢 การ Deploy: DDEV / Docker Compose / Production

ถ้าคุณ **develop บน Mac** และจะ **รันจริงบน Raspberry Pi 5** แนะนำให้อ่านคู่มือแยก:

- **[DEPLOY.md](DEPLOY.md)** — อธิบายความต่างระหว่าง **DDEV** (dev บน Mac), **Docker Compose** (ทดสอบบน Mac/PC), และ **Production** (รันบน Raspberry Pi 5) พร้อมวิธี setup แต่ละแบบ

สรุปสั้นๆ:

| แบบ | ใช้เมื่อ | สิ่งที่ต้องมี |
|-----|----------|----------------|
| **DDEV** | พัฒนาบน Mac ไม่อยากลง PHP/Node โดยตรง | Docker Desktop + DDEV |
| **Docker Compose** | ทดสอบ build + รันแบบใกล้เคียง production | Docker Desktop |
| **Production (Pi 5)** | รันจริงในงาน ต่อกล้อง/เครื่องพิมพ์ | Raspberry Pi 5 + Raspberry Pi OS |

---

## 🌐 API และการทำงาน

### API Endpoints หลัก

| Endpoint | คำอธิบาย | Method |
|----------|---------|--------|
| `/api/capture.php` | ถ่ายรูป | POST |
| `/api/applyEffects.php` | ใส่เอฟเฟกต์ | POST |
| `/api/print.php` | พิมพ์รูป | POST |
| `/api/deletePhoto.php` | ลบรูป | POST |
| `/api/gallery.php` | ดึงข้อมูลแกลเลอรี่ | GET |
| `/api/qrcode.php` | สร้าง QR Code | GET |
| `/api/sendPic.php` | ส่งรูปทางอีเมล | POST |
| `/api/settings.php` | ดึง/บันทึกการตั้งค่า | GET/POST |

### โครงสร้างฐานข้อมูล

```
data/
├── images/          # รูปภาพต้นฉบับ
├── thumbs/          # รูปย่อ
├── print/           # รูปสำหรับพิมพ์
├── qr/              # QR Codes
└── database.txt     # ฐานข้อมูลรูป
```

### ระบบภาษา

ไฟล์ภาษาอยู่ใน `resources/lang/` รองรับ 20+ ภาษา:
- `th.json` - ภาษาไทย
- `en.json` - ภาษาอังกฤษ
- `de.json` - ภาษาเยอรมัน
- `fr.json` - ภาษาฝรั่งเศส
- และอื่นๆ

---

## ✨ ฟีเจอร์หลัก

### 📸 การถ่ายรูป
- ถ่ายรูปเดี่ยวพร้อมนับถอยหลัง
- ถ่ายคอลลาจหลายรูป (2-4 รูป)
- ใส่กรอบรูปแบบกำหนดเอง
- ใส่ข้อความบนรูป
- ฟิลเตอร์รูปภาพหลายแบบ
- เอฟเฟกต์โพลารอยด์

### 🎨 การตกแต่ง
- **Chroma Key** - ลบพื้นหลังสีเขียว/น้ำเงิน
- **Magic Greenscreen** - ลบพื้นหลังด้วย AI
- **ฟิลเตอร์** - กรองรูปแบบต่างๆ
- **กรอบรูป** - กรอบแบบกำหนดเอง

### 🖨️ การพิมพ์
- พิมพ์ทันทีหลังถ่าย
- พิมพ์จากแกลเลอรี่
- ใส่กรอบพิมพ์แยก
- พิมพ์ QR Code บนรูป
- ใส่ข้อความบนรูปพิมพ์

### 📤 การแชร์
- **QR Code** - สแกนเพื่อดาวน์โหลด
- **อีเมล** - ส่งรูปทางอีเมล
- **ดาวน์โหลด** - ดาวน์โหลดรูปโดยตรง

### 🎮 การควบคุม
- **Hardware Button** - ปุ่มกดภายนอก
- **Remote Buzzer** - ควบคุมผ่าน HTTP
- **Rotary Encoder** - หมุนเลือกเมนู
- **Keyboard** - ควบคุมด้วยแป้นพิมพ์

### 🖥️ การแสดงผล
- **แกลเลอรี่** - ดูรูปทั้งหมด
- **Slideshow** - สไลด์โชว์รูป
- **Screensaver** - หน้าจอพัก
- **Live Preview** - พรีวิวก่อนถ่าย

---

## 🔒 ความปลอดภัย

> ⚠️ **คำเตือน:** Photobooth ไม่ได้รับการออกแบบให้ใช้งานบนอินเทอร์เน็ตสาธารณะ ไม่ควรเปิดให้เข้าถึงจากภายนอกโดยไม่มีการป้องกันเพิ่มเติม

### มาตรการความปลอดภัยที่มี
- CSRF Protection
- Session Security (HttpOnly, Secure, SameSite)
- Login Protection
- IP Whitelist
- Admin Panel Protection

---

## 📞 การสนับสนุน

- **เว็บไซต์:** https://photoboothproject.github.io
- **FAQ:** https://photoboothproject.github.io/faq/
- **Telegram:** https://t.me/PhotoboothGroup
- **GitHub:** https://github.com/PhotoboothProject/photobooth
- **แปลภาษา:** https://crowdin.com/project/photobooth

---

## 📄 ลิขสิทธิ์

Photobooth ใช้ลิขสิทธิ์ MIT License

---

## 🙏 ขอบคุณ

ขอบคุณผู้ร่วมพัฒนาและชุมชนทุกท่านที่มีส่วนช่วยพัฒนาโปรเจคนี้

---

*เอกสารฉบับนี้จัดทำขึ้นเพื่ออธิบายโครงสร้างและการใช้งาน Photobooth v4 สำหรับผู้ใช้ภาษาไทย*
