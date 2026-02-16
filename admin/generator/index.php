<?php
require_once __DIR__ . '/../admin_boot.php';

use Photobooth\Service\ConfigurationService;
use Photobooth\Service\ApplicationService;
use Photobooth\Service\LanguageService;
use Photobooth\Utility\AdminInput;
use Photobooth\Utility\FontUtility;
use Photobooth\Utility\ImageUtility;
use Photobooth\Utility\PathUtility;
use Photobooth\Service\AssetService;

$configurationService = ConfigurationService::getInstance();

$error = false;
$success = false;
$warning = false;
$languageService = LanguageService::getInstance();
$pageTitle = 'Collage generator - ' . ApplicationService::getInstance()->getTitle();
include PathUtility::getAbsolutePath('admin/components/head.admin.php');
include PathUtility::getAbsolutePath('admin/helper/index.php');

$collageConfigFilePath = PathUtility::getAbsolutePath('private/collage.json');
$collageJson = '';
$permitSubmit = true;
$enableWriteMessage = '';
$startPreloaded = false;
if (file_exists($collageConfigFilePath)) {
    $collageJson = json_decode((string)file_get_contents($collageConfigFilePath), true);
    if (!is_writable($collageConfigFilePath)) {
        $permitSubmit = false;
        $enableWriteMessage = $languageService->translate('collage:generator:please_enable_write');
    }
}

$demoImages = ImageUtility::getDemoImages(8);

$newConfiguration = '';
if (isset($_POST['new-configuration'])) {
    $newConfiguration = $_POST['new-configuration'];
    $newConfig = $config;

    $fp = fopen($collageConfigFilePath, 'w');
    if ($fp) {
        fwrite($fp, $newConfiguration);
        fclose($fp);

        $collageJson = json_decode($newConfiguration);
        $startPreloaded = true;
        $arrayCollageJson = (array) $collageJson;

        // Always sync key settings from generator to main config
        if (array_key_exists('background', $arrayCollageJson) && $arrayCollageJson['background'] !== '') {
            $newConfig['collage']['background'] = $arrayCollageJson['background'];
        }
        if (array_key_exists('background_color', $arrayCollageJson)) {
            $newConfig['collage']['background_color'] = $arrayCollageJson['background_color'];
        }
        if (array_key_exists('background_on_top', $arrayCollageJson)) {
            $newConfig['collage']['background_on_top'] = (bool) $arrayCollageJson['background_on_top'];
        }
        if (array_key_exists('frame', $arrayCollageJson) && $arrayCollageJson['frame'] !== '') {
            $newConfig['collage']['frame'] = $arrayCollageJson['frame'];
        }
        if (array_key_exists('apply_frame', $arrayCollageJson)) {
            $newConfig['collage']['take_frame'] = $arrayCollageJson['apply_frame'];
        }
        if (array_key_exists('placeholder', $arrayCollageJson)) {
            $newConfig['collage']['placeholder'] = $arrayCollageJson['placeholder'];
        }
        if (array_key_exists('placeholderposition', $arrayCollageJson)) {
            $newConfig['collage']['placeholderposition'] = $arrayCollageJson['placeholderposition'];
        }
        if (array_key_exists('placeholderpath', $arrayCollageJson)) {
            $newConfig['collage']['placeholderpath'] = $arrayCollageJson['placeholderpath'];
        }

        // Layout-specific: limit count only applies when using collage.json layout
        if ($config['collage']['layout'] === 'collage.json') {
            if (array_key_exists('layout', $arrayCollageJson)) {
                $newConfig['collage']['limit'] = count($arrayCollageJson['layout']);
            } else {
                $newConfig['collage']['limit'] = count($arrayCollageJson);
            }
        }

        // Adjust limit for placeholder
        if ($newConfig['collage']['placeholder']) {
            $collagePlaceholderPosition = (int) $newConfig['collage']['placeholderposition'];
            $currentLimit = $newConfig['collage']['limit'] ?? $config['collage']['limit'];
            if ($collagePlaceholderPosition > 0 && $collagePlaceholderPosition <= $currentLimit) {
                $newConfig['collage']['limit'] = $currentLimit - 1;
            } else {
                $newConfig['collage']['placeholder'] = false;
                $warning = true;
            }
        }

        try {
            $configurationService->update($newConfig);
        } catch (\Exception $exception) {
            $warning = true;
        }
    } else {
        $error = true;
    }

    $success = !($error || $warning);
}

