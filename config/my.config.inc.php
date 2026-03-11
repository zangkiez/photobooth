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
            'mm.json',
            'new.json',
        ],
        'limit' => 2,
        'dashedline_color' => '#df0c0c',
        'background_color' => '#e1d0d0',
        'take_frame' => 'once',
        'frame' => 'resources/img/frames/1.png',
        'polaroid_effect' => true,
        'placeholderpath' => 'resources/img/background/01.jpg',
        'background' => 'resources/img/background/1.png',
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
    'remotebuzzer' => [
        'serverip' => '192.168.156.3',
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
        'frame' => 'resources/img/frames/frame.png',
    ],
    'commands' => [
        'print' => 'lp -d Dai_Nippon_Printing_DP_QW410 -o PageSize=dnp4x6 -o media=Custom.4x6 -o scaling=100 -o position=center %s',
    ],
];
