# ติดตั้ง Photobooth บน Raspberry Pi และใช้ Cloudflare Tunnel

คู่มือนี้สรุปขั้นตอนการติดตั้ง Photobooth บน Raspberry Pi และการตั้งค่า Cloudflare Tunnel เพื่อให้เข้าถึง Photobooth จากอินเทอร์เน็ตได้ผ่านโดเมน (เช่น `https://photo.betweensunandmoon.coffee`) โดยผู้ใช้ไม่ต้องเชื่อม Wi-Fi เดียวกับตัวเครื่อง — แค่สแกน QR ก็ดาวน์โหลดรูปได้

---

## สิ่งที่ต้องเตรียม

- **Raspberry Pi** (แนะนำ Pi 4 หรือ Pi 5) พร้อม Raspberry Pi OS 64-bit (Bookworm ขึ้นไป)
- **กล้อง** ที่รองรับ (Camera Module, gphoto2 หรือ webcam)
- **บัญชี Cloudflare** และโดเมนที่ชี้ DNS ผ่าน Cloudflare แล้ว
- การเชื่อมต่ออินเทอร์เน็ตของ Raspberry Pi (สายหรือ Wi-Fi)

---

## ส่วนที่ 1: ติดตั้ง Photobooth บน Raspberry Pi

### 1.1 อัปเดตระบบ

```bash
sudo apt update
sudo apt dist-upgrade
```

### 1.2 ติดตั้งเว็บเซิร์ฟเวอร์และ PHP

**แบบ Apache (แนะนำสำหรับการติดตั้งครั้งแรก):**

```bash
sudo apt install -y libapache2-mod-php
```

**หรือแบบ Nginx (ใช้ทรัพยากรน้อยกว่า):**

```bash
sudo apt install -y nginx php-fpm
```

ถ้าใช้ Nginx ต้องตั้งค่าให้รัน PHP ตาม [Enable PHP in Nginx](install-nginx.md)

### 1.3 ติดตั้ง dependencies

```bash
sudo apt install -y curl gcc g++ make git ffmpeg gphoto2 libimage-exiftool-perl nodejs php-xml php-gd php-zip php-mbstring python3 python3-gphoto2 python3-psutil python3-zmq rsync udisks2 v4l2loopback-dkms v4l-utils
```