$font_paths = [
    PathUtility::getAbsolutePath('resources/fonts'),
    PathUtility::getAbsolutePath('private/fonts')
];

$font_family_options = [];

$font_styles = '<style>';
foreach ($font_paths as $path) {
    try {
        $files = FontUtility::getFontsFromPath($path, false);
        $files = array_map(fn ($file): string => PathUtility::getPublicPath($file), $files);
        if (count($files) > 0) {
            foreach ($files as $name => $path) {
                $font_styles .= '
					@font-face {
						font-family: "' . $name . '";
						src: url(' . $path . ') format("truetype");
					}
				';
                $font_family_options[$path] = $name;
            }
        }
    } catch (\Exception $e) {
        $font_styles .= '';
    }
}
$font_styles .= '</style>';

?>

<div class="w-full h-screen bg-brand-2 px-3 md:px-6 py-6 md:py-12 overflow-x-hidden overflow-y-auto">
	<?= $font_styles ?>
    <style>
        :root {
            --modal-backdrop: rgba(0, 0, 0, 0.8);
            --modal-color: #313131;
            --modal-background: #fff;
            --modal-font-size: inherit;
            --modal-line-height: inherit;
            --modal-padding: 2rem;
            --modal-spacing: 2rem;
            --modal-button-color: var(--button-font-color);
            --modal-button-background: var(--primary-color);
            --modal-button-font-size: 1rem;
            --modal-button-font-weight: 400;
            --modal-button-icon-size: 1rem;
            --modal-button-padding-y: 1rem;
            --modal-button-padding-x: 1rem;
            --modal-button-border-width: 0;
            --modal-button-border-color: var(--border-color);
            --modal-button-border-radius: 0;
            --modal-button-height: auto;
            --modal-button-width: auto;
            --modal-button-gap: 0.25rem;
            --modal-button-direction: row;
            --modal-button-focus-background: color-mix(in srgb, var(--modal-button-background), var(--modal-button-color) 20%);
        }
        .modal {
            display: flex;
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: var(--modal-backdrop);
            justify-content: center;
            align-items: center;
            z-index: 16777372;
        }
        .modal-inner {
            flex-direction: column;
            display: flex;
            position: relative;
            color: var(--modal-color);
            background: var(--modal-background);
            max-width: calc(100dvw - var(--modal-spacing) * 2);
            max-height: calc(100dvh - var(--modal-spacing) * 2);
        }
        .modal-body {
            overflow-y: scroll;
            font-size: var(--modal-font-size);
            line-height: var(--modal-line-height);
            padding: var(--modal-padding);
        white-space: pre;
        }
        .modal-body img {
            display: block;
            margin: 0 auto;
            height: auto;
            max-width: 100%;
        }
        .modal-body > *:first-child {
            margin-top: 0;
        }
        .modal-body > *:last-child {
            margin-bottom: 0;
        }
        .modal-buttonbar {
            display: flex;
            background: color-mix(in srgb, var(--modal-button-background), var(--modal-button-color) 40%);
            gap: 1px;
        }
        .modal-button {
            flex-grow: 1;
            display: inline-flex;
            flex-direction: var(--modal-button-direction);
            padding: var(--modal-button-padding-y) var(--modal-button-padding-x);
            gap: var(--modal-button-gap);
            font-size: var(--modal-button-font-size);
            font-weight: var(--modal-button-font-weight);
            color: var(--modal-button-color);
            text-align: center;
            text-decoration: none;
            vertical-align: middle;
            cursor: pointer;
            user-select: none;
            height: var(--modal-button-height);
            width: var(--modal-button-width);
            border: var(--modal-button-border-width) solid var(--modal-button-border-color);
            border-radius: var(--modal-button-border-radius);
            background: var(--modal-button-background);
            justify-content: center;
            align-items: center;
            white-space: nowrap;
            line-height: 1;
        }
        .modal-button.focused, .modal-button:hover, .modal-button:focus {
            --modal-button-background: var(--modal-button-focus-background);
        }
        .modal-button[disabled] {
            opacity: 0.5;
        }
        .modal-button--icon {
            font-size: var(--modal-button-icon-size);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        /* Generator page: clearer layout, easier to use */
        .generator-page .result_section { align-items: stretch; gap: 1rem; }
        .generator-page .result_positions {
            max-height: min(75vh, 900px);
            overflow-y: auto;
            overflow-x: hidden;
            scroll-behavior: smooth;
            padding-right: 0.5rem;
        }
        .generator-page .result_positions::-webkit-scrollbar { width: 8px; }
        .generator-page .result_positions::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
        .generator-page .result_positions::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
        .generator-page .generator-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
        }
        .generator-page .generator-section-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--brand-1, #333);
            margin-bottom: 0.75rem;
            padding-bottom: 0.35rem;
            border-bottom: 2px solid #e2e8f0;
        }
        .generator-page .images_settings {
            margin-top: 0.5rem;
            padding-top: 1rem;
            border-top: 2px dashed #e2e8f0;
        }
        .generator-page .images_settings .generator-section-title { margin-bottom: 0.75rem; }
        .generator-page #layout_containers {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            padding: 0.5rem 0 1.5rem;
            min-height: 120px;
            align-items: start;
        }
        .generator-page .image_layout {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            background-color: #fff;
            padding: 1.25rem !important;
            gap: 1rem !important;
            position: relative;
            display: flex !important;
            flex-direction: column;
            transition: all 0.2s ease-in-out;
        }
        .generator-page .image_layout:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }
        .generator-page .image_layout .image-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .generator-page .image_layout .image-title {
            font-weight: 600;
            color: #475569;
            font-size: 0.95rem;
        }
        .generator-page .image_layout .delete-btn {
            color: #ef4444;
            background: #fee2e2;
            border: none;
            border-radius: 6px;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .generator-page .image_layout .delete-btn:hover {
            background: #fecaca;
            color: #dc2626;
        }
        .generator-page .image_layout .input-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }
        .generator-page .image_layout .full-width {
            width: 100%;
        }
        .generator-page .image_layout .adminImageSelection-preview { 
            border-radius: 8px; 
            border: 1px solid #e2e8f0;
            max-height: 150px;
            object-fit: contain;
            background-color: #f8fafc;
        }
        .generator-page #addImage {
            margin-top: 0.25rem;
            padding: 0.75rem 1.5rem;
            border-radius: 9999px;
            font-weight: 600;
        }
        .generator-page .result_images {
            border-radius: 12px;
            overflow: hidden;
            min-height: 280px;
        }
        .generator-page #result_canvas { border-radius: 8px; }
        .generator-page button[onclick="saveConfiguration()"] {
            box-shadow: 0 4px 14px rgba(0,0,0,0.2);
            font-size: 1.5rem;
        }
        /* JSON Config Display */
        .config-display {
            background: #1e293b;
            color: #e2e8f0;
            padding: 1rem;
            border-radius: 8px;
            font-family: monospace;
            font-size: 0.85rem;
            overflow-x: auto;
            white-space: pre-wrap;
            max-height: 200px;
            margin-top: 1rem;
            position: relative;
        }
        .config-display-btn {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: rgba(255,255,255,0.1);
            color: #fff;
            border: none;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.75rem;
        }
        .config-display-btn:hover { background: rgba(255,255,255,0.2); }
    </style>
    <style id="fontselectedStyle"></style>
    <div class="w-full flex items-center justify-center flex-col">
        <div class="w-full max-w-[1500px] rounded-lg p-4 md:p-8 bg-white flex flex-col shadow-xl place-items-center relative">
            <div class="w-full text-center flex flex-col items-center justify-center text-2xl font-bold text-brand-1 mb-2">
                Collage Layout Generator
            </div>
            <div class="result_section mt-4 w-full flex gap-4 flex-col md:flex-row">
                <div class="result_positions md:max-h-[75vh] p-2 md:p-4 overflow-y-auto overflow-x-hidden flex-1">
                    <div class="general_settings">
                        <input id="current_config" type="hidden" value='<?= json_encode($collageJson) ?>' />
                        <input id="can_submit" type="hidden" value='<?= $permitSubmit ?>' />
                        <input id="start_preloaded" type="hidden" value='<?= $startPreloaded ?>' />
                        <?php if ($enableWriteMessage !== '') { ?>
                            <input id='enable_write_message' type='hidden' value='<?= $enableWriteMessage ?>' />
                        <?php } ?>
                        <?php if ($collageJson !== '') { ?>
                            <div class="w-full flex flex-col gap-2 mb-4 md:mb-8">
                                <div>
                                    <?= AdminInput::renderCta('collage:generator:load_current_configuration', 'loadCurrentConfiguration') ?>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="grid gap-2">
                        <div>
                            <span class="w-full flex flex-col text-xl font-bold text-brand-1 mb-2">
                                <?= $languageService->translate('general') ?>
                            </span>
                        </div>
                        <div class="grid gap-2 mb-4 grid-cols-[repeat(auto-fit,_minmax(150px,_1fr))]">
                            <div class="col-span-2 flex flex-col">
                                <?=
                                    AdminInput::renderColor(
                                        [
                                            'name' => 'background_color',
                                            'value' => $config['collage']['background_color'] ?? '#FFFFFF',
                                            'placeholder' => 'background color',
                                            'attributes' => ['data-trigger' => 'general']
                                        ],
                                        'collage:collage_background_color'
                                    )
?>
                            </div>
                            <div class="col-span-2 flex flex-col">
                                <?= AdminInput::renderImageSelect(
                                    [
            'name' => 'generator-background',
            'value' => $config['collage']['background'] ?? '',
            'paths' => [
                PathUtility::getAbsolutePath('resources/img/background'),
                PathUtility::getAbsolutePath('private/images/background'),
            ],
            'attributes' => ['data-trigger' => 'general']
        ],
                                    'collage:collage_background'
                                )
?>
                            </div>
                            <div class="col-span-2 flex flex-col">
                                <?=
    AdminInput::renderImageSelect(
        [
            'name' => 'generator-frame',
            'value' => $config['collage']['frame'] ?? '',
            'paths' => [
                PathUtility::getAbsolutePath('resources/img/frames'),
                PathUtility::getAbsolutePath('private/images/frames'),
            ],
            'attributes' => ['data-trigger' => 'general']
        ],
        'collage:collage_frame'
    )
?>
                            </div>
                            <div class="flex flex-col">
                                <?=
    AdminInput::renderInput(
        [
            'type' => 'number',
            'name' => 'final_width',
            'value' => '1500',
            'placeholder' => 'collage width',
            'attributes' => ['data-trigger' => 'general']
        ],
        'collage:generator:final_width'
    )
?>
                            </div>
                            <div class="flex flex-col">
                                <?=
    AdminInput::renderInput(
        [
            'type' => 'number',
            'name' => 'final_height',
            'value' => '1000',
            'placeholder' => 'collage height',
            'attributes' => ['data-trigger' => 'general']
        ],
        'collage:generator:final_height'
    )
?>
                            </div>
                            <div class="col-span-2 flex flex-col">
                                <?=
    AdminInput::renderSelect(
        [
            'type' => 'select',
            'name' => 'apply_frame',
            'options' => [
                'off' => 'Off',
                'always' => 'Always',
                'once' => 'Once',
            ],
            'value' => $config['collage']['take_frame'] ?? 'once',
            'attributes' => ['data-trigger' => 'general']
        ],
        'collage:collage_take_frame'
    )
?>
                            </div>
                            <div class="flex flex-col">
                                <?=
    AdminInput::renderCheckbox(
        [
            'name' => 'show-background',
            'value' => !empty($config['collage']['background']) ? 'true' : 'false',
            'attributes' => ['data-trigger' => 'general']
        ],
        'collage:generator:show_background'
    )
?>
                            </div>
                            <div class="flex flex-col">
                                <?=
    AdminInput::renderCheckbox(
        [
            'name' => 'show-frame',
            'value' => !empty($config['collage']['frame']) ? 'true' : 'false',
            'attributes' => ['data-trigger' => 'general']
        ],
        'collage:generator:show_frame'
    )
?>
                            </div>
                            <div class="flex flex-col">
                                <?=
    AdminInput::renderCheckbox(
        [
            'name' => 'generator-background_on_top',
            'value' => !empty($config['collage']['background_on_top']) ? 'true' : 'false',
            'attributes' => ['data-trigger' => 'general']
        ],
        'collage:collage_background_on_top'
    )
?>
                            </div>
                        </div>
                        <div>
                            <span class="w-full flex flex-col text-xl font-bold text-brand-1 mb-2">
                                <?= $languageService->translate('collage:generator:placeholder_settings') ?>
                            </span>
                        </div>
                        <div class="grid gap-2 mb-4 grid-cols-[repeat(auto-fit,_minmax(150px,_1fr))]">
                            <div class="col-span-2 flex flex-col">
                                <?=
    AdminInput::renderCheckbox(
        [
            'name' => 'enable_placeholder_image',
            'value' => 'false',
            'attributes' => ['data-trigger' => 'general']
        ],
        'collage:collage_placeholder'
    )
?>
                            </div>
                            <div class="col-span-2 flex flex-col">
                                <?=
    AdminInput::renderInput(
        [
            'type' => 'number',
            'name' => 'placeholder_image_position',
            'value' => '1',
            'placeholder' => 'placehoder image position',
            'attributes' => [
                'min' => '1',
                'max' => '8',
                'data-trigger' => 'general'
            ]
        ],
        'collage:collage_placeholderposition'
    )
?>
                            </div>
                            <div class="col-span-2 flex flex-col">
                                <?=
    AdminInput::renderImageSelect(
        [
            'name' => 'placeholder_image',
            'value' => '',
            'paths' => [
                PathUtility::getAbsolutePath('resources/img/demo'),
                PathUtility::getAbsolutePath('private/images/placeholder'),
            ],
            'attributes' => ['data-trigger' => 'general']
        ],
        'choose_placeholder'
    )
?>
                            </div>
                        </div>
                        <div>
                            <span class="w-full flex flex-col text-xl font-bold text-brand-1 mb-2">
                                <?= $languageService->translate('text_settings') ?>
                            </span>
                        </div>
                        <div class="grid gap-2 mb-4 grid-cols-[repeat(auto-fit,_minmax(150px,_1fr))]">
                            <div class="col-span-2 flex flex-col">
                                <?=
    AdminInput::renderCheckbox(
        [
            'name' => 'text_enabled',
            'value' => 'false',
            'attributes' => ['data-trigger' => 'general']
        ],
        'collage:textoncollage_enabled'
    )
?>
                            </div>
                            <div class="col-span-2 flex flex-col">
                                <?=
    AdminInput::renderFontSelect(
        [
            'name' => 'text_font_family',
            'value' => '',
            'paths' => [
                PathUtility::getAbsolutePath('resources/fonts'),
                PathUtility::getAbsolutePath('private/fonts'),
            ],
            'attributes' => ['data-trigger' => 'general']
        ],
        'collage:textoncollage_font'
    )
?>
                            </div>
                            <div class="flex flex-col">
                            <?=
AdminInput::renderColor(
    [
        'name' => 'text_font_color',
        'value' => '#000000',
        'placeholder' => 'text font color',
        'attributes' => ['data-trigger' => 'general']
    ],
    'collage:textoncollage_font_color'
)
?>
                            </div>
                            <div class="flex flex-col">
                                <?=
        AdminInput::renderInput(
            [
                'type' => 'number',
                'name' => 'text_font_size',
                'value' => '50',
                'placeholder' => 'text font size',
                'attributes' => ['data-trigger' => 'general']
            ],
            'collage:textoncollage_font_size'
        )
?>
                            </div>
                            <div class="flex flex-col">
                                <?=
    AdminInput::renderInput(
        [
            'type' => 'text',
            'name' => 'text_line_1',
            'value' => 'Photobooth',
            'placeholder' => 'text line 1',
            'attributes' => ['data-trigger' => 'general']
        ],
        'collage:textoncollage_line1'
    )
?>
                            </div>
                            <div class="flex flex-col">
                                <?=
    AdminInput::renderInput(
        [
            'type' => 'text',
            'name' => 'text_line_2',
            'value' => 'we love',
            'placeholder' => 'text line 2',
            'attributes' => ['data-trigger' => 'general']
        ],
        'collage:textoncollage_line2'
    )
?>
                            </div>
                            <div class="flex flex-col">
                                <?=
    AdminInput::renderInput(
        [
            'type' => 'text',
            'name' => 'text_line_3',
            'value' => 'OpenSource',
            'placeholder' => 'text line 3',
            'attributes' => ['data-trigger' => 'general']
        ],
        'collage:textoncollage_line3'
    )
?>
                            </div>
                            <div class="flex flex-col">
                                <?=
    AdminInput::renderInput(
        [
            'type' => 'number',
            'name' => 'text_line_space',
            'value' => '90',
            'placeholder' => 'text line space',
            'attributes' => ['data-trigger' => 'general']
        ],
        'collage:textoncollage_linespace'
    )
?>
                            </div>
                            <div class="flex flex-col">
                                <?=
    AdminInput::renderInput(
        [
            'type' => 'number',
            'name' => 'text_location_x',
            'value' => '1470',
            'placeholder' => 'text location x',
            'attributes' => ['data-trigger' => 'general']
        ],
        'collage:textoncollage_locationx'
    )
?>
                            </div>
                            <div class="flex flex-col">
                                <?=
    AdminInput::renderInput(
        [
            'type' => 'number',
            'name' => 'text_location_y',
            'value' => '250',
            'placeholder' => 'text location y',
            'attributes' => ['data-trigger' => 'general']
        ],
        'collage:textoncollage_locationy'
    )
?>
                            </div>
                            <div class="col-span-2 flex flex-col">
                                <?=
    AdminInput::renderRange(
        [
            'type' => 'number',
            'name' => 'text_rotation',
            'value' => '0',
            'unit' => 'degrees',
            'range_min' => '-180',
            'range_max' => '180',
            'range_step' => '5',
            'placeholder' => 'degrees',
            'attributes' => ['data-trigger' => 'general']
        ],
        'collage:textoncollage_rotation'
    )
?>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="images_settings flex flex-col gap-4">
                    <div id="layout_containers">
                        <?php for ($i = 0; $i < count($demoImages); $i++) {
                            $hidden_class = 'hidden';
                            if ($i == 0) {
                                $hidden_class = '';
                            }
                            $demoRelPath = ltrim(str_replace('\\', '/', str_replace(PathUtility::getRootPath(), '', $demoImages[$i])), '/');
                            $computed_classes = 'image_layout ' . $hidden_class;
                            ?>
                            <div data-picture="picture-<?=$i?>" class="<?=$computed_classes?>">
                                <div class="image-header">
                                    <span class="image-title">Image <?=$i + 1?></span>
                                    <button type="button" class="delete-btn" onclick="hideImage('picture-<?=$i?>')" title="Remove Image">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                                <div class="full-width">
                                    <?=
                                    AdminInput::renderImageSelect(
                                        [
                                            'name' => 'picture-image-' . $i,
                                            'value' => $demoRelPath,
                                            'paths' => [
                                                PathUtility::getAbsolutePath('resources/img/demo'),
                                                PathUtility::getAbsolutePath('private/images/placeholder'),
                                                PathUtility::getAbsolutePath('data/tmp'),
                                                PathUtility::getAbsolutePath('data/images'),
                                            ],
                                            'attributes' => ['data-trigger' => 'image']
                                        ],
                                        'choose_image'
                                    )
                                    ?>
                                </div>
                                <div class="input-group">
                                    <div>
                                        <?=
                                            AdminInput::renderInput(
                                                [
                                                    'type' => 'number',
                                                    'name' => 'picture-x-position-' . $i,
                                                    'value' => rand(100, 500),
                                                    'placeholder' => 'x position',
                                                    'attributes' => ['data-prop' => 'left', 'data-trigger' => 'image']
                                                ],
                                                'collage:generator:x_position'
                                            )
                            ?>
                                    </div>
                                    <div>
                                        <?=
                                AdminInput::renderInput(
                                    [
                                        'type' => 'number',
                                        'name' => 'picture-y-position-' . $i,
                                        'value' => rand(100, 500),
                                        'placeholder' => 'y position',
                                        'attributes' => ['data-prop' => 'top', 'data-trigger' => 'image']
                                    ],
                                    'collage:generator:y_position'
                                )
                            ?>
                                    </div>
                                </div>
                                <div class="input-group">
                                    <div>
                                        <?=
                                AdminInput::renderInput(
                                    [
                                        'type' => 'text',
                                        'name' => 'picture-width-' . $i,
                                        'value' => 'x*0.5',
                                        'placeholder' => $languageService->translate('image_width'),
                                        'attributes' => ['data-prop' => 'width', 'data-trigger' => 'image']
                                    ],
                                    'collage:generator:image_width'
                                )
                            ?>
                                    </div>
                                    <div>
                                        <?=
                                AdminInput::renderInput(
                                    [
                                        'type' => 'text',
                                        'name' => 'picture-height-' . $i,
                                        'value' => 'y*0.5',
                                        'placeholder' => $languageService->translate('image_height'),
                                        'attributes' => ['data-prop' => 'height', 'data-trigger' => 'image']
                                    ],
                                    'collage:generator:image_height'
                                )
                            ?>
                                    </div>
                                </div>
                                <div class="input-group">
                                    <div>
                                        <?=
                                AdminInput::renderRange(
                                    [
                                        'type' => 'number',
                                        'name' => 'picture-rotation-' . $i,
                                        'value' => '0',
                                        'unit' => 'degrees',
                                        'range_min' => '-180',
                                        'range_max' => '180',
                                        'range_step' => '1',
                                        'placeholder' => 'degrees',
                                        'attributes' => ['data-prop' => 'transform', 'data-trigger' => 'image']
                                    ],
                                    'collage:generator:image_rotation'
                                )
                            ?>
                                    </div>
                                    <div class="flex items-center pt-6">
                                        <?=
                                AdminInput::renderCheckbox(
                                    [
                                        'name' => 'picture-show-frame-' . $i,
                                        'value' => 'false',
                                        'attributes' => ['data-prop' => 'single_frame', 'data-trigger' => 'image']
                                    ],
                                    'collage:generator:show_single_frame'
                                )
                            ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="flex justify-center mt-6 mb-8">
                        <div class="w-full md:w-1/3">
                            <?= AdminInput::renderCta('add_image', 'addImage') ?>
                        </div>
                    </div>

                    <div class="generator-section mt-4">
                        <div class="generator-section-title">Current Configuration (JSON)</div>
                        <div class="config-display" id="config-display-box">
                            <button class="config-display-btn" onclick="copyConfig()">Copy</button>
                            <code id="config-json-content"></code>
                        </div>
                    </div>
                </div>
            </div>
            <div class="result_images md:max-h-[75vh] flex-1 relative lg:flex-[3_1_0%] p-4 md:p-8 bg-slate-300">
                <div id="result_canvas" class="relative m-0 left-[50%] top-[50%] right-0 bottom-0 translate-y-[0%] md:translate-y-[-50%] translate-x-[-50%] max-w-full max-h-full shadow-xl">
                    <div id="collage_background" class="absolute h-full w-full" style="z-index:0;">
                        <img class="h-full hidden object-contain object-top" src="" alt="Choose the background">
                    </div>
                    <?php
for ($i = 0; $i < count($demoImages); $i++) {
    $imagePath = PathUtility::getPublicPath($demoImages[$i]);
    $hiddenClass = $i == 0 ? '' : 'hidden';
    echo "<div id='picture-$i' class='absolute overflow-hidden w-full h-full $hiddenClass' style='z-index:1;'>
            <img class='absolute object-left-top rotate-0 max-w-none' data-src='$imagePath'>
            <img class='picture-frame absolute object-left-top rotate-0 max-w-none hidden' />
          </div>";
}
?>
                    <div id="collage_frame" class="absolute h-full w-full" style="z-index:10;">
                        <img class="h-full w-full hidden" src="" alt="Choose the frame">
                    </div>
                    <div id="collage_text" class="absolute h-full font-selected" style="z-index:15;">
                        <div class='relative'>
                            <div class='absolute whitespace-nowrap origin-top-left text-line-1 leading-none'></div>
                            <div class='absolute whitespace-nowrap origin-top-left text-line-2 leading-none'></div>
                            <div class='absolute whitespace-nowrap origin-top-left text-line-3 leading-none'></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button onclick="saveConfiguration()" class="absolute left-[50%] translate-x-[-50%] bottom-[-30px] w-20 h-20 rounded-full bg-blue-300 flex flex-row items-center justify-center">
            <i class="fa fa-save fa-2xl"></i>
        </button>
        <form id="configuration_form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data" class="hidden">
            <input type="hidden" name="new-configuration" value="" />
        </form>
    </div>
    <div class="w-full max-w-xl my-12 border-b border-solid border-white border-opacity-20"></div>
        <div class="w-full max-w-xl rounded-lg py-8 bg-white flex flex-col shadow-xl relative">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 px-4 ">
                <?php
                    echo getMenuBtn(PathUtility::getPublicPath('admin'), 'admin_panel', $config['icons']['admin']);

echo getMenuBtn(PathUtility::getPublicPath('test/collage.php'), 'collageTest', $config['icons']['take_collage'], true);

if (isset($_SESSION['auth']) && $_SESSION['auth'] === true) {
    echo getMenuBtn(PathUtility::getPublicPath('login/logout.php'), 'logout', $config['icons']['logout']);
}
?>
            </div>
        </div>
    </div>
</div>
<?php
$assetService = AssetService::getInstance();

include PathUtility::getAbsolutePath('admin/components/footer.scripts.php');
echo '<script src="' . $assetService->getUrl('resources/js/admin/generator.js') . '"></script>';
?>
<script>
function copyConfig() {
    const content = document.getElementById('config-json-content').innerText;
    navigator.clipboard.writeText(content).then(() => {
        if(typeof openToast === 'function') openToast("Configuration copied to clipboard");
        else alert("Copied!");
    });
}

// Keep original saveConfiguration (do not override – it submits the form and must run)

function updateConfigDisplay() {
    // This logic mimics saveConfiguration in generator.js to build the object
    let configuration = {
        width: $('input[name=\'final_width\']').val(),
        height: $('input[name=\'final_height\']').val(),
        text_custom_style: $('input[name=\'text_enabled\'][data-trigger=\'general\']').is(':checked'),
        text_font_size: $('input[name=\'text_font_size\']').val(),
        text_rotation: $('input[name=\'text_rotation\']').val(),
        text_locationx: $('input[name=\'text_location_x\']').val(),
        text_locationy: $('input[name=\'text_location_y\']').val(),
        text_font_color: $('input[name=\'text_font_color\']').val(),
        text_font: $('input[name=\'text_font_family\']').val(),
        text_line1: $('input[name=\'text_line_1\']').val(),
        text_line2: $('input[name=\'text_line_2\']').val(),
        text_line3: $('input[name=\'text_line_3\']').val(),
        text_linespace: $('input[name=\'text_line_space\']').val(),
        apply_frame: $('select[name=\'apply_frame\']').val(),
        frame: $('input[name=\'generator-frame\']').val(),
        background: $('input[name=\'generator-background\']').val(),
        background_color: $('input[name=\'background_color\']').val(),
        background_on_top: $('input[name=\'generator-background_on_top\'][data-trigger=\'general\']').is(':checked'),
        placeholder: $('input[name=\'enable_placeholder_image\'][data-trigger=\'general\']').is(':checked'),
        placeholderpath: $('input[name=\'placeholder_image\']').val(),
        placeholderposition: $('input[name=\'placeholder_image_position\']').val(),
        layout: []
    };

    $('div.image_layout:visible').each(function () {
        let container = $(this);
        let single_image_layout = [];
        container.find('input[data-prop]').each(function () {
            let to_save = $(this).val();
            if ($(this).attr('type') === 'checkbox') {
                to_save = $(this).is(':checked') && configuration.apply_frame === 'always';
            }
            single_image_layout.push(to_save);
        });
        configuration.layout.push(single_image_layout);
    });

    // Simple formatting
    let json = JSON.stringify(configuration, null, 4);
    $('#config-json-content').text(json);
}

// Update on any change
$(document).on('change keyup', 'input, select', function() {
    setTimeout(updateConfigDisplay, 100);
});
$(function() {
    setTimeout(updateConfigDisplay, 500);
});

// Enable ArrowUp/ArrowDown for text inputs that contain numbers
$(document).on('keydown', 'input[type=text]', function(e) {
    if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
        const val = this.value;
        // Check if it's a pure number
        if (!isNaN(val) && !isNaN(parseFloat(val))) {
            e.preventDefault();
            let num = parseFloat(val);
            const step = e.shiftKey ? 10 : 1;
            if (e.key === 'ArrowUp') num += step;
            else num -= step;
            this.value = num;
            $(this).trigger('change');
        }
    }
});
</script>
<?php

if ($success) {
    echo '<script>setTimeout(function(){openToast("' . $languageService->translate('collage:generator:configuration_saved') . '")},500);</script>';
}
if ($error !== false) {
    echo '<script>setTimeout(function(){openToast("' . $languageService->translate('collage:generator:configuration_saving_error') . '", "isError", 5000)},500);</script>';
}
if ($warning) {
    echo '<script>setTimeout(function(){openToast("' . $languageService->translate('collage:generator:save_config_manually') . '", "isWarning", 5000)},500);</script>';
}

include PathUtility::getAbsolutePath('admin/components/footer.admin.php');

?>
