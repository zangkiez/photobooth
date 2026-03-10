# การ Deploy Photobooth: DDEV / Docker Compose / Production (Raspberry Pi 5)

คู่มือนี้อธิบายความต่างระหว่างการรัน Photobooth แบบ **DDEV**, **Docker Compose** และ **Production (รันจริงบน Raspberry Pi 5)** รวมถึงวิธี setup แต่ละแบบ

**สถานการณ์ของคุณ:**
- **Dev:** ทำบน Mac (ผ่าน DDEV)
- **Production:** รันจริงบน Raspberry Pi 5

---

## การเปลี่ยนแปลงล่าสุด (10 มีนาคม 2026)

### 🐛 Bug Fixes
- **Filter slide กลับหน้าแรก:** แก้ไข timeout ไม่ reload page ขณะที่ filter nav เปิดอยู่หรือกำลัง process
- **ปรับ filter ได้แค่ครั้งแรก:** แก้ให้ใช้ไฟล์ต้นฉบับ (captureBasename) แทน variant สำหรับ filter reprocess ครั้งถัดไป

### ✨ ฟีเจอร์ใหม่ — Collage Slideshow GIF
- หลังถ่าย collage เสร็จ ระบบจะสร้าง **animated GIF** จากรูปเดี่ยวทุกใบอัตโนมัติ
- GIF ถูกเพิ่มเข้า gallery ทันที
- ระยะเวลา: 3 รูป = 5 วิ, วน 2 รอบ; ทุกรูปเพิ่ม = +1 วิ
- **ต้องการเพียง PHP GD** (มีอยู่แล้วใน stack ปัจจุบัน) — ไม่ต้องติดตั้ง ffmpeg เพิ่ม
- เปิดใช้ได้ที่: Admin → Collage → **"Create animated GIF slideshow after collage"**

### ไฟล์ที่เปลี่ยนแปลง
| ไฟล์ | รายละเอียด |
|------|------------|
| `src/Utility/GifEncoder.php` | ✨ ใหม่ — Pure-PHP animated GIF encoder |
| `src/Configuration/Section/CollageConfiguration.php` | เพิ่ม config `collage[slideshow_enabled]` |
| `lib/configsetup.inc.php` | เพิ่ม checkbox ใน Admin panel |
| `api/applyEffects.php` | สร้าง GIF slideshow หลัง collage process |
| `resources/js/core.js` | รับ `data.slideshow` แล้วเพิ่มลง gallery + แก้ filter bugs |
| `resources/lang/en.json` + `th.json` | เพิ่ม translation key ใหม่ |

---

## สรุปเปรียบเทียบ

| หัวข้อ | DDEV | Docker Compose | Production (Raspberry Pi 5) |
|--------|------|----------------|-----------------------------|
| **ใช้เมื่อไหร่** | พัฒนาบน Mac (หรือ PC) ไม่อยากลง PHP/Node บนเครื่อง | ทดสอบแบบ “ใกล้เคียง production” บน Mac/PC หรือเซิร์ฟเวอร์ | รันจริงในงาน (งานอีเวนต์, โฟโต้บูธจริง) |
| **รันอยู่ที่ไหน** | Container บน Mac (ผ่าน Docker) | Container บน Mac/PC หรือเซิร์ฟเวอร์ | ลงโดยตรงบน Raspberry Pi (ไม่มี container) |
| **กล้อง / เครื่องพิมพ์** | ใช้ได้จำกัด (ต้อง bind device เข้า container) | ใช้ได้จำกัด (เช่นกัน) | ใช้ได้เต็มที่ (กล้อง Pi, USB, เครื่องพิมพ์ผ่าน CUPS) |
| **ความเร็ว / ทรัพยากร** | เร็วสำหรับ dev | ใช้ RAM/CPU ตามที่กำหนดใน Docker | ใช้ทรัพยากร Pi โดยตรง เหมาะกับฮาร์ดแวร์จริง |
| **การอัปเดต** | `git pull` แล้ว `ddev restart` / `ddev build` | `git pull` แล้ว build image ใหม่ | `git pull` แล้ว `npm run build` บน Pi |
| **เหมาะกับ** | Dev ทุกวันบน Mac | ทดสอบ flow ทั้งระบบก่อนเอาไปลง Pi | ใช้งานจริงในงาน |

---

## 1. DDEV (พัฒนาบน Mac)

### คืออะไร
- **DDEV** = เครื่องมือสร้างสภาพแวดล้อม dev แบบ container บนเครื่องคุณ (Mac / Windows WSL2 / Linux)
- รัน PHP, Apache, Node ฯลฯ อยู่ใน container ไม่ต้องลง PHP/Node เวอร์ชันเฉพาะโดยตรงบน Mac
- โค้ดอยู่บน Mac แก้แล้วเห็นผลทันที (sync กับ container)

