<?php

use Photobooth\Service\LanguageService;
use Photobooth\Service\PrintManagerService;
use Photobooth\Utility\ComponentUtility;

$languageService = LanguageService::getInstance();
$printManager = PrintManagerService::getInstance();

echo '<div class="buttonbar">';
if ($config['button']['force_buzzer']) {
    echo '<div class="buzzer-message">' . $config['button']['buzzer_message'] . '</div>';
} else {
    if ($config['picture']['enabled'] && empty($config['collage']['enabled'])) {
        // ปุ่มถ่ายเดี่ยว: แสดงใน buttonbar เฉพาะเมื่อปิด collage (ถ้าเปิด collage ใช้ TAP TO START เท่านั้น)
        echo ComponentUtility::renderButton('takePhoto', $config['icons']['take_picture'], 'takePic');
    }
    if ($config['custom']['enabled']) {
        echo ComponentUtility::renderButton($config['custom']['btn_text'], $config['icons']['take_custom'], 'takeCustom');
    }
    if ($config['collage']['enabled']) {
        // ปุ่มคอลลาจอยู่ที่ magazine-visual ใน stage template แล้ว ไม่ render ซ้ำ
    }
    if ($config['video']['enabled']) {
        echo ComponentUtility::renderButton('takeVideo', $config['icons']['take_video'], 'takeVideo');
    }
}
if ($config['button']['reload']) {
    echo ComponentUtility::renderButton('reload', $config['icons']['refresh'], 'reload');
}
if ($config['gallery']['enabled'] && ($config['gallery']['show_on_main_app'] ?? true)) {
    echo ComponentUtility::renderButton('gallery', $config['icons']['gallery'], 'gallery-button');
}
if ($config['button']['show_cups']) {
    echo ComponentUtility::renderButton('cups', $config['icons']['cups'], 'cups-button');
}
if ($config['button']['show_printUnlock']) {
    echo ComponentUtility::renderButton('reset_lock', $config['icons']['print'], 'print-unlock-button', true, $printManager->isPrintLocked() ? [] : ['class' => 'hidden']);
}
echo '</div>';
