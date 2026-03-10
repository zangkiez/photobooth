# Magazine Theme - Fun Edition

ธีม Photobooth สไตล์ Magazine/Street ญี่ปุ่น-เกาหลี ที่สนุกสนานและสดใส

## สไตล์และแนวคิด

- **แนว Magazine**: อินสไปร์จากปกแฟชั่นนิตยสารญี่ปุ่นและเกาหลี
- **Street/Pop Art**: สีสันสดใส กราฟิก bold ตัดกันชัดเจน
- **Playful**: ฟอนต์ rounded ดูเป็นมิตร animation สนุกสนาน

## ฟีเจอร์หลัก

### 1. ฟอนต์สนุกสนาน (Fun Fonts)

ใช้ฟอนต์ Bold Geometric Sans-Serif คล้าย Gotham Ultra พร้อมฟอนต์ playful:

- **Bungee** - Urban/condensed สำหรับหัวข้อใหญ่
- **Fredoka** - Rounded playful สำหรับ body text และปุ่ม
- **Kanit** - สำหรับภาษาไทย อ่านง่าย
- **Bebas Neue** - Magazine headlines สูงยาว
- **Comfortaa** - Rounded geometric

### 2. สีสันสดใส (Vibrant Colors)

```css
--fun-hot-pink: #FF006E;       /* ชมพูสด */
--fun-electric-blue: #00F5FF;  /* ฟ้าไฟฟ้า */
--fun-acid-green: #39FF14;     /* เขียวนีออน */
--fun-sunshine: #FFD700;       /* เหลืองสด */
--fun-vivid-purple: #BF00FF;     /* ม่วงสด */
--fun-coral: #FF6B35;          /* ส้มสด */
```

### 3. Animation สนุกสนาน

- **Button Wiggle** - ปุ่มสั่นไปมาเมื่อ hover
- **Title Bounce** - หัวข้อ bounce เข้ามา
- **Pulse Glow** - เอฟเฟกต์เรืองแสงที่ pulse
- **Sticker Pop** - สติกเกอร์ pop เข้ามาพร้อมหมุน
- **Speed Lines** - เส้นความเร็วแบบ manga ที่หน้า countdown
- **Multi-layer Shadow** - เงาหลายชั้นสีสัน

### 4. UI Elements

- **Corner Decorations** - มุมสีสันสดใส (แต่ละมุมสีต่างกัน)
- **Side Labels** - ป้ายซ้าย/ขวาแบบ magazine หมุน 90 องศา
- **Fun Stickers** - สติกเกอร์หมุน rotate พร้อม border หนา
- **Marquee Bar** - แถบวิ่งด้านล่างแบบ street style
- **Hard Shadows** - เงาคมชัดแบบ neo-brutalism

## ไฟล์ที่เกี่ยวข้อง

### CSS Files (9 ไฟล์)

| ไฟล์ | รายละเอียด | สถานะ |
|------|------------|-------|
| `resources/css/fonts-magazine.css` | ฟอนต์และ typography | ✓ พร้อมใช้งาน |
| `resources/css/magazine-fun.css` | สีสันและเอฟเฟกต์หลัก | ✓ พร้อมใช้งาน |
| `resources/css/magazine-v2.css` | ธีมหลัก v2 (start/loader/result) | ✓ พร้อมใช้งาน |
| `resources/css/magazine-layout.css` | layout components พื้นฐาน | ✓ พร้อมใช้งาน |
| `resources/css/start-magazine.css` | หน้าเริ่มต้น (detailed styling) | ✓ พร้อมใช้งาน |
| `resources/css/results-magazine.css` | หน้าผลลัพธ์ | ✓ พร้อมใช้งาน |
| `resources/css/filter-magazine.css` | หน้าเลือก filter | ✓ พร้อมใช้งาน |
| `resources/css/gallery-magazine.css` | หน้าแกลเลอรี่ | ✓ พร้อมใช้งาน |
| `resources/css/animations-magazine.css` | animation ต่างๆ | ✓ พร้อมใช้งาน |