### ใช้เมื่อไหร่
- ใช้ **พัฒนาบน Mac** เป็นหลัก
- ไม่อยากลง PHP 8.4 / Node 20 บน Mac โดยตรง
- ต้องการคำสั่งเดียว (เช่น `ddev start`) แล้วได้เว็บ + build พร้อมใช้

### สิ่งที่ต้องติดตั้งบน Mac
- **Docker Desktop** (หรือ Docker Engine บน Linux)
- **DDEV**
  - macOS: `brew install ddev`
  - หรือดู [การติดตั้ง DDEV](https://ddev.readthedocs.io/en/stable/users/install/ddev-installation/)

### วิธี Setup

```bash
# 1. โคลนโปรเจกต์ (ถ้ายังไม่มี)
cd ~/projects
git clone <repo-url> photobooth
cd photobooth

# 2. สตาร์ท DDEV (ครั้งแรกจะ build image และติดตั้ง npm + build ให้)
ddev start

# 3. ถ้า build ยังไม่ chạy หรือแก้ package/frontend
ddev build
```

### การเข้าใช้งาน
- **HTTP:** http://photobooth.ddev.site:9080
- **HTTPS:** https://photobooth.ddev.site:9443

### คำสั่งที่ใช้บ่อย
- `ddev start` / `ddev stop` / `ddev restart` — เปิด/ปิด/รีสตาร์ท
- `ddev build` — build frontend (npm install + npm run build) ใหม่
- `ddev composer <cmd>` — รัน Composer ใน container
- `ddev npm <cmd>` — รัน npm ใน container
- `ddev qa` — รัน QA (lint, phpstan, phpunit ฯลฯ)
- `ddev ssh` — เข้า shell ใน container

### ข้อจำกัด
- **กล้อง / เครื่องพิมพ์:** โดยปกติไม่ต่อกับกล้องจริงหรือเครื่องพิมพ์ของ Mac โดยตรง (ถ้าจะใช้ต้อง bind device ซึ่งซับซ้อน)
- เหมาะสำหรับ **พัฒนา UI, ฟีเจอร์, API** ไม่ได้เน้นทดสอบฮาร์ดแวร์จริง

---

## 2. Docker Compose (ทดสอบบน Mac หรือเซิร์ฟเวอร์)

### คืออะไร
- รัน Photobooth ใน **Docker container** ตาม `docker-compose.yml` และ `Dockerfile`
- ไม่มี DDEV — ใช้แค่ Docker + Docker Compose
- image ถูก build จาก Dockerfile (PHP 8.4, Apache, Node, gphoto2 ฯลฯ)

### ใช้เมื่อไหร่
- อยากทดสอบว่า **build และรันได้** แบบ “ใกล้เคียง production” บน Mac
- ยังไม่เอาไปลง Raspberry Pi หรืออยากมีสภาพแวดล้อมเดียวกับใน CI/CD

### สิ่งที่ต้องติดตั้งบน Mac
- **Docker Desktop** (มี Docker Compose รวมอยู่แล้ว)

### วิธี Setup

```bash
cd /path/to/photobooth

# Build และรัน (พอร์ต 80 และ 443)
docker compose up --build
```

- ครั้งแรกจะ build image และรันนานหน่อย
- หลังรันแล้วเข้า: **http://localhost** (พอร์ต 80) หรือ **https://localhost** (พอร์ต 443 ถ้ามีตั้งค่า)

### การอัปเดต
```bash
git pull
docker compose up --build
```

### ข้อจำกัด
- **กล้อง / เครื่องพิมพ์:** เช่นเดียวกับ DDEV — ไม่ได้ต่อกับกล้องหรือเครื่องพิมพ์จริงของ Mac โดยตรง
- ใช้ทดสอบ **แอปและ build** มากกว่าทดสอบฮาร์ดแวร์

---

## 3. Production บน Raspberry Pi 5 (รันจริง ไม่ใช้ Docker)

### คืออะไร
- ติดตั้ง **โดยตรงบน Raspberry Pi 5** (ลง PHP, Node, Apache/Nginx, โค้ด Photobooth)
- **ไม่มี Docker** — กล้อง Pi, USB, เครื่องพิมพ์ (CUPS) ใช้ได้ปกติ
- นี่คือแบบที่ใช้ **ในงานจริง** (โฟโต้บูธที่ลูกค้าใช้)

### ใช้เมื่อไหร่
- พร้อมเอาไปใช้ในงานอีเวนต์
- ต้องการต่อ **กล้อง** (Pi Camera Module หรือ USB) และ **เครื่องพิมพ์** (เช่น Epson L805 ผ่าน Wavlink)

### สิ่งที่ต้องเตรียม
- Raspberry Pi 5
- Raspberry Pi OS 64-bit (Bookworm ขึ้นไป)
  - ดาวน์โหลด: https://www.raspberrypi.com/software/
- การเชื่อมต่อเครือข่าย (Wi‑Fi หรือ Ethernet)

### วิธี Setup (แบบย่อ)

#### 3.1 อัปเดตระบบ
```bash
sudo apt update
sudo apt dist-upgrade
```

#### 3.2 ติดตั้ง Web Server + PHP + Dependencies
```bash
# Apache + PHP
sudo apt install -y libapache2-mod-php

# Dependencies (ปรับตามที่โปรเจกต์ต้องการ)
sudo apt install -y curl git gphoto2 libimage-exiftool-perl nodejs php-xml php-gd php-zip php-mbstring rsync udisks2 python3
```

- ต้องมี **PHP >= 8.4** และ **Node.js >= 20**
  - ถ้าใน repo มีเวอร์ชันต่ำกว่า ดู [Prerequisites](https://photoboothproject.github.io/) หรือใช้ [Photobooth Setup Wizard](https://photoboothproject.github.io/install/setup_wizard)
- **`php-gd`** ต้องติดตั้งเพื่อรองรับฟีเจอร์ animated GIF slideshow (มักติดตั้งพร้อม libapache2-mod-php แต่ตรวจสอบว่ามีอยู่)
  ตรวจสอบด้วย: `php -m | grep gd`

#### 3.3 โคลนโปรเจกต์และ Build
```bash
sudo chown -R www-data:www-data /var/www
cd /var/www
sudo -u www-data git clone <repo-url> html
cd /var/www/html
sudo -u www-data git submodule update --init
sudo -u www-data npm install
sudo -u www-data npm run build
```

- ตั้งค่า Apache/Nginx ให้ document root ชี้ไปที่ `/var/www/html` (หรือ path ที่โคลนไว้)

#### 3.4 สิทธิ์กล้องและเครื่องพิมพ์ (ถ้าใช้)
```bash
# กล้อง
sudo gpasswd -a www-data plugdev

# เครื่องพิมพ์ (CUPS)
sudo apt install -y cups
sudo gpasswd -a www-data lp
sudo cupsctl --remote-any
sudo systemctl restart cups
```

#### 3.5 รีสตาร์ทแล้วทดสอบ
```bash
sudo reboot
```

จากเครื่องอื่นในเครือข่าย: เปิดเบราว์เซอร์ไปที่ `http://<IP ของ Pi>` (เช่น `http://192.168.10.5`)

### การอัปเดตเมื่อมีโค้ดใหม่
```bash
cd /var/www/html
sudo -u www-data git pull
sudo -u www-data git submodule update --init
sudo -u www-data npm install
sudo -u www-data npm run build
```

> **หลัง update ล่าสุด (10 มี.ค. 2026):** ไม่ต้องติดตั้ง dependency เพิ่ม — `php-gd` มีอยู่แล้วใน stack ปัจจุบัน
> เข้า Admin → Collage เพื่อเปิดฟีเจอร์ animated GIF slideshow ถ้าต้องการ

### ข้อดี
- ใช้ **กล้องและเครื่องพิมพ์จริง** ได้
- ไม่ใช้ทรัพยากรของ Docker
- เหมาะกับการรันยาวๆ ในงาน

---

## สรุป: Workflow แนะนำ (Dev บน Mac → Production บน Pi 5)

| ขั้นตอน | ทำที่ | ใช้แบบ |
|---------|--------|--------|
| พัฒนา / แก้ UI, API | Mac | **DDEV** (`ddev start` แล้วเข้า photobooth.ddev.site:9080) |
| ทดสอบ build + รันแบบใกล้เคียง production | Mac | **Docker Compose** (`docker compose up --build` แล้วเข้า localhost) |
| นำขึ้นเครื่องจริง | Raspberry Pi 5 | **Production** (โคลนลง Pi, build บน Pi, ต่อกล้อง/เครื่องพิมพ์) |

---

## ลิงก์ที่เกี่ยวข้อง

- [การติดตั้ง Photobooth (official)](https://photoboothproject.github.io/)
- [ติดตั้งบน Raspberry Pi + Cloudflare Tunnel](docs/install/raspberry-pi-cloudflare-tunnel.md) (ใน repo นี้)
- [DDEV Documentation](https://ddev.readthedocs.io/)
- [Raspberry Pi OS](https://www.raspberrypi.com/software/)

---

## Changelog

| วันที่ | รายการ |
|--------|--------|
| 10 มี.ค. 2026 | เพิ่ม animated GIF slideshow หลัง collage; แก้ filter slide กลับหน้าแรก; แก้ filter ปรับได้แค่ครั้งเดียว |