- ต้องใช้ **Node.js v20 ขึ้นไป** และ **PHP 8.4 ขึ้นไป**  
- ถ้า Raspberry Pi OS มีเวอร์ชันต่ำกว่า ให้ติดตั้ง/อัปเกรดตาม [Prerequisites](prerequisites.md) หรือใช้ [Photobooth Setup Wizard](https://photoboothproject.github.io/install/setup_wizard) แทน

### 1.4 โคลนและ build Photobooth

```bash
sudo chown -R www-data:www-data /var/www/
cd /var/www/
sudo -u www-data git clone https://github.com/PhotoboothProject/photobooth html
cd /var/www/html
sudo -u www-data git submodule update --init
sudo -u www-data npm install
sudo -u www-data npm run build
```

หมายเหตุ: `npm install` และ `npm run build` อาจใช้เวลาถึงประมาณ 15 นาที

### 1.5 สิทธิ์สำหรับกล้องและเครื่องพิมพ์ (ถ้าใช้)

```bash
# กล้อง USB
sudo gpasswd -a www-data plugdev

# เครื่องพิมพ์ (CUPS)
sudo apt install -y cups
sudo gpasswd -a www-data lp
sudo gpasswd -a www-data lpadmin
sudo cupsctl --remote-any
sudo systemctl restart cups
```

### 1.6 รีสตาร์ทแล้วทดสอบ

```bash
sudo reboot
```

หลังบูตแล้ว เปิดเบราว์เซอร์ไปที่ `http://<IP ของ Pi>` (หรือ `http://localhost` ถ้าใช้บนตัว Pi) ควรเห็นหน้า Photobooth

---

## ส่วนที่ 2: ติดตั้งและตั้งค่า Cloudflare Tunnel

Cloudflare Tunnel (cloudflared) ทำให้บริการบน Raspberry Pi เข้าถึงจากอินเทอร์เน็ตผ่านโดเมนของ Cloudflare ได้ โดยไม่ต้องเปิด port ที่เราเตอร์หรือตั้งค่า port forwarding

### 2.1 ติดตั้ง cloudflared บน Raspberry Pi

Raspberry Pi 4/5 ใช้สถาปัตยกรรม **ARM64**:

```bash
# ดาวน์โหลด .deb ล่าสุด (ARM64)
cd /tmp
wget https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-arm64.deb
sudo dpkg -i cloudflared-linux-arm64.deb
cloudflared --version
```

ถ้าใช้ Pi รุ่นเก่า (32-bit) ให้ใช้ `cloudflared-linux-arm.deb` แทน

### 2.2 ล็อกอิน Cloudflare (ครั้งแรกเท่านั้น)

```bash
cloudflared tunnel login
```

คำสั่งนี้จะเปิดเบราว์เซอร์ให้คุณเลือกโดเมนและยืนยัน — หลังล็อกอิน Cloudflare จะบันทึก certificate ไว้ที่เครื่องนี้

### 2.3 สร้าง Tunnel

```bash
# สร้าง tunnel ชื่อ photobooth (หรือชื่ออื่นตามต้องการ)
cloudflared tunnel create photobooth
```

หลังสร้างเสร็จจะได้ **Tunnel ID** (รูปแบบ UUID) — จำหรือ copy ไว้

ดูรายการ tunnel ทั้งหมด:

```bash
cloudflared tunnel list
```

### 2.4 สร้างไฟล์ config สำหรับ tunnel

สร้างไฟล์ config (เช่น `/etc/cloudflared/config.yml`):

```bash
sudo mkdir -p /etc/cloudflared
sudo nano /etc/cloudflared/config.yml
```

ใส่เนื้อหา (แก้ `tunnel` และ `credentials-file` ให้ตรงกับ Tunnel ID ของคุณ และแก้โดเมนเป็นของคุณ):

```yaml
tunnel: <TUNNEL_ID>
credentials-file: /home/pi/.cloudflared/<TUNNEL_ID>.json

ingress:
  - hostname: photo.betweensunandmoon.coffee
    service: http://localhost:80
  - service: http_status:404
```

**หมายเหตุ:** ถ้ารัน `cloudflared` เป็น systemd service (ดังขั้นตอนถัดไป) process มักรันในฐานะ root และอาจอ่านไฟล์ใน `/home/pi/` ไม่ได้ ให้ copy ไฟล์ credentials ไปที่ `/etc/cloudflared/` แล้วชี้ใน config เช่น:

```bash
sudo cp /home/pi/.cloudflared/<TUNNEL_ID>.json /etc/cloudflared/
```

จากนั้นใน `config.yml` ใช้ `credentials-file: /etc/cloudflared/<TUNNEL_ID>.json`

- แทนที่ `<TUNNEL_ID>` ด้วย Tunnel ID จริง  
- ถ้า Photobooth อยู่ใต้ path ย่อย (เช่น `http://localhost/photobooth`) ให้ใช้ `service: http://localhost:80/photobooth`  
- ถ้าใช้ Nginx และ listen port อื่น (เช่น 8080) ให้ใช้ `service: http://localhost:8080`

ถ้า `credentials-file` อยู่ที่อื่น (เช่นของ user `www-data`) ให้ชี้ path ให้ถูกต้อง

### 2.5 กำหนด DNS ใน Cloudflare

ให้ Cloudflare ชี้ subdomain ไปที่ tunnel:

```bash
cloudflared tunnel route dns photobooth photo.betweensunandmoon.coffee
```

หรือสร้าง CNAME ใน Cloudflare Dashboard เอง:

- **Type:** CNAME  
- **Name:** `photo` (หรือ subdomain ที่ต้องการ)  
- **Target:** `<TUNNEL_ID>.cfargotunnel.com`  
- **Proxy status:** Proxied (สีส้ม)

### 2.6 รัน cloudflared เป็น systemd service

```bash
sudo cloudflared service install
sudo systemctl start cloudflared
sudo systemctl enable cloudflared
sudo systemctl status cloudflared
```

จากนี้เมื่อเปิดเครื่อง Pi แล้ว cloudflared จะรันอัตโนมัติ และ `https://photo.betweensunandmoon.coffee` จะชี้มาที่ Photobooth บน Pi

---

## ส่วนที่ 3: ตั้งค่า Photobooth ให้ใช้ URL ผ่าน Tunnel

เพื่อให้ลิงก์และ QR Code ใช้โดเมนของ Cloudflare Tunnel (เช่น `https://photo.betweensunandmoon.coffee`) แทนที่อยู่จาก request (เช่น DDEV หรือ IP ในเครือข่าย):

1. เปิด **Admin Panel** ของ Photobooth (เช่น `https://photo.betweensunandmoon.coffee/admin`)
2. ไปที่ **General** (หรือ **ทั่วไป**)
3. หาช่อง **Webserver URL** (หรือ **URL เว็บเซิร์ฟเวอร์**) — อยู่ในมุมมอง Advanced
4. ใส่ URL ฐานของ Tunnel แล้วบันทึก เช่น:
   - ถ้า Photobooth อยู่ที่ root: `https://photo.betweensunandmoon.coffee`
   - ถ้าอยู่ใน subfolder (เช่น `/photobooth/`): `https://photo.betweensunandmoon.coffee/photobooth`
5. (ถ้าต้องการ) ไปที่ส่วน **QR** แล้วตั้ง **URL สำหรับ QR Code** เป็น path เทียบกับโดเมนด้านบน เช่น `view.php?image=` — ระบบจะใช้ Webserver URL ด้านบนเป็นฐานให้อัตโนมัติ

เมื่อตั้ง **Webserver URL** แล้ว ลิงก์เหล่านี้จะใช้โดเมน Tunnel:
- QR Code (api/qrcode.php)
- QR บนรูปพิมพ์ (api/print.php)
- หน้า view.php (รูปและปุ่มดาวน์โหลด)

---

## สรุปการทำงาน

| ขั้นตอน | คำอธิบาย |
|--------|-----------|
| Photobooth บน Pi | รันเว็บและถ่ายรูปบนเครือข่ายท้องถิ่น |
| Cloudflare Tunnel | cloudflared สร้าง tunnel ออกไปยัง Cloudflare แล้วส่ง traffic จากโดเมนมายัง `localhost` |
| DNS | โดเมน `photo.betweensunandmoon.coffee` ชี้ไปที่ tunnel |
| QR URL | ตั้งใน Admin เป็น `https://photo.../view.php?image=` เพื่อให้ผู้ใช้สแกนแล้วเข้าโดเมนสาธารณะและดาวน์โหลดรูปได้ทันที |

---

## แก้ปัญหาเบื้องต้น

- **เข้า https://photo... ไม่ได้**  
  - ตรวจสอบ `sudo systemctl status cloudflared` ว่า running  
  - ตรวจสอบ DNS ว่า CNAME ชี้ไปที่ `<TUNNEL_ID>.cfargotunnel.com`  
  - ดู log: `sudo journalctl -u cloudflared -f`

- **QR สแกนแล้วโหลดรูปไม่ได้**  
  - ตรวจสอบว่า QR URL ใน Admin ตรงกับโดเมนที่ใช้ (รวม path ถ้ามี)  
  - ทดสอบเปิด `https://photo.../view.php?image=ชื่อไฟล์.jpg` ในเบราว์เซอร์โดยตรง

- **อ้างอิงการติดตั้ง Photobooth แบบละเอียด**  
  - [Install on Debian](install-debian.md)  
  - [Photobooth Setup Wizard](https://photoboothproject.github.io/install/setup_wizard)  
  - [Prerequisites](prerequisites.md)

- **เอกสาร Cloudflare Tunnel**  
  - [Cloudflare Tunnel - Install and setup](https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/install-and-setup/installation/)  
  - [Create a tunnel](https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/install-and-setup/tunnel-guide/)