### Template Files

| ไฟล์ | รายละเอียด | สถานะ |
|------|------------|-------|
| `template/components/stage.start.magazine.php` | หน้าเริ่มต้น | ✓ อัพเดทแล้ว |
| `template/components/stage.loader.php` | หน้า countdown | ✓ อัพเดทแล้ว |
| `template/components/stage.results.php` | หน้าผลลัพธ์ | ✓ ใช้งานได้ |
| `template/components/filter.php` | หน้า filter | ✓ ใช้งานได้ |
| `template/components/gallery.php` | โครงสร้าง gallery | ✓ ใช้งานได้ |
| `template/components/gallery.images.php` | รายการรูป gallery | ✓ อัพเดทแล้ว |
| `template/components/main.head.php` | โหลด CSS | ✓ อัพเดทแล้ว |

## การใช้งาน

### 1. เปิดใช้ Magazine Theme

ธีมนี้ถูกโหลดอัตโนมัติผ่าน `main.head.php`:

```php
<!-- Magazine Theme CSS -->
<link rel="stylesheet" href="resources/css/fonts-magazine.css" />
<link rel="stylesheet" href="resources/css/magazine-fun.css" />
<link rel="stylesheet" href="resources/css/magazine-v2.css" />
<link rel="stylesheet" href="resources/css/magazine-layout.css" />
<link rel="stylesheet" href="resources/css/start-magazine.css" />
<link rel="stylesheet" href="resources/css/results-magazine.css" />
<link rel="stylesheet" href="resources/css/filter-magazine.css" />
<link rel="stylesheet" href="resources/css/gallery-magazine.css" />
<link rel="stylesheet" href="resources/css/animations-magazine.css" />
```

```

### 2. ใช้งาน CSS Classes

#### Typography Classes
```html
<!-- ฟอนต์ display ใหญ่ -->
<h1 class="text-hero">PHOTO BOOTH</h1>
<h1 class="text-mega">EVENT NAME</h1>

<!-- ฟอนต์สนุก -->
<h2 class="text-playful">Fun Title</h2>
<h2 class="text-street">Street Style</h2>
<h2 class="text-magazine">Magazine Headline</h2>

<!-- เอฟเฟกต์ข้อความ -->
<h1 class="text-shadow-pop">Pop Art Shadow</h1>
<h1 class="text-shadow-neon">Neon Glow</h1>
<h1 class="text-shadow-sticker">Sticker Style</h1>
<h1 class="text-wavy">Wavy Animation</h1>
<h1 class="text-pulse">Pulse Animation</h1>
```

#### Button Classes
```html
<!-- ปุ่มสนุก -->
<button class="button-fun">Click Me</button>
<button class="button-fun button-fun--pink">Hot Pink</button>
<button class="button-fun button-fun--blue">Electric Blue</button>
<button class="button-fun button-fun--green">Acid Green</button>
<button class="button-fun button-fun--yellow">Sunshine</button>
<button class="button-fun button-fun--purple">Vivid Purple</button>
```

### 3. ใช้แค่คอลลาจ (TAP TO START = คอลลาจ)

ถ้าใช้โหมด **คอลลาจเท่านั้น** ไม่ใช้ฟีเจอร์ถ่ายรูปเดี่ยว:

- **Admin** → **Pictures**: ปิด **อนุญาตถ่ายรูป** (Picture enabled = off)
- **Admin** → **Collage**: เปิด **เปิดใช้คอลลาจ** (Collage enabled = on)

จากนั้นปุ่ม **TAP TO START** บนหน้าแรกจะเริ่มคอลลาจเท่านั้น และจะไม่มีปุ่มถ่ายรูปเดี่ยวในแถบปุ่ม

#### Sticker Classes
```html
<!-- สติกเกอร์ -->
<span class="sticker-fun">NEW</span>
<span class="sticker-fun sticker-fun--pink">HOT</span>
<span class="sticker-fun sticker-fun--blue">COOL</span>
<span class="sticker-fun sticker-fun--green">FRESH</span>
<span class="sticker-fun sticker-fun--yellow">POP</span>
<span class="sticker-fun sticker-fun--circle">SALE</span>
<span class="sticker-fun sticker-fun--outline">BOLD</span>
```

#### Frame Classes
```html
<!-- กรอบรูปแบบต่างๆ -->
<div class="frame-pop">Pop Frame</div>
<div class="frame-sticker">Sticker Frame</div>
<div class="frame-dashed">Dashed Frame</div>
<div class="frame-double">Double Frame</div>
```

#### Background Classes
```html
<!-- พื้นหลัง pattern -->
<div class="bg-halftone">Halftone</div>
<div class="bg-stripes">Stripes</div>
<div class="bg-checker">Checkerboard</div>
<div class="bg-burst">Burst Gradient</div>
<div class="bg-wave">Wave Pattern</div>
<div class="dots-pattern">Dots</div>
```

#### Animation Classes
```html
<!-- Animation เข้า -->
<div class="animate-bounce-in">Bounce In</div>
<div class="animate-pop-in">Pop In</div>
<div class="animate-slide-bounce">Slide Bounce</div>

