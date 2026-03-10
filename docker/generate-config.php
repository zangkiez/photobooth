#!/usr/bin/env php
<?php
/**
 * สร้าง config/my.config.inc.php จากตัวแปรสภาพแวดล้อม (ใช้ใน Docker)
 * ใช้ทั้ง dev และ production — ไม่ต้อง mount ไฟล์ config จาก host
 *
 * ตัวแปรที่รองรับ (ถ้าไม่ตั้ง จะไม่ override ใน config):
 *   PHOTOBOOTH_WEBSERVER_URL   → webserver.url, qr.url (ต่อท้าย view.php?image=)
 *   PHOTOBOOTH_PRINTER_NAME   → commands.print = "lp -d <name> -o fit-to-page %s"
 *   PHOTOBOOTH_PRINT_ENABLED  → print.enabled (1/true/yes = เปิด)
 *   PHOTOBOOTH_PREVIEW_MODE   → preview.mode (device_cam, gphoto2, libcamera, url)
 *   PHOTOBOOTH_TZ             → ui.local_timezone (default Asia/Bangkok)
 */
$baseDir = getenv('PHOTOBOOTH_BASE_DIR') ?: '/var/www/html';
$configFile = $baseDir . '/config/my.config.inc.php';

$overrides = [];

if (($v = getenv('PHOTOBOOTH_WEBSERVER_URL')) !== false && $v !== '') {
    $overrides['webserver']['url'] = rtrim($v, '/');
    $overrides['qr']['url'] = rtrim($v, '/') . '/view.php?image=';
}

if (($v = getenv('PHOTOBOOTH_TZ')) !== false && $v !== '') {
    $overrides['ui']['local_timezone'] = $v;
}

if (($v = getenv('PHOTOBOOTH_PRINTER_NAME')) !== false && $v !== '') {
    $overrides['commands']['print'] = 'lp -d ' . str_replace(["'", '"'], '', $v) . ' -o fit-to-page %s';
    $overrides['print']['enabled'] = true;
}

if (($v = getenv('PHOTOBOOTH_PRINT_ENABLED')) !== false && $v !== '') {
    $overrides['print']['enabled'] = in_array(strtolower($v), ['1', 'true', 'yes', 'on'], true);
}

if (($v = getenv('PHOTOBOOTH_PREVIEW_MODE')) !== false && $v !== '') {
    $overrides['preview']['mode'] = $v;
}

// ไม่ overwrite ถ้ามีไฟล์อยู่แล้วและไม่ได้บังคับ generate
if (file_exists($configFile) && getenv('PHOTOBOOTH_FORCE_GENERATE_CONFIG') !== '1' && getenv('PHOTOBOOTH_FORCE_GENERATE_CONFIG') !== 'true') {
    return;
}

// Default สำหรับ Docker
if (empty($overrides['webserver']['url'])) {
    $overrides['webserver']['url'] = getenv('PHOTOBOOTH_WEBSERVER_URL') ?: 'http://localhost';
    $overrides['qr']['url'] = rtrim($overrides['webserver']['url'], '/') . '/view.php?image=';
}

if (empty($overrides)) {
    return;
}

$dir = dirname($configFile);
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$export = var_export($overrides, true);
$content = "<?php\n// Auto-generated from env — do not edit by hand\nreturn $export;\n";
file_put_contents($configFile, $content);
chmod($configFile, 0644);
