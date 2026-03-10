<?php

return [
    'ui' => [
        'language' => 'th',
        'local_timezone' => 'Asia/Bangkok',
        'show_fork' => false,
    ],
    'adminpanel' => [
        'view' => 'expert',
    ],
    'webserver' => [
        'url' => 'https://photo.betweensunandmoon.coffee',
    ],
    'screensaver' => [
        'enabled' => true,
        'mode' => 'video',
        'text' => 'daaad',
    ],
    'logo' => [
        'enabled' => false,
        'path' => 'resources/img/logo/logo-plain-fulltext.png',
    ],
    'picture' => [
        'cntdwn_time' => 10,
        'cheese_time' => 500,
        'polaroid_effect' => true,
        'take_frame' => false,
        'frame' => 'resources/img/frames/frame_parque.png',
        'extend_by_frame' => false,
    ],
    'textonpicture' => [
        'rotation' => -100,
        'font' => 'resources/fonts/GreatVibes-Regular.ttf',
        'font_color' => '#fffafa',
    ],
    'event' => [
        'enabled' => true,
        'symbol' => 'fa-gift',
    ],
    'button' => [
        'show_cups' => true,
    ],
    'filters' => [
        'disabled' => [
            'plain',
        ],
    ],
    'collage' => [
        'cntdwn_time' => 2,
        'continuous_time' => 4,
        'layout' => 'collage.json',
        'allow_selection' => true,
        'layouts_enabled' => [
            '2+2-1',
            '2+2-2',
            '1+3-1',
            '1+2-1',
            '2+1-1',
            '2x4-1',
            '2x4-2',
            '2x4-3',
            '2x4-4',
            '2x3-2',
            'collage.json',
        ],
        'limit' => 3,
        'dashedline_color' => '#df0c0c',
        'background_color' => '#f50a0a',
        'take_frame' => 'once',
        'frame' => 'private/images/frames/สำเนาของ Second​ hand​ Market​ _​ Music​ _3.png',
        'polaroid_effect' => true,
        'placeholderpath' => 'resources/img/background/01.jpg',
        'background' => 'resources/img/background/สำเนาของ Second​ hand​ Market​ _​ Music​ _3.png',
        'background_on_top' => true,
        'slideshow_enabled' => true,
    ],
    'textoncollage' => [
        'line1' => 'Between',
        'line2' => 'Sun &',
        'line3' => 'Moon',
        'rotation' => 45,
        'font' => 'resources/fonts/GreatVibes-Regular.ttf',
    ],
    'login' => [
        'username' => '',
    ],
    'gallery' => [
        'show_on_main_app' => false,
    ],
    'protect' => [
        'admin' => false,
        'localhost_admin' => false,
        'index_redirect' => 'gallery',
    ],
    'colors' => [
        'primary' => '#106a37',
        'panel' => '#529146',
    ],
    'fonts' => [
        'gallery_title_color' => '#000000',
    ],
    'background' => [
        'defaults' => 'resources/img/background.png',
        'chroma' => 'resources/img/background.png',
    ],
    'preview' => [
        'mode' => 'device_cam',
        'camTakesPic' => true,
    ],
    'textonprint' => [
        'font' => 'resources/fonts/GreatVibes-Regular.ttf',
    ],
    'qr' => [
        'url' => 'https://photo.betweensunandmoon.coffee/view.php?image=',
    ],
    'print' => [
        'from_result' => true,
        'from_gallery' => true,
        'limit' => 1,
        'frame' => 'resources/img/frames/frame.png',
    ],
    // ส่งงานพิมพ์จาก DDEV container ไปยัง CUPS บน Mac ผ่าน host.docker.internal
    // /usr/local/bin/lp ใน container คือ Python3 IPP proxy (ไม่ต้องลง cups-client)
    'commands' => [
        'print' => 'lp -H host.docker.internal:631 -d L8050_Series_on_NETUSB -o landscape -o fit-to-page %s',
    ],
];