<!-- Animation ต่อเนื่อง -->
<div class="animate-floating">Floating</div>
<div class="animate-pulse-scale">Pulse Scale</div>
<div class="text-bouncy">Bouncy Text</div>
<div class="text-wavy">Wavy Text</div>
<div class="text-pulse">Pulse Text</div>

<!-- Hover effects -->
<div class="hover-bounce">Hover Bounce</div>
<div class="hover-shake">Hover Shake</div>
<div class="hover-jello">Hover Jello</div>
<div class="text-rotate-hover">Rotate on Hover</div>
```

### 3. ปรับแต่งสี

แก้ไข CSS variables ในไฟล์ `magazine-fun.css`:

```css
:root {
  --fun-hot-pink: #FF006E;
  --fun-electric-blue: #00F5FF;
  --fun-acid-green: #39FF14;
  --fun-sunshine: #FFD700;
  --fun-vivid-purple: #BF00FF;
  --fun-coral: #FF6B35;
  --fun-turquoise: #00D4AA;
  --fun-lime: #CCFF00;
}
```

## Screens

### 1. Start Screen (หน้าเริ่มต้น)
- ✓ จัด center ทั้งแนวตั้งและแนวนอน (flexbox)
- ✓ Corner decorations 4 สี (pink, blue, green, yellow)
- ✓ Side labels "PHOTO" / "BOOTH" หมุน 90 องศา
- ✓ Fun stickers (EXCLUSIVE, WELCOME) พร้อม animation
- ✓ Marquee bar ด้านล่างแบบ street style
- ✓ Buttons สีสัน พร้อม wiggle animation เมื่อ hover
- ✓ รองรับ selfie mode และ buzzer mode

### 2. Loader/Countdown (หน้านับถอยหลัง)
- ✓ ไม่มี component บังกล้อง (ลบ message และ buttonbar ออก)
- ✓ Counter ขนาดใหญ่ center จริงๆ (min 10rem, max 25rem)
- ✓ Speed lines background แบบ manga
- ✓ "CHEESE!" text แบบ fun pop พร้อม multi-layer shadow
- ✓ Animation: counter pop, cheese bounce

### 3. Result Screen (หน้าผลลัพธ์)
- ✓ รูปภาพมี border หนา 8px + multi-color shadow
- ✓ รูปเอียง rotate(-2deg) เมื่อ hover ค่อยกลับเป็น 0deg
- ✓ Button bar ด้านล่างสีสัน แต่ละปุ่มสีต่างกัน
- ✓ QR code มี styling fun พร้อม shadow
- ✓ Messages (success/error) แบบ pop art พร้อม animation

### 4. Filter Screen (หน้าเลือก filter)
- ✓ Sidenav สไตล์ magazine มี header สีดำ
- ✓ Filter buttons มี preview thumbnail
- ✓ Active state สดใส (ดำ + ขาว + สีเน้น)
- ✓ Stagger animation เมื่อเปิด

### 5. Gallery Screen (หน้าแกลเลอรี่)
- ✓ Grid layout แบบ editorial (auto-fill minmax)
- ✓ Images มี border หนา และ shadow
- ✓ Hover effects: lift, rotate, shadow glow
- ✓ Empty state มี icon และ styling
- ✓ Lightbox (PhotoSwipe) ปรับแต่งสีสัน

## Responsive Support

ธีมรองรับทุกขนาดหน้าจอ:

### Desktop (> 1024px)
- Layout เต็มรูปแบบ
- Side labels แสดงเต็มที่
- กรอบรูปและ shadow ขนาดเต็ม

### Tablet (768px - 1024px)
- ปรับขนาด font ลงเล็กน้อย
- Spacing กระชับขึ้น
- Side labels ยังแสดง

### Mobile (< 768px)
- Layout กระชับ เหมาะสม
- Side labels ซ่อน (ไม่มีพื้นที่)
- Buttons เรียงกัน auto wrap
- Font sizes ปรับลดตาม viewport

## Browser Support

- ✓ Chrome/Chromium (แนะนำ)
- ✓ Firefox
- ✓ Safari (iOS/macOS)
- ✓ Edge
- ✓ Mobile browsers (iOS Safari, Chrome Android)

## การแก้ไขปัญหาเบื้องต้น

### 1. หน้าไม่ center
ตรวจสอบว่า CSS ทั้งหมดถูกโหลดใน `main.head.php`

### 2. Components บังกล้อง
ตรวจสอบว่า `stage.loader.php` ใช้ structure ที่ถูกต้อง (ไม่มี message/buttonbar)

### 3. สีไม่ตรง concept
ตรวจสอบว่า `magazine-fun.css` ถูกโหลดก่อน CSS อื่นๆ

### 4. Font ไม่แสดง
ตรวจสอบ internet connection (fonts โหลดจาก Google Fonts)

## Credits

- **Fonts**: Google Fonts (Kanit, Fredoka, Bungee, Comfortaa, Bebas Neue, Righteous, Black Ops One, Space Grotesk, Syne, Archivo Black)
- **Icons**: Font Awesome, Material Icons
- **Inspiration**: 
  - Japanese Fashion Magazines (Vogue Japan, Harajuku style)
  - Korean Street Fashion
  - Pop Art (Andy Warhol, Roy Lichtenstein)
  - Neo-Brutalism Design
  - Memphis Design Group

## License

ส่วนหนึ่งของ Photobooth Project

---

**หมายเหตุ**: ธีมนี้ออกแบบให้สนุกสนานและดึงดูดสายตา เหมาะสำหรับ event ที่ต้องการบรรยากาศ playful และ modern หากต้องการปรับแต่งเพิ่มเติม สามารถแก้ไข CSS variables ในไฟล์ต่างๆ ได้ตามต้องการ

---

## Changelog

### v2.0 - Fun Edition (Current)
- เพิ่มสีสัน vibrant (hot pink, electric blue, acid green)
- เพิ่ม animation สนุกสนาน (wiggle, bounce, pop)
- อัพเดททุก screen ให้ใช้สไตล์เดียวกัน
- แก้ไขปัญหา center alignment
- แก้ไขปัญหา components บังกล้อง
- เพิ่ม responsive support ครบทุกขนาดหน้าจอ

### v1.0 - Initial
- Magazine concept พื้นฐาน
- สีขาว-ดำ-ครีม
- Neo-brutalism styling
