# ออกแบบระบบ: Photobooth บน Cloud + Print Relay บน Local ผ่าน Cloudflare Tunnel (HTTPS)

> ใช้ **Cloudflare Tunnel** แทน ngrok เพื่อให้เซิร์ฟเวอร์บน cloud ส่งงานพิมพ์ไปยังเครื่องพิมพ์ที่ต่อกับเครื่อง local (Mac) ได้ผ่าน HTTPS

---

## 1. สถาปัตยกรรมระบบ

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  CLOUD (VPS / VM / etc.)                                                    │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │  Photobooth (PHP + Apache)                                            │  │
│  │  api/print.php → commands.print → สคริปต์ส่ง POST ไปยัง Relay URL     │  │
│  └─────────────────────────────────────┬──────────────────────────────────┘  │
└────────────────────────────────────────┼─────────────────────────────────────┘
                                         │ HTTPS POST (image body)
                                         ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│  CLOUDFLARE                                                                │
│  print.yourdomain.com (หรือ subdomain ที่ตั้งไว้)                            │
│  → รับ HTTPS แล้วส่งต่อไปยัง cloudflared ที่รันบน local                     │
└────────────────────────────────────────┬─────────────────────────────────────┘
                                         │
                                         ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│  LOCAL (Mac / PC ที่ต่อเครื่องพิมพ์)                                        │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │  cloudflared tunnel run print-relay                                   │  │
│  │  ingress: print.yourdomain.com → http://localhost:6631                │  │
│  └─────────────────────────────────────┬──────────────────────────────────┘  │
│                                        ▼                                     │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │  bin/print-relay (Python, port 6631)                                  │  │
│  │  รับ POST → ส่งต่อ lp → CUPS → เครื่องพิมพ์ (เช่น DNP QW410)          │  │
│  └──────────────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────────┘
```

**สรุป:** Cloud ส่งงานพิมพ์ผ่าน HTTPS ไปที่โดเมนของ Cloudflare → Cloudflare ส่งต่อให้ cloudflared บน local → print-relay บน local รับแล้วส่งให้ CUPS พิมพ์

---

## 2. สิ่งที่ต้องมี

| ส่วน | สิ่งที่ต้องมี |
|------|----------------|
| **Cloud** | Photobooth ติดตั้งแล้ว, PHP มี `curl` extension, สคริปต์ส่งงานไป Relay URL |
| **Local** | Python 3, CUPS + เครื่องพิมพ์ตั้งค่าแล้ว, `bin/print-relay`, cloudflared |
| **Cloudflare** | บัญชีฟรี + domain ที่ชี้ DNS มาที่ Cloudflare (หรือใช้ subdomain) |

---

## 3. การตั้งค่าบน Local (เครื่องที่ต่อเครื่องพิมพ์)

### 3.1 ติดตั้งและรัน Print Relay

```bash
# จากโฟลเดอร์โปรเจกต์ photobooth (หรือที่ที่เก็บ bin/print-relay)
chmod +x bin/print-relay

# รัน relay (พอร์ต 6631, ชื่อเครื่องพิมพ์ตามที่ lpstat -p)
nohup python3 bin/print-relay 6631 Dai_Nippon_Printing_DP_QW410 > /tmp/print-relay.log 2>&1 &

# ตรวจสอบ
curl http://localhost:6631/health
# ควรได้: OK
```

### 3.2 ติดตั้ง Cloudflare Tunnel (cloudflared)

```bash
# macOS
brew install cloudflare/cloudflare/cloudflared

# หรือดาวน์โหลดจาก https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/downloads/
```

### 3.3 สร้าง Named Tunnel สำหรับ Print Relay (ครั้งแรก)

```bash
# Login ครั้งแรก (เปิดเบราว์เซอร์)
cloudflared tunnel login

# สร้าง tunnel ชื่อ print-relay (ทำครั้งเดียว)
cloudflared tunnel create print-relay

# กำหนด subdomain ให้ชี้ไปที่ tunnel นี้
# แทนที่ yourdomain.com ด้วยโดเมนที่คุณจัดการผ่าน Cloudflare
cloudflared tunnel route dns print-relay print.yourdomain.com
```

จะได้ `TUNNEL_ID` (UUID) — ใช้ใน config ถัดไป

### 3.4 กำหนด Ingress: ชี้ subdomain ไปที่ localhost:6631

สร้างหรือแก้ไข `~/.cloudflared/config.yml`:

```yaml
tunnel: <TUNNEL_ID>   # ใส่ UUID จาก tunnel create
credentials-file: /Users/<username>/.cloudflared/<TUNNEL_ID>.json

ingress:
  - hostname: print.yourdomain.com
    service: http://localhost:6631
  - service: http_status:404
```

- แทนที่ `<TUNNEL_ID>` และ `<username>` ให้ตรงกับเครื่องคุณ
- ถ้ามี tunnel อื่นอยู่แล้ว (เช่น photobooth) ให้เพิ่มบล็อก `ingress` ของ print-relay ในไฟล์เดียวกัน โดยใช้หลาย hostname ต่อหนึ่งไฟล์ได้

### 3.5 รัน Tunnel

```bash
cloudflared tunnel run print-relay
```

เปิดไว้ตลอดที่ต้องการให้ cloud ส่งงานพิมพ์ได้  
ทดสอบจากเครื่องอื่นหรือจาก cloud:

```bash
curl -X POST --data-binary @/path/to/image.jpg \
  -H "Content-Type: image/jpeg" \
  https://print.yourdomain.com/print
