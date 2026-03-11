# DEV_GUIDE — คู่มือนักพัฒนา Photobooth

> **กฎเหล็ก**: อย่าแก้ไขไฟล์ใน `resources/` โดยตรง — ไฟล์เหล่านั้นถูก **generate** ทั้งหมด
> แก้ที่ `assets/` เสมอ แล้ว build → สิ่งที่แก้จะเข้า `resources/` อัตโนมัติ

---

## สารบัญ

1. [โครงสร้างโปรเจกต์](#1-โครงสร้างโปรเจกต์)
2. [แก้ JS หน้าบ้าน (Frontend)](#2-แก้-js-หน้าบ้าน-frontend)
3. [แก้ JS Admin Panel](#3-แก้-js-admin-panel)
4. [แก้ CSS / SCSS](#4-แก้-css--scss)
5. [เพิ่ม / แก้ Config](#5-เพิ่ม--แก้-config)
6. [แก้ PHP API](#6-แก้-php-api)
7. [Build และ Dev Server](#7-build-และ-dev-server)
8. [ขั้นตอน Deploy](#8-ขั้นตอน-deploy)

---

## 1. โครงสร้างโปรเจกต์

```
photobooth/
├── assets/                  ← SOURCE (แก้ที่นี่เท่านั้น)
│   ├── js/
│   │   ├── core.js          ← Logic หลักหน้าบ้าน (stage, filter, gallery)
│   │   ├── photoswipe.js    ← Gallery lightbox + download + print
│   │   ├── slideshow.js     ← Slideshow + PhotoSwipe init
│   │   ├── tools.js         ← Utility functions (shared)
│   │   ├── admin/           ← JS เฉพาะ Admin Panel
│   │   │   ├── index.js
│   │   │   ├── buttons.js
│   │   │   ├── generator.js ← Collage layout generator
│   │   │   ├── imageSelect.js
│   │   │   └── ...
│   │   └── ...
│   └── sass/
│       ├── framework.scss   ← CSS หลักหน้าบ้าน
│       ├── fonts.scss       ← Web fonts
│       ├── tailwind.admin.scss ← Admin Panel (Tailwind)
│       └── components/      ← SCSS components แยกย่อย
│
├── resources/               ← OUTPUT (auto-generated, ห้ามแก้)
│   ├── js/                  ← Compiled JS (Babel ES5)
│   │   ├── core.js
│   │   ├── photoswipe.js
│   │   ├── main.admin.js    ← assets/js/admin/* ทั้งหมด concat รวมกัน
│   │   └── ...
│   ├── css/                 ← Compiled CSS
│   └── revisions.json       ← Cache-busting hash manifest
│
├── private/
│   └── sass/
│       └── _custom.scss     ← CSS override ส่วนตัว (ไม่ถูก commit)
│
├── src/
│   ├── Configuration/
│   │   ├── PhotoboothConfiguration.php  ← ประกาศ config sections ทั้งหมด
│   │   └── Section/
│   │       ├── CollageConfiguration.php ← default + validation ของแต่ละ section
│   │       ├── PrintConfiguration.php
│   │       └── ...
│   └── Service/
│       └── ConfigurationService.php     ← Load / merge / save config
│
├── lib/
│   ├── boot.php             ← Bootstrap: load $config, session, CSRF
│   └── configsetup.inc.php  ← Admin panel UI definition (form fields)
│
├── config/
│   └── my.config.inc.php    ← User overrides (ไม่ถูก commit ใน prod)
│
├── api/
│   ├── applyEffects.php     ← ประมวลผล filter, GIF, MP4
│   ├── capture.php          ← ถ่ายภาพ / video
│   └── ...
│
└── gulpfile.mjs             ← Build tasks
```

---

## 2. แก้ JS หน้าบ้าน (Frontend)

### ไฟล์หลักและหน้าที่

| ไฟล์ (`assets/js/`) | หน้าที่ |
|---|---|
| `core.js` | Stage flow (start→loader→result), filter, gallery, GIF/MP4 |
| `photoswipe.js` | Lightbox gallery, download URL, print button |
| `slideshow.js` | Auto slideshow + PhotoSwipe initialisation |
| `tools.js` | Utility functions ที่ใช้ร่วมกัน (shared กับ admin) |

### วิธีแก้ไข

```bash
# 1. เปิดไฟล์ source
code assets/js/core.js

# 2. แก้ไข ES6 ตามต้องการ

# 3. Build
ddev exec npm run build:gulp

# 4. ตรวจสอบ browser ใช้ resources/js/core.js (compiled)
```

### ตัวแปรและ function สำคัญใน `core.js`

```js
// Stage management (อย่า manipulate class ตรง ๆ — ใช้ function เหล่านี้เสมอ)
setActiveStage('start')   // แสดงหน้าหลัก
setActiveStage('loader')  // แสดง loading / countdown
setActiveStage('result')  // แสดงผลลัพธ์

// Session tracking (reset ทุกครั้งที่ถ่ายใหม่)
captureBasename           // ชื่อไฟล์พื้นฐาน เช่น "20260310_154132.jpg"
sessionFiles              // array ของไฟล์ทั้งหมดในการถ่ายครั้งนี้ (รวม filter variants)

// Filter
imgFilter                 // filter ปัจจุบัน (string เช่น "blur", "plain")
window.photoboothCurrentFilter  // expose ให้ photoswipe.js อ่านได้
getFilterString(f)        // normalize filter value เป็น string

// Reprocess guard (เมื่อเปลี่ยน filter บน result page)
const isFilterReprocess = resultPage.hasClass('stage--active') || !!resultPage.attr('data-img');
// → ถ้า true = กำลัง reprocess filter (ไม่ใช่ถ่ายใหม่)
// → ส่ง reprocess: isFilterReprocess ? 1 : 0 ให้ PHP เสมอ
```

### การเพิ่ม feature ใน `core.js`

ตัวอย่าง: เพิ่มปุ่ม "Share" หลังถ่ายภาพ

```js
// ใน api.renderPic() — ต่อจาก buttonPrint handler
resultPage
    .find('.sharebtn')
    .off('click')
    .on('click', () => {
        const shareUrl = environment.publicFolders.images + '/' + filename;
        navigator.share({ url: shareUrl });
    });
```

---

## 3. แก้ JS Admin Panel

### ไฟล์และลำดับ concat

Gulp **รวม** ไฟล์ทั้งหมดต่อไปนี้เป็น `resources/js/main.admin.js` ตามลำดับ:

```
assets/js/tools.js
assets/js/admin/index.js
assets/js/admin/buttons.js
assets/js/admin/navi.js
assets/js/admin/keypad.js
assets/js/admin/imageSelect.js
assets/js/admin/fontSelect.js
assets/js/admin/videoSelect.js
assets/js/admin/themes.js
assets/js/admin/toast.js
```

> **หมายเหตุ**: `assets/js/admin/generator.js` **ไม่ถูก** concat เข้า `main.admin.js`
> มันถูก compile แยกเป็น `resources/js/admin/generator.js`

### วิธีแก้ไข

```bash
# แก้ไขใน assets/js/admin/
code assets/js/admin/buttons.js

# Build
ddev exec npm run build:gulp
```

### เพิ่มไฟล์ admin JS ใหม่

1. สร้างไฟล์ `assets/js/admin/myfeature.js`
2. เพิ่มในลำดับ `gulp.task('js-admin')` ใน `gulpfile.mjs`:

```js
gulp.task('js-admin', function () {
  return gulp
    .src([
      './assets/js/tools.js',
      './assets/js/admin/index.js',
      // ... ของเดิม ...
      './assets/js/admin/toast.js',
      './assets/js/admin/myfeature.js',  // ← เพิ่มตรงนี้
    ])
```

3. Build ใหม่

---

## 4. แก้ CSS / SCSS

### โครงสร้าง

| Source | Output | ใช้สำหรับ |
|---|---|---|
| `assets/sass/framework.scss` | `resources/css/framework.css` | หน้าบ้านหลัก |
| `assets/sass/fonts.scss` | `resources/css/fonts.css` | Web fonts |
| `assets/sass/tailwind.admin.scss` | `resources/css/tailwind.admin.css` | Admin Panel (Tailwind) |
| `assets/sass/photoswipe-bottom.scss` | `resources/css/photoswipe-bottom.css` | Gallery bottom bar |
| `private/sass/_custom.scss` | (import เข้า framework) | Override ส่วนตัว |

### วิธีแก้ไข

```bash
# แก้ CSS หน้าบ้าน
code assets/sass/framework.scss
# หรือแก้ใน components:
code assets/sass/components/_stage.scss

# แก้ admin
code assets/sass/tailwind.admin.scss

# Override ส่วนตัว (ไม่ commit)
code private/sass/_custom.scss

# Build
ddev exec npm run build:gulp
# หรือ build เฉพาะ CSS
ddev exec npx gulp sass
ddev exec npx gulp tailwind-admin
```

---

## 5. เพิ่ม / แก้ Config

Config system ทำงาน 3 ชั้น:

```
src/Configuration/Section/XxxConfiguration.php   ← ประกาศ key + default + validation
       ↓
config/my.config.inc.php                         ← user override (เฉพาะที่ต่างจาก default)
       ↓
lib/boot.php: $config = ConfigurationService::getInstance()->getConfiguration()
       ↓
ทุก PHP file ใช้ $config['section']['key']
```

### ขั้นตอนเพิ่ม config key ใหม่

**ตัวอย่าง**: เพิ่ม `collage.my_new_option` (boolean, default: false)

#### Step 1 — ประกาศ default ใน `src/Configuration/Section/CollageConfiguration.php`

```php
->children()
    ->booleanNode('enabled')->defaultValue(true)->end()
    // ... ของเดิม ...
    ->booleanNode('my_new_option')->defaultValue(false)->end()  // ← เพิ่ม
->end()
```

ประเภท node ที่ใช้บ่อย:

| PHP method | ใช้สำหรับ |
|---|---|
| `->booleanNode('key')->defaultValue(false)` | true/false |
| `->integerNode('key')->defaultValue(3)->min(0)->max(10)` | ตัวเลขจำนวนเต็ม |
| `->scalarNode('key')->defaultValue('plain')` | string |
| `->floatNode('key')->defaultValue(1.0)` | ทศนิยม |
| `->arrayNode('key')->scalarPrototype()->end()` | array of strings |

#### Step 2 — เพิ่ม field ใน Admin Panel (`lib/configsetup.inc.php`)

```php
// หา section 'collage' แล้วเพิ่ม setting ใหม่
'collage_my_new_option' => [
    'view' => 'expert',   // 'basic' | 'advanced' | 'expert'
    'type' => 'checkbox',
    'name' => 'collage[my_new_option]',
    'value' => $config['collage']['my_new_option'],
],
```

ประเภท input:

| `type` | ใช้สำหรับ |
|---|---|
| `'checkbox'` | boolean |
| `'input'` | text string |
| `'number'` | ตัวเลข |
| `'range'` | slider (ต้องมี `range_min`, `range_max`, `range_step`) |
| `'select'` | dropdown (ต้องมี `'options' => [...]`) |
| `'color'` | color picker |

#### Step 3 — ใช้งานใน PHP API

```php
// api/applyEffects.php (หรือ PHP file อื่น)
if (!empty($config['collage']['my_new_option'])) {
    // ทำอะไรบางอย่าง
}
```

#### Step 4 — ใช้งานใน JS

Config PHP ถูก inject เข้า JS ผ่าน global `config` ใน `index.php`:

```js
// assets/js/core.js หรือ photoswipe.js
if (config.collage.my_new_option) {
    // ทำอะไรบางอย่าง
}
```

#### Step 5 — Set ใน `config/my.config.inc.php` (user override)

```php
return [
    'collage' => [
        'my_new_option' => true,
    ],
];
```

> ไฟล์นี้เก็บ **เฉพาะส่วนที่แตกต่างจาก default** — ไม่ต้องใส่ทุก key

---

## 6. แก้ PHP API

### Pattern มาตรฐาน

ทุก API file เริ่มด้วย:

```php
<?php
require_once '../lib/boot.php';  // โหลด $config, session, CSRF

header('Content-Type: application/json');
checkCsrfOrFail($_POST);         // ตรวจสอบ CSRF token

// ... logic ...

echo json_encode(['success' => true, 'file' => $filename]);
```

### `api/applyEffects.php` — จุดสำคัญ

```php
// รับ reprocess flag จาก JS
$isReprocess = !empty($_POST['reprocess']) && (string)$_POST['reprocess'] === '1';

// Guard สำคัญ: ไม่สร้าง GIF ซ้ำเมื่อเปลี่ยน filter
if (
    !$isReprocess &&              // ← ต้องเช็คตรงนี้ก่อนสร้าง GIF/MP4
    $vars['isCollage'] &&
    !empty($config['collage']['slideshow_enabled']) &&
    ...
) {
    // สร้าง slideshow GIF / MP4
}

// Filename ของ filter variant
// เมื่อ $isReprocess = true → ชื่อไฟล์จะเป็น original_blur.jpg (ไม่ overwrite ต้นฉบับ)
if ($isReprocess && $vars['imageFilter'] !== null && $vars['imageFilter'] !== ImageFilterEnum::PLAIN) {
    $outputFilename = pathinfo($vars['fileName'], PATHINFO_FILENAME)
                    . '_' . $vars['imageFilter']->value
                    . '.' . pathinfo($vars['fileName'], PATHINFO_EXTENSION);
}
```

---

## 7. Build และ Dev Server

### คำสั่งหลัก

```bash
# Start dev server (รัน Docker + gulp watch อัตโนมัติ)
ddev start

# Build ทุกอย่าง (JS + CSS + revisions)
ddev exec npm run build:gulp

# Build เฉพาะ JS
ddev exec npx gulp js

# Build เฉพาะ Admin JS
ddev exec npx gulp js-admin

# Build เฉพาะ SCSS
ddev exec npx gulp sass

# Build เฉพาะ Tailwind Admin
ddev exec npx gulp tailwind-admin

# Watch mode (auto-rebuild เมื่อแก้ไขไฟล์)
ddev exec npm run watch:gulp

# Format + lint JS
ddev exec npm run format
ddev exec npm run eslint

# ตรวจสอบ PHP code style
ddev exec php bin/composer lint
```

### เมื่อ `ddev start` รัน — Gulp watch ทำงานอัตโนมัติ

DDEV config ตั้งค่า hooks ให้รัน `npm run watch:gulp` ใน background อยู่แล้ว
ทันทีที่บันทึกไฟล์ใน `assets/js/` หรือ `assets/sass/` → resources จะ rebuild เอง

### ตรวจสอบ Node version

```bash
ddev exec node --version   # ต้องได้ v20+ (Docker มี v24)
node --version             # บน Mac อาจเป็น v18 (ใช้ build ตรงไม่ได้)
```

> **สำคัญ**: Build ผ่าน `ddev exec` เสมอ ไม่ใช่ `npm run build:gulp` ตรง ๆ บน Mac
> เพราะ Mac มี Node 18 ซึ่ง `lightningcss` ต้องการ Node ≥20

---

## 8. ขั้นตอน Deploy

### Workflow มาตรฐาน

```
แก้ assets/js/ หรือ assets/sass/
         ↓
ddev exec npm run build:gulp
         ↓
ตรวจสอบ resources/ มีการเปลี่ยนแปลง
         ↓
git add assets/ resources/ config/ api/ src/ lib/
         ↓
git commit -m "feat: ..."
         ↓
git push
```

### ไฟล์ที่ต้อง commit เสมอ

| เมื่อแก้ | ต้อง commit ด้วย |
|---|---|
| `assets/js/core.js` | `resources/js/core.js` |
| `assets/js/photoswipe.js` | `resources/js/photoswipe.js` |
| `assets/js/admin/*.js` | `resources/js/main.admin.js` |
| `assets/sass/*.scss` | `resources/css/*.css` |
| ไฟล์ `resources/` ใด ๆ | `resources/revisions.json` (auto-updated by gulp) |

### ไฟล์ที่ **ไม่** commit ใน Production

```
config/my.config.inc.php    ← เฉพาะ server นั้น ๆ
private/sass/_custom.scss   ← custom override ส่วนตัว
data/images/                ← รูปภาพที่ถ่าย
data/thumbs/
data/tmp/
```

---

## Appendix — Quick Reference

### แก้แล้ว browser ไม่ update?

```bash
# Force cache bust: revisions.json จะ update hash ทุกครั้งที่ build
ddev exec npm run build:gulp

# แล้วกด Shift+Reload ใน browser
```

### ดู log PHP

```bash
ddev logs -f
# หรือตรวจ data/ folder สำหรับ photobooth log
```

### ตรวจสอบ config ที่ active

```bash
# ดู effective config ทั้งหมด
ddev exec php -r "
  require 'lib/boot.php';
  echo json_encode(\$config, JSON_PRETTY_PRINT);
"
```

### โครงสร้าง Stage Flow (core.js)

```
[Start Page]
     ↓ กด take photo
setActiveStage('loader')
     ↓ countdown + ถ่ายภาพ
capture.php → applyEffects.php
     ↓ success
setActiveStage('result')
     ↓ กด filter
isFilterReprocess=true → applyEffects.php?reprocess=1
     ↓ กด new photo / reset
setActiveStage('start') + reset sessionFiles + captureBasename
```
