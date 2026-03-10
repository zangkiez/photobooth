# Photobooth — คู่มือ Dev & Deploy

> **สำหรับผู้พัฒนาคนต่อไป:** อ่านเอกสารนี้ก่อนเริ่มทำงาน ครอบคลุมทั้ง setup Dev บน Mac, การปริ้น, กล้อง iPhone, Cloudflare Tunnel และการ Deploy จริงบน Raspberry Pi 5

**Stack ปัจจุบัน:**
- **Dev:** Mac + DDEV (Docker) + Cloudflare Tunnel
- **Production:** Raspberry Pi 5 (ติดตั้งตรง ไม่ใช้ Docker)
- **กล้อง (Dev):** iPhone ผ่าน Browser Camera (`getUserMedia`) — ต้องการ HTTPS
- **กล้อง (Production):** Pi Camera Module หรือ USB gphoto2
- **เครื่องพิมพ์ (Dev):** ปริ้นผ่าน Print Relay สู่ CUPS บน Mac
- **เครื่องพิมพ์ (Production):** ต่อ USB กับ Pi โดยตรง ผ่าน CUPS

---

## สารบัญ

1. [การเปลี่ยนแปลงล่าสุด](#การเปลี่ยนแปลงล่าสุด)
2. [สถาปัตยกรรมระบบ](#สถาปัตยกรรมระบบ)
3. [Dev Environment — DDEV บน Mac](#dev-environment--ddev-บน-mac)
4. [กล้อง iPhone บน Dev](#กล้อง-iphone-บน-dev)
5. [เครื่องพิมพ์บน Dev — Print Relay](#เครื่องพิมพ์บน-dev--print-relay)
6. [Cloudflare Tunnel (HTTPS สำหรับ Dev)](#cloudflare-tunnel-https-สำหรับ-dev)
7. [โครงสร้างโค้ดสำคัญ](#โครงสร้างโค้ดสำคัญ)
8. [Production — Raspberry Pi 5](#production--raspberry-pi-5)
9. [การอัปเดตโค้ด Production](#การอัปเดตโค้ด-production)
10. [Troubleshooting](#troubleshooting)

---

## การเปลี่ยนแปลงล่าสุด (10 มีนาคม 2026)

### Bug Fixes
- **Filter slide กลับหน้าแรก:** แก้ไข timeout ไม่ reload page ขณะที่ filter nav เปิดอยู่หรือกำลัง process
- **ปรับ filter ได้แค่ครั้งแรก:** แก้ให้ใช้ไฟล์ต้นฉบับ (captureBasename) แทน variant สำหรับ filter reprocess ครั้งถัดไป
- **GIF สีผิดเพี้ยน:** แก้ `GifEncoder::buildAnimated()` ให้ใช้ **Local Color Table (LCT)** ต่อ frame — แก้ปัญหา pixel index ชี้ palette ผิด frame
- **PhotoSwipe crash** `TypeError: Cannot read properties of null (reading getAttribute)`: แก้ caption handler ให้ null-safe
- **กด play video ใน view.php ไม่ได้:** เพิ่ม CSS override `pointer-events: auto` สำหรับ `<video controls>`
- **MP4 ใน gallery modal ว่างเปล่า:** เปลี่ยนเป็น `data-video-src` + สร้าง `<video>` DOM ใน `contentLoad` event

### ฟีเจอร์ใหม่
- **Collage Slideshow (GIF + MP4):** หลังถ่าย collage เสร็จ สร้าง animated GIF และ MP4 อัตโนมัติ เพิ่มใน gallery ทันที
- **Print Relay:** ปริ้นจาก DDEV container ผ่าน Python relay ไปยัง CUPS บน Mac ได้โดยไม่ต้องลง cups-client
- **Auto-start Relay:** DDEV hook เปิด relay อัตโนมัติทุกครั้งที่ `ddev start`

### ไฟล์ที่เปลี่ยนแปลง
| ไฟล์ | รายละเอียด |
|------|------------|
| `src/Utility/GifEncoder.php` | Pure-PHP animated GIF encoder (LCT per-frame) |
| `src/Configuration/Section/CollageConfiguration.php` | เพิ่ม `collage[slideshow_enabled]` |
| `lib/configsetup.inc.php` | checkbox ใน Admin panel |
| `api/applyEffects.php` | สร้าง GIF + MP4 หลัง collage |
| `assets/js/core.js` + `resources/js/core.js` | รับ `slideshow_mp4` → gallery; `addVideoToGallery` |
| `assets/js/photoswipe.js` + `resources/js/photoswipe.js` | video slide ใน PhotoSwipe |
| `view.php` | CSS `pointer-events` สำหรับ video |
| `.ddev/web-build/Dockerfile` | ffmpeg + `/usr/local/bin/lp` (print proxy) |
| `.ddev/web-build/lp-cups.py` | bash+curl script แทน `lp` command |
| `.ddev/config.yaml` | post-start hook auto-start print relay |
| `bin/print-relay` | Python relay server รันบน Mac host |
| `config/my.config.inc.php` | `commands.print` ชี้ไปที่ relay |
| `resources/lang/en.json` + `th.json` | translation keys ใหม่ |

---

## สถาปัตยกรรมระบบ

### Dev (Mac + DDEV)

```
iPhone Safari ──HTTPS──► Cloudflare Tunnel
                               |
                        DDEV container (Mac)
                        PHP 8.4 + Apache
                        /var/www/html
                               |
              .────────────────┴────────────────.
              |                                 |
        กล้อง iPhone                      เครื่องพิมพ์
    getUserMedia (Browser)           lp → host.docker.internal:6631
                                              |
                                     bin/print-relay (Mac)
                                              |
                                        Mac CUPS
                                              |
                                 DNP QW410 (Dai_Nippon_Printing_DP_QW410)
```

### Production (Raspberry Pi 5)

```
แขก (มือถือ) ──LAN──► Raspberry Pi 5
                       Apache + PHP
                            |
              .─────────────┴─────────────.
              |                           |
        Pi Camera / USB              เครื่องพิมพ์ USB
        (gphoto2/libcamera)          CUPS ปริ้นโดยตรง
```

---

## Dev Environment — DDEV บน Mac

### สิ่งที่ต้องติดตั้งบน Mac (ครั้งแรกครั้งเดียว)

```bash
# 1. OrbStack (แนะนำ — เร็วกว่า Docker Desktop) หรือ Docker Desktop
brew install orbstack

# 2. DDEV
brew install ddev

# 3. Cloudflare Tunnel (สำหรับ HTTPS + กล้อง iPhone)
brew install cloudflare/cloudflare/cloudflared
```

### Clone และ Start (ครั้งแรก)

```bash
git clone <repo-url> photobooth
cd photobooth
ddev start
```

`ddev start` จะทำสิ่งต่อไปนี้อัตโนมัติ:
1. Build Docker image (PHP 8.4, Apache, ffmpeg, print proxy)
2. ติดตั้ง npm dependencies (ถ้ายังไม่มี)
3. Build frontend assets (`npm run build`) ครั้งแรก
4. เปิด gulp watch (CSS/JS auto-rebuild เมื่อแก้ไฟล์ใน `resources/`)
5. **เปิด Print Relay บน Mac host** (port 6631) อัตโนมัติ

### Dev บน Mac ด้วย Docker (ทางเลือกจาก DDEV) — config จบใน Docker

ถ้าไม่อยากใช้ DDEV ใช้ **docker-compose.dev.yml** ได้ — PHP/Apache/config อยู่ใน Docker ไม่ต้องตั้งค่าบน host; อัปเดตแค่ `git pull` แล้ว `docker compose up -d --build`

```bash
cd photobooth
# ครั้งแรก: รัน print-relay บน Mac (อีก terminal) ถ้าต้องการทดสอบพิมพ์
# ./bin/print-relay 6631 Dai_Nippon_Printing_DP_QW410

docker compose -f docker-compose.dev.yml up -d --build
# เปิด http://localhost:9080
```

- **Config แอป** จาก env ใน compose (`PHOTOBOOTH_WEBSERVER_URL`, `PHOTOBOOTH_PRINTER_NAME`) — แก้ใน `docker-compose.dev.yml` หรือส่ง env
- **PHP/Apache** ใช้ `docker/php.ini` และ `docker/apache-photobooth.conf` ใน repo
- โค้ด mount จาก host → แก้แล้วเห็นผล; `vendor` และ `node_modules` อยู่ใน volume ของ container (Linux)
- อัปเดต: `git pull && docker compose -f docker-compose.dev.yml up -d --build`

ดูรายละเอียดใน [docs/DEPLOY-PRODUCTION-UBUNTU-RASPBERRY.md](docs/DEPLOY-PRODUCTION-UBUNTU-RASPBERRY.md) (ส่วน Docker) และ [.env.example](.env.example) สำหรับตัวแปร config

### URL สำหรับ Dev

| URL | ใช้ทำอะไร |
|-----|-----------|
| `https://photobooth.ddev.site:9443` | หน้าหลัก (HTTPS local) |
| `http://photobooth.ddev.site:9080` | หน้าหลัก (HTTP local) |
| `https://photobooth.ddev.site:9443/admin` | Admin panel |

### คำสั่ง DDEV ที่ใช้บ่อย

```bash
ddev start                    # เริ่ม (rebuild image ถ้า Dockerfile เปลี่ยน)
ddev stop                     # หยุด
ddev restart                  # หยุด + เริ่มใหม่
ddev ssh                      # เข้า shell ใน container
ddev exec "<คำสั่ง>"          # รัน command ใน container
ddev logs -s web              # ดู log ของ web container
ddev npm run build            # build frontend ใหม่ทั้งหมด
ddev npm run watch:gulp       # เปิด watch mode ด้วยมือ
ddev composer install         # ติดตั้ง PHP dependencies
ddev qa                       # รัน linting + phpstan + tests
```

### การแก้ไขโค้ด

| ประเภทไฟล์ | โฟลเดอร์ | หมายเหตุ |
|-----------|---------|---------|
| PHP source | `src/`, `api/`, `lib/` | แก้แล้วเห็นผลทันที |
| JavaScript (source) | `resources/js/` | gulp watch compile → `assets/js/` อัตโนมัติ |
| CSS/SCSS (source) | `resources/sass/` | gulp watch compile → `assets/css/` อัตโนมัติ |
| JavaScript (compiled) | `assets/js/` | **อย่าแก้ตรงนี้** — ถูก overwrite โดย build |
| Config | `config/my.config.inc.php` | ไฟล์ config ส่วนตัว (ไม่ commit) |
| Template | `template/`, `view.php` | แก้ได้โดยตรง |

> **สำคัญ:** แก้ไฟล์ใน `resources/js/` หรือ `resources/sass/` เท่านั้น ไม่ใช่ `assets/`
> gulp watch จะ compile ให้อัตโนมัติ — ถ้า watch ไม่รัน ใช้ `ddev npm run build`

---

## กล้อง iPhone บน Dev

โปรเจกต์นี้ใช้กล้อง iPhone ผ่าน browser (`getUserMedia`) ซึ่ง **ต้องการ HTTPS**

### วิธี Setup

**1. เปิด Cloudflare Tunnel** (อ่านรายละเอียดในหัวข้อถัดไป) เพื่อได้ HTTPS URL

**2. ตั้งค่า config** (`config/my.config.inc.php`):

```php
'webserver' => [
    'url' => 'https://xxxx.trycloudflare.com', // URL จาก tunnel
],
'preview' => [
    'mode' => 'device_cam',
    'camTakesPic' => true,
],
'qr' => [
    'url' => 'https://xxxx.trycloudflare.com/view.php?image=',
],
```

**3. เปิด photobooth บน iPhone** ผ่าน URL ของ Cloudflare Tunnel
Safari จะขอ permission กล้อง → อนุญาต

### ข้อสังเกต

- iPhone ต้องเปิด Safari ที่ URL จาก tunnel ไม่ใช่ `photobooth.ddev.site`
- `getUserMedia` ใช้ไม่ได้บน HTTP หรือ `localhost` บน iOS
- เลือกกล้องหน้า/หลังได้ใน Admin → Camera

---

## เครื่องพิมพ์บน Dev — Print Relay

เนื่องจาก DDEV รันใน Docker container ไม่สามารถเรียก CUPS บน Mac ตรงๆ ได้ (CUPS block HTTP requests จาก Docker network) จึงใช้ระบบ **Print Relay** แทน

### สถาปัตยกรรม Print

```
PHP exec("lp file.jpg")
       |
/usr/local/bin/lp  <- bash script 1 บรรทัด (ใน container)
curl POST file -> host.docker.internal:6631/print
       |
bin/print-relay (Python HTTP server, Mac host)
subprocess.run(['lp', '-d', PRINTER, file])
       |
Mac CUPS -> DNP QW410
```

### ไฟล์ที่เกี่ยวข้อง

| ไฟล์ | รันที่ไหน | หน้าที่ |
|------|---------|---------|
| `.ddev/web-build/lp-cups.py` | Container | ถูก copy เป็น `/usr/local/bin/lp` — curl POST ไปหา relay |
| `bin/print-relay` | Mac host | Python HTTP server รับ job แล้วส่งต่อ `lp` ของ Mac |
| `.ddev/config.yaml` (hook) | Mac host | Auto-start relay ทุกครั้ง `ddev start` |
| `config/my.config.inc.php` | — | `commands.print = 'lp -d <ชื่อปริ้น> ...'` |

> **Photobooth บน Cloud + Print บน Local:** ถ้ารัน Photobooth บน cloud แล้วต้องการส่งงานพิมพ์ไปเครื่องพิมพ์ที่ต่อกับเครื่อง local ผ่าน **Cloudflare Tunnel (HTTPS)** ดูคู่มือออกแบบและ setup ได้ที่ [docs/print-relay-cloudflare-tunnel.md](docs/print-relay-cloudflare-tunnel.md)

### Setup เครื่องพิมพ์ใหม่ (เมื่อเปลี่ยนปริ้นหรือ environment ใหม่)

**1. เพิ่มเครื่องพิมพ์ใน Mac:**
> System Settings → Printers & Scanners → + → เลือกปริ้น

**2. หาชื่อ printer:**
```bash
lpstat -p
# ตัวอย่าง: printer Dai_Nippon_Printing_DP_QW410 is idle.
```

> **หมายเหตุ:** ชื่อปริ้นขึ้นอยู่กับ driver ที่ลงใน Mac ตรวจด้วย `lpstat -p` ทุกครั้ง

**3. หา media option ที่ปริ้นรองรับ:**
```bash
lpoptions -p <ชื่อปริ้น> -l | grep -i "pagesize\|media"
# ตัวอย่าง output: PageSize/Media Size: dnp4x3 dnp4x4 dnp4x6 ...
```

**4. อัปเดต `bin/print-relay`** (บรรทัด PRINTER):
```python
PRINTER = sys.argv[2] if len(sys.argv) > 2 else 'ชื่อปริ้นของคุณ'
```

**5. อัปเดต `config/my.config.inc.php`:**
```php
'commands' => [
    'print' => 'lp -d ชื่อปริ้น -o PageSize=dnp4x6 -o fit-to-page %s',
],
```

> **หมายเหตุ:** อย่าใส่ `-o landscape` — PHP ใน `print.php` rotate ภาพไว้แล้ว ถ้าใส่จะ rotate ซ้ำทำให้ภาพตกขอบ

**6. Restart print-relay** (จำเป็นทุกครั้งที่แก้ไข `bin/print-relay`):
```bash
pkill -f "bin/print-relay"
nohup python3 bin/print-relay > /tmp/print-relay.log 2>&1 &

# ตรวจว่าขึ้นมาแล้ว
curl http://localhost:6631/health
```

> **สรุป: เปลี่ยนปริ้นต้อง restart อะไรบ้าง**
> | สิ่งที่เปลี่ยน | ต้อง restart |
> |---------------|-------------|
> | ชื่อปริ้น (`bin/print-relay`) | ✅ `pkill -f bin/print-relay` แล้วรันใหม่ |
> | ชื่อปริ้น (`config/my.config.inc.php`) | ❌ ไม่ต้อง — PHP อ่านทุก request |
> | paper size / options (config เท่านั้น) | ❌ ไม่ต้อง |
> | paper size / options (relay ด้วย) | ✅ restart relay |
> | `ddev restart` | ❌ ไม่จำเป็น เว้นแต่แก้ Dockerfile |

**7. ทดสอบ:**
```bash
ddev exec "lp -d ชื่อปริ้นของคุณ /var/www/html/resources/img/logo/logo-plain-fulltext.png"
# ต้องเห็น: request id is ชื่อปริ้น-XX (1 file(s))
```

### Log ของ Relay
```bash
cat /tmp/print-relay.log
```

---

### คู่มือ `lp` command — อธิบายแต่ละ option

`lp` คือ command สั่งพิมพ์บน macOS/Linux ผ่าน CUPS

#### Syntax พื้นฐาน
```bash
lp [options] <ไฟล์>
```

#### Options ที่ใช้ในโปรเจกต์นี้

| Option | ความหมาย | ตัวอย่าง |
|--------|----------|---------|
| `-d <ชื่อปริ้น>` | เลือกเครื่องพิมพ์ที่จะส่งงาน | `-d Dai_Nippon_Printing_DP_QW410` |
| `-o PageSize=<ขนาด>` | กำหนดขนาดกระดาษ ชื่อขึ้นกับ driver | `-o PageSize=dnp4x6` |
| `-o fit-to-page` | ย่อ/ขยายภาพให้พอดีกระดาษ ไม่ตัด | `-o fit-to-page` |
| `-o landscape` | หมุนภาพ 90° ก่อนส่งให้ปริ้น | `-o landscape` |
| `-n <จำนวน>` | จำนวนก็อปปี้ | `-n 2` |
| `-H <host:port>` | ส่งไปยัง CUPS server อื่น | `-H localhost:631` |

#### ขนาดกระดาษ DNP QW410 — ดูได้ด้วย

```bash
lpoptions -p Dai_Nippon_Printing_DP_QW410 -l | grep PageSize
# PageSize/Media Size: dnp4x3 dnp4x4 dnp4x4.5 dnp4x6 dnp4.5x3 dnp4.5x4 ...
```

| ชื่อ option | ขนาดจริง |
|------------|---------|
| `dnp4x3` | 4 × 3 นิ้ว |
| `dnp4x4` | 4 × 4 นิ้ว |
| `dnp4x6` | 4 × 6 นิ้ว ← **ที่ใช้อยู่** |
| `dnp4.5x6` | 4.5 × 6 นิ้ว |
| `dnp4.5x8` | 4.5 × 8 นิ้ว |

#### `-o fit-to-page` vs ไม่ใส่

| | ผล |
|--|--|
| **ใส่ `-o fit-to-page`** | ภาพถูกย่อ/ขยายให้พอดีกระดาษ ไม่มีส่วนที่ถูกตัดออก |
| **ไม่ใส่** | CUPS ปริ้นขนาดจริงของภาพ ถ้าใหญ่กว่ากระดาษจะถูกตัด (crop) |

#### ทำไมถึง **ไม่** ใส่ `-o landscape` ในโปรเจกต์นี้

PHP ใน `api/print.php` rotate ภาพให้เป็น landscape ก่อนบันทึกไฟล์ print อยู่แล้ว:
```php
// จาก api/print.php
if (imagesx($source) > imagesy($source) || $config['print']['no_rotate'] === true) {
    $imageHandler->qrRotate = false;
} else {
    $source = imagerotate($source, 90, 0);  // ← rotate ที่นี่แล้ว
}
```

ถ้าใส่ `-o landscape` อีกครั้ง CUPS จะ rotate ซ้ำ ทำให้ภาพกลับมาเป็น portrait บนกระดาษ landscape → **ภาพตกขอบ**

#### ตัวอย่าง command เต็มที่ใช้อยู่

```bash
lp -d Dai_Nippon_Printing_DP_QW410 \
   -o PageSize=dnp4x6 \
   -o media=Custom.4x6 \
   -o scaling=100 \
   -o position=center \
   /path/to/image.jpg
```

| Option | ความหมาย |
|--------|----------|
| `PageSize=dnp4x6` | บอก driver ว่ากระดาษที่ใส่คือ DNP 4×6 |
| `media=Custom.4x6` | บอก CUPS ว่าขนาด media คือ 4×6 นิ้ว (ตรงกับกระดาษจริง) |
| `scaling=100` | ปริ้น 100% ไม่ย่อ/ขยาย — ใช้ขนาดภาพตามที่ PHP เตรียมไว้ |
| `position=center` | วางภาพตรงกลางกระดาษ |

อ่านง่ายๆ: _"ส่งไฟล์นี้ไปปริ้นที่ DNP QW410, กระดาษ 4×6, ปริ้นเต็ม 100% วางกลาง"_

#### ปรับแต่งเพิ่มเติมที่ทำได้

```bash
# ปริ้น 2 ก็อปปี้
lp -d Dai_Nippon_Printing_DP_QW410 -o PageSize=dnp4x6 -o fit-to-page -n 2 file.jpg

# ดู job ที่รออยู่ในคิว
lpq -P Dai_Nippon_Printing_DP_QW410

# ยกเลิก job (เอา job ID จาก lpq)
cancel <job-id>

# ดู options ทั้งหมดที่ปริ้นรองรับ
lpoptions -p Dai_Nippon_Printing_DP_QW410 -l
```

---

## Cloudflare Tunnel (HTTPS สำหรับ Dev)

Cloudflare Tunnel ทำให้ DDEV บน Mac เข้าได้จาก internet ผ่าน HTTPS — จำเป็นสำหรับ:
- กล้อง iPhone (`getUserMedia` ต้องการ HTTPS)
- QR code ที่แขกสแกนแล้วดูรูปจากมือถือ

### Quick Tunnel (ไม่ต้อง account — ใช้ dev ชั่วคราว)

```bash
cloudflared tunnel --url http://localhost:9080
# ได้ URL เช่น: https://abc123-def456.trycloudflare.com
```

URL เปลี่ยนทุกครั้งที่เปิด tunnel ใหม่ ต้องอัป `webserver.url` ในconfig ด้วย

### Named Tunnel (URL คงที่ — แนะนำ)

ต้องมี Cloudflare account ฟรี + domain บน Cloudflare

```bash
# Login ครั้งแรก
cloudflared tunnel login

# สร้าง tunnel (ทำครั้งเดียว)
cloudflared tunnel create photobooth

# กำหนด subdomain (ชี้ที่ Cloudflare DNS)
cloudflared tunnel route dns photobooth photo.yourdomain.com

# เปิด tunnel
cloudflared tunnel run photobooth
```

สร้าง `~/.cloudflared/config.yml`:
```yaml
tunnel: <TUNNEL_ID>
credentials-file: /Users/<username>/.cloudflared/<TUNNEL_ID>.json
ingress:
  - hostname: photo.yourdomain.com
    service: http://localhost:9080
  - service: http_status:404
```

### หลังได้ URL แล้ว

อัปเดต `config/my.config.inc.php`:
```php
'webserver' => [
    'url' => 'https://photo.yourdomain.com',
],
'qr' => [
    'url' => 'https://photo.yourdomain.com/view.php?image=',
],
```

---

## โครงสร้างโค้ดสำคัญ

```
photobooth/
├── api/                    # PHP API endpoints
│   ├── applyEffects.php    # สร้าง GIF + MP4 slideshow
│   ├── capture.php         # ถ่ายรูป
│   └── print.php           # สั่งปริ้น -> exec lp
├── assets/                 # Compiled output — อย่าแก้ตรงนี้
│   ├── js/                 # Compiled JS (จาก resources/js/)
│   └── css/                # Compiled CSS (จาก resources/sass/)
├── bin/
│   └── print-relay         # Python relay server รันบน Mac
├── config/
│   └── my.config.inc.php   # Config ส่วนตัว (ไม่ commit เข้า git)
├── resources/
│   ├── js/                 # JS source — แก้ที่นี่
│   │   ├── core.js         # Main photobooth logic
│   │   └── photoswipe.js   # Gallery + video modal
│   └── sass/               # SCSS source — แก้ที่นี่
├── src/                    # PHP classes
│   └── Utility/
│       └── GifEncoder.php  # Pure-PHP animated GIF encoder
├── .ddev/
│   ├── config.yaml         # DDEV config + hooks
│   └── web-build/
│       ├── Dockerfile      # Custom container image
│       └── lp-cups.py      # Print proxy -> /usr/local/bin/lp
└── lib/
    ├── boot.php            # Bootstrap (load config, session)
    └── configsetup.inc.php # Admin panel config fields
```

### Flow การถ่ายรูป

```
Browser -> capture.php (trigger กล้อง)
        -> applyEffects.php (filter, frame, collage, GIF/MP4)
        -> core.js renderPic() (แสดงผล, เพิ่มใน gallery)
```

### Flow การปริ้น

```
Browser -> api/print.php
        -> exec("lp file.jpg")
        -> /usr/local/bin/lp [container] -> curl POST -> relay [Mac]
        -> lp [Mac] -> CUPS -> เครื่องพิมพ์
```

---

## Production — Raspberry Pi 5

> **คู่มือ deploy แบบละเอียด (Ubuntu + Raspberry Pi, รันทั้งวันระบบไม่ค้าง):** [docs/DEPLOY-PRODUCTION-UBUNTU-RASPBERRY.md](docs/DEPLOY-PRODUCTION-UBUNTU-RASPBERRY.md)

### สิ่งที่ต้องเตรียม

- Raspberry Pi 5 (RAM 4GB ขึ้นไป)
- Raspberry Pi OS 64-bit Bookworm หรือใหม่กว่า
- Storage: SD Card หรือ NVMe ขนาด 32GB ขึ้นไป
- กล้อง: Pi Camera Module 3 หรือ USB camera
- เครื่องพิมพ์: ต่อ USB กับ Pi ได้โดยตรง

### Setup ครั้งแรก

#### ขั้นที่ 1 — อัปเดต OS
```bash
sudo apt update && sudo apt dist-upgrade -y
```

#### ขั้นที่ 2 — ติดตั้ง dependencies
```bash
# Web server + PHP
sudo apt install -y apache2 libapache2-mod-php

# ตรวจ PHP version (ต้องการ >= 8.1, แนะนำ 8.2+)
php -v

# Tools + dependencies
sudo apt install -y git curl wget gphoto2 libimage-exiftool-perl \
  rsync udisks2 python3 \
  php-gd php-xml php-zip php-mbstring php-curl

# Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo bash -
sudo apt install -y nodejs

# ffmpeg (จำเป็นสำหรับ MP4 slideshow)
sudo apt install -y ffmpeg

# CUPS (เครื่องพิมพ์)
sudo apt install -y cups
```

#### ขั้นที่ 3 — Clone และ Build
```bash
cd /var/www
sudo git clone <repo-url> html
cd /var/www/html

# สิทธิ์
sudo chown -R www-data:www-data /var/www/html

# PHP dependencies
sudo -u www-data composer install --no-dev

# Frontend
sudo -u www-data npm install
sudo -u www-data npm run build
```

#### ขั้นที่ 4 — Apache config
```bash
sudo nano /etc/apache2/sites-available/photobooth.conf
```

```apache
<VirtualHost *:80>
    DocumentRoot /var/www/html
    <Directory /var/www/html>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

```bash
sudo a2ensite photobooth
sudo a2enmod rewrite
sudo a2dissite 000-default
sudo systemctl restart apache2
```

#### ขั้นที่ 5 — สิทธิ์กล้องและปริ้น
```bash
# กล้อง (USB/gphoto2)
sudo gpasswd -a www-data plugdev
sudo gpasswd -a www-data video

# เครื่องพิมพ์ (CUPS)
sudo gpasswd -a www-data lp
sudo gpasswd -a www-data lpadmin
sudo cupsctl --remote-any
sudo systemctl enable cups
sudo systemctl start cups
```

#### ขั้นที่ 6 — เพิ่มเครื่องพิมพ์บน Pi

เข้า CUPS web admin: `http://localhost:631`
หรือ command line:
```bash
# หา URI ของปริ้น
lpinfo -v

# เพิ่มปริ้น (ปรับ URI และชื่อตามจริง)
sudo lpadmin -p Epson_L8050 -E -v <printer_uri> -m everywhere
sudo lpoptions -d Epson_L8050

# ทดสอบ
lpstat -p
```

#### ขั้นที่ 7 — Config ของ Photobooth
สร้าง `config/my.config.inc.php`:
```php
<?php
return [
    'webserver' => [
        'url' => 'http://192.168.1.x', // IP ของ Pi ใน LAN
    ],
    'preview' => [
        'mode' => 'gphoto2',           // หรือ 'libcamera', 'device_cam'
    ],
    'picture' => [
        'cntdwn_time' => 5,
    ],
    'print' => [
        'from_result' => true,
        'from_gallery' => true,
        'limit' => 1,
    ],
    // บน Pi ไม่ต้อง commands.print — ใช้ lp ของ Pi โดยตรงผ่าน CUPS
    'collage' => [
        'slideshow_enabled' => true,
    ],
    'qr' => [
        'url' => 'http://192.168.1.x/view.php?image=',
    ],
];
```

#### ขั้นที่ 8 — ตรวจสอบและ Reboot
```bash
# ตรวจ PHP extensions
php -m | grep -E "gd|zip|mbstring|xml"

# ตรวจ ffmpeg
ffmpeg -version | head -1

# ตรวจ CUPS
lpstat -p

sudo reboot
```

เปิด browser จากมือถือในเครือข่ายเดียวกัน: `http://<IP ของ Pi>`

---

## การอัปเดตโค้ด Production

ทุกครั้งที่มีโค้ดใหม่จาก git:

```bash
cd /var/www/html

# ดึงโค้ดล่าสุด
sudo -u www-data git pull
sudo -u www-data git submodule update --init

# ถ้า composer.json เปลี่ยน
sudo -u www-data composer install --no-dev

# ถ้า package.json เปลี่ยน
sudo -u www-data npm install

# Build frontend เสมอ
sudo -u www-data npm run build

# Reload Apache (ถ้า config เปลี่ยน)
sudo systemctl reload apache2
```

> **ตรวจสอบหลัง update:**
> - `php -m | grep gd` — ต้องมี GD
> - `which ffmpeg` — ต้องมี ffmpeg
> - เข้า `/admin` ตรวจ config ที่เพิ่งเพิ่ม

---

## Troubleshooting

### DDEV ไม่ start / container crash

```bash
ddev logs -s web        # ดู log ก่อน
ddev stop
docker rm -f ddev-photobooth-web 2>/dev/null
ddev start
```

### Frontend ไม่อัปเดต (JS/CSS เก่า)

```bash
rm -f .ddev/.built
ddev exec "npm run build"
```

### Print Relay ไม่ทำงาน

```bash
# ตรวจว่า relay รันอยู่
curl http://localhost:6631/health

# ถ้าไม่ตอบ — start ด้วยมือ (หรือ ddev restart ให้ hook start ให้)
nohup python3 bin/print-relay > /tmp/print-relay.log 2>&1 &

# ดู log
cat /tmp/print-relay.log

# ทดสอบส่ง job จาก container
ddev exec "lp -d Dai_Nippon_Printing_DP_QW410 /var/www/html/resources/img/logo/logo-plain-fulltext.png"
# ต้องเห็น: request id is Dai_Nippon_Printing_DP_QW410-XX (1 file(s))
```

### Print lock ค้าง (กดปริ้นแล้วเงียบ ไม่มี error)

```bash
# ตรวจและลบ lock
ls data/print.lock && rm data/print.lock && echo "✓ lock removed"

# ตรวจ printed.csv ว่าเกิน limit ไหม
cat data/printed.csv
# ถ้า limit เต็ม: แก้ config/my.config.inc.php → 'limit' => 0
```

> **สาเหตุ print lock ค้าง:** เกิดเมื่อ PHP เรียก lp แล้ว relay ส่งต่อสำเร็จแต่ unlock process ไม่ถูกเรียก เช่น browser ปิดก่อน หรือ timeout — ลบ `data/print.lock` ได้เลย

### CUPS listen แค่ localhost ทำให้ Docker ต่อไม่ได้

```bash
netstat -anp tcp | grep LISTEN | grep 631
# ต้องเห็น *.631 ไม่ใช่ 127.0.0.1.631

# แก้:
sudo cupsctl --remote-any
sudo launchctl stop org.cups.cupsd
sudo launchctl start org.cups.cupsd
```

### กล้อง iPhone ไม่เปิด / ไม่ขอ permission

- ต้องเปิดผ่าน **HTTPS** (URL จาก Cloudflare Tunnel) ไม่ใช่ `photobooth.ddev.site`
- ลองปิด-เปิด Safari หรือ Settings → Safari → Clear History and Website Data

### MP4 ไม่ถูกสร้างหลัง Collage

```bash
# ตรวจ ffmpeg ใน container
ddev exec "ffmpeg -version | head -1"

# ถ้าไม่มี rebuild
ddev restart
```

บน Pi:
```bash
which ffmpeg || sudo apt install -y ffmpeg
```

### QR code ลิงก์ไม่ถูก / เปิดไม่ได้บนมือถือ

ตรวจ `qr.url` ใน `config/my.config.inc.php` ต้องเป็น URL ที่มือถือแขกเข้าถึงได้จริง:
- Dev: URL จาก Cloudflare Tunnel
- Production: IP หรือ domain ของ Pi

---

## Workflow สรุป

```
Dev (Mac)                        Production (Pi)
─────────────────────            ─────────────────────
ddev start                       git pull
  |                              npm run build
  +─ auto build frontend         systemctl reload apache2
  +─ auto start print relay
  └─ ready at :9443

แก้โค้ดใน resources/js/ หรือ src/
  → gulp watch compile อัตโนมัติ

ทดสอบผ่าน Cloudflare Tunnel URL
  → iPhone camera OK
  → Print OK
  → QR code OK

git commit && git push
     |
     └───────────────────────────► git pull บน Pi
                                   npm run build
                                   พร้อมใช้งานจริง
```

---

## Changelog

| วันที่ | รายการ |
|--------|--------|
| 10 มี.ค. 2026 | แก้ชื่อปริ้น → `Dai_Nippon_Printing_DP_QW410` (DNP QW410); แก้ print.lock ค้าง; `print.limit => 0` |
| 10 มี.ค. 2026 | Print Relay: ปริ้นจาก DDEV ผ่าน Python relay → Mac CUPS; auto-start ผ่าน DDEV hook |
| 10 มี.ค. 2026 | รองรับกล้อง iPhone บน Dev ผ่าน Cloudflare Tunnel + Browser Camera |
| 10 มี.ค. 2026 | Collage Slideshow GIF + MP4; แก้ GIF LCT palette bug; แก้ PhotoSwipe video modal; แก้ filter bugs |