```

---

## 4. การตั้งค่าบน Cloud (เซิร์ฟเวอร์ที่รัน Photobooth)

Photobooth ใช้ `commands.print` เป็นคำสั่งที่รับ **หนึ่งอาร์กิวเมนต์** เป็น path ไฟล์รูป (เช่น `lp %s`)  
บน cloud เราไม่รัน `lp` จริง แต่ใช้สคริปต์ที่ **ส่งเนื้อหาไฟล์ไปยัง Relay URL ผ่าน HTTPS**

### 4.1 สร้างสคริปต์ส่งงานไปยัง Print Relay (บน cloud)

สร้างไฟล์บนเซิร์ฟเวอร์ cloud เช่น `/usr/local/bin/print-to-relay`:

```bash
#!/bin/bash
# ส่งงานพิมพ์ไปยัง Print Relay ผ่าน Cloudflare Tunnel (HTTPS)
# ใช้กับ Photobooth บน cloud: commands.print = '/usr/local/bin/print-to-relay %s'

set -e
RELAY_URL="${PRINT_RELAY_URL:-https://print.yourdomain.com}"
FILE="${1:?Usage: print-to-relay <file>}"

if [ ! -f "$FILE" ]; then
  echo "File not found: $FILE" >&2
  exit 1
fi

CONTENT_TYPE="image/jpeg"
case "$(echo "$FILE" | tr '[:upper:]' '[:lower:]')" in
  *.png) CONTENT_TYPE="image/png" ;;
  *.pdf) CONTENT_TYPE="application/pdf" ;;
esac

curl -sf -X POST \
  --data-binary "@$FILE" \
  -H "Content-Type: $CONTENT_TYPE" \
  "$RELAY_URL/print"
```

- แทนที่ `https://print.yourdomain.com` ด้วย URL จริงของ tunnel ที่ชี้ไปที่ print-relay
- ตั้งค่าให้รันได้: `chmod +x /usr/local/bin/print-to-relay`

ถ้าต้องการไม่ hardcode URL ในสคริปต์ ให้ใช้ตัวแปรสภาพแวดล้อม เช่น:

```bash
# ใน systemd หรือ .env ของเว็บเซิร์ฟเวอร์
export PRINT_RELAY_URL=https://print.yourdomain.com
```

แล้วในสคริปต์ใช้ `RELAY_URL="${PRINT_RELAY_URL:-https://print.yourdomain.com}"` เหมือนด้านบน

### 4.2 ตั้งค่า Photobooth บน Cloud

ใน config ของ Photobooth (เช่น `config/my.config.inc.php` หรือผ่าน Admin Panel):

```php
'commands' => [
    'print' => '/usr/local/bin/print-to-relay %s',
    // ... commands อื่นๆ
],
```

เมื่อมีคำสั่งพิมพ์ Photobooth จะรัน `/usr/local/bin/print-to-relay /path/to/image.jpg` → สคริปต์จะ curl POST ไปที่ `https://print.yourdomain.com/print`

---

## 5. ความปลอดภัย (แนะนำ)

- **จำกัดผู้เรียก:** ใช้ Cloudflare Access กำหนดนโยบายให้เฉพาะ IP ของเซิร์ฟเวอร์ cloud หรือใช้ Service Token เฉพาะการเรียกจาก backend
- **HTTPS เท่านั้น:** Cloudflare Tunnel ให้ HTTPS อยู่แล้ว ไม่ต้องเปิดพอร์ต 6631 ออกอินเทอร์เน็ตโดยตรง
- **ไม่ใส่ URL relay ในที่สาธารณะ:** เก็บ `PRINT_RELAY_URL` ใน env หรือในสคริปต์บนเซิร์ฟเวอร์ ไม่ commit ลง repo สาธารณะ

---

## 6. Checklist สรุป (สำหรับ Agent / ผู้ deploy)

- [ ] **Local:** ติดตั้ง Python 3, CUPS, เครื่องพิมพ์ (`lpstat -p`)
- [ ] **Local:** รัน `bin/print-relay` พอร์ต 6631 และทดสอบ `curl http://localhost:6631/health`
- [ ] **Local:** ติดตั้ง cloudflared, login, สร้าง tunnel `print-relay`, route DNS `print.yourdomain.com`
- [ ] **Local:** กำหนด ingress ใน `~/.cloudflared/config.yml` ให้ `print.yourdomain.com` → `http://localhost:6631`
- [ ] **Local:** รัน `cloudflared tunnel run print-relay` (เปิดไว้ตลอดหรือใช้ systemd/launchd)
- [ ] **Cloud:** สร้างสคริปต์ `/usr/local/bin/print-to-relay` ที่ curl POST ไปที่ `https://print.yourdomain.com/print`
- [ ] **Cloud:** ตั้งค่า Photobooth `commands.print = '/usr/local/bin/print-to-relay %s'`
- [ ] **ทดสอบ:** จาก cloud รัน `print-to-relay /path/to/test.jpg` หรือถ่ายรูปแล้วกดพิมพ์จาก Photobooth

---

## 7. อ้างอิง

- โปรเจกต์นี้ใช้ **Cloudflare Tunnel (cloudflared)** แทน ngrok — URL คงที่ได้เมื่อใช้ Named Tunnel + DNS
- คู่มือ Dev/Deploy หลัก: [DEPLOY.md](../DEPLOY.md)
- สคริปต์ print-relay: [bin/print-relay](../bin/print-relay)
