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

        // Copy background image to template/collage/custom/
        if (!empty($arrayCollageJson['background'])) {
            $bgSrcAbs = PathUtility::getAbsolutePath($arrayCollageJson['background']);
            if (file_exists($bgSrcAbs)) {
                $customTemplateDir = PathUtility::getAbsolutePath('template/collage/custom');
                if (!is_dir($customTemplateDir)) {
                    mkdir($customTemplateDir, 0755, true);
                }
                copy($bgSrcAbs, $customTemplateDir . '/' . basename($bgSrcAbs));
            }
        }

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
        if ($config['collage']['layout'] === 'collage.json') {
            if (array_key_exists('layout', $arrayCollageJson)) {
                $newConfig['collage']['limit'] = count($arrayCollageJson['layout']);
            } else {
                $newConfig['collage']['limit'] = count($arrayCollageJson);
            }
        }
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
    PathUtility::getAbsolutePath('private/fonts'),
];
$font_family_options = [];
$font_styles = '<style>';
foreach ($font_paths as $path) {
    try {
        $files = FontUtility::getFontsFromPath($path, false);
        $files = array_map(fn ($file): string => PathUtility::getPublicPath($file), $files);
        if (count($files) > 0) {
            foreach ($files as $name => $path) {
                $font_styles .= '@font-face{font-family:"' . $name . '";src:url(' . $path . ') format("truetype");}';
                $font_family_options[$path] = $name;
            }
        }
    } catch (\Exception $e) {
        // skip
    }
}
$font_styles .= '</style>';
echo $font_styles;
?>
<style id="fontselectedStyle"></style>
<link rel="stylesheet" href="<?=$assetService->getUrl('resources/css/generator.css')?>">

<div class="gen-root">

    <!-- Top bar -->
    <header class="gen-topbar">
        <div class="gen-topbar-title">
            <span class="gen-topbar-icon"><i class="fa fa-th-large"></i></span>
            Collage Generator
        </div>
        <div class="gen-topbar-actions">
            <a href="<?= PathUtility::getPublicPath('test/collage.php') ?>" target="_blank" class="gen-topbar-btn">
                <i class="fa fa-external-link"></i> Test
            </a>
            <a href="<?= PathUtility::getPublicPath('admin') ?>" class="gen-topbar-btn">
                <i class="fa fa-arrow-left"></i> Admin
            </a>
        </div>
    </header>

    <!-- Stepper -->
    <nav class="gen-stepper">
        <div class="gen-step-tab active" data-go="1">
            <span class="gen-step-num">1</span>
            <span class="gen-step-label">Background</span>
        </div>
        <span class="gen-step-sep">›</span>
        <div class="gen-step-tab" data-go="2">
            <span class="gen-step-num">2</span>
            <span class="gen-step-label">Canvas Size</span>
        </div>
        <span class="gen-step-sep">›</span>
        <div class="gen-step-tab" data-go="3">
            <span class="gen-step-num">3</span>
            <span class="gen-step-label">Photo Frames</span>
        </div>
        <span class="gen-step-sep">›</span>
        <div class="gen-step-tab" data-go="4">
            <span class="gen-step-num">4</span>
            <span class="gen-step-label">Details &amp; Save</span>
        </div>
    </nav>
    <!-- Progress bar -->
    <div class="gen-progress-bar"><div class="gen-progress-fill" id="gen-progress-fill" style="width:25%"></div></div>

    <!-- Hidden inputs for generator.js -->
    <input id="current_config"  type="hidden" value='<?= json_encode($collageJson) ?>' />
    <input id="can_submit"      type="hidden" value='<?= $permitSubmit ?>' />
    <input id="start_preloaded" type="hidden" value='<?= $startPreloaded ?>' />
    <?php if ($enableWriteMessage !== '') { ?>
        <input id="enable_write_message" type="hidden" value='<?= $enableWriteMessage ?>' />
    <?php } ?>

    <!-- Body -->
    <div class="gen-body">

        <!-- Sidebar -->
        <aside class="gen-sidebar">
            <div class="gen-scroll">

                <?php if ($collageJson !== '') { ?>
                <div class="g-load-banner">
                    <i class="fa fa-history"></i>
                    <span>Saved configuration found.</span>
                    <button id="loadCurrentConfiguration">Load</button>
                </div>
                <?php } ?>

                <!-- ══ STEP 1: Background ══ -->
                <div class="gen-panel active" data-panel="1">
                    <div class="g-step-h">
                        Background Image
                        <small>Upload your collage theme. Canvas size will be auto-detected from it.</small>
                    </div>

                    <div class="g-card">
                        <div class="g-card-title"><i class="fa fa-image"></i> Background</div>
                        <div class="g-field">
                            <div class="g-label">Background Image</div>
                            <?= AdminInput::renderImageSelect([
                                'name'       => 'generator-background',
                                'value'      => $config['collage']['background'] ?? '',
                                'paths'      => [
                                    PathUtility::getAbsolutePath('resources/img/background'),
                                    PathUtility::getAbsolutePath('private/images/background'),
                                ],
                                'attributes' => ['data-trigger' => 'general'],
                            ], 'collage:collage_background') ?>
                            <button type="button" class="g-detect-btn" id="gen-detect-btn1">
                                <i class="fa fa-magic"></i> Auto-detect canvas size from image
                            </button>
                        </div>
                        <div class="g-field" style="margin-top:.625rem;">
                            <div class="g-label">Background Color (fallback)</div>
                            <?= AdminInput::renderColor([
                                'name'       => 'background_color',
                                'value'      => $config['collage']['background_color'] ?? '#FFFFFF',
                                'attributes' => ['data-trigger' => 'general'],
                            ], 'collage:collage_background_color') ?>
                        </div>
                        <div class="g-field" style="margin-top:.4rem;">
                            <div class="g-toggle-row">
                                <div><div class="g-toggle-lbl">Show Background</div></div>
                                <label class="g-switch">
                                    <?= AdminInput::renderCheckbox([
                                        'name'       => 'show-background',
                                        'value'      => !empty($config['collage']['background']) ? 'true' : 'false',
                                        'attributes' => ['data-trigger' => 'general'],
                                    ], 'collage:generator:show_background') ?>
                                </label>
                            </div>
                            <div class="g-toggle-row">
                                <div>
                                    <div class="g-toggle-lbl">Background on Top</div>
                                    <div class="g-toggle-sub">Background overlays photos (transparent PNG)</div>
                                </div>
                                <label class="g-switch">
                                    <?= AdminInput::renderCheckbox([
                                        'name'       => 'generator-background_on_top',
                                        'value'      => !empty($config['collage']['background_on_top']) ? 'true' : 'false',
                                        'attributes' => ['data-trigger' => 'general'],
                                    ], 'collage:collage_background_on_top') ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══ STEP 2: Canvas Size ══ -->
                <div class="gen-panel" data-panel="2">
                    <div class="g-step-h">
                        Canvas Size
                        <small>Set the final output dimensions to match your background image.</small>
                    </div>

                    <div class="g-card">
                        <div class="g-card-title"><i class="fa fa-th-large"></i> Quick Presets</div>
                        <div class="g-presets">
                            <button type="button" class="g-preset" data-w="1800" data-h="1200">4×6″ Land.</button>
                            <button type="button" class="g-preset" data-w="1200" data-h="1800">4×6″ Port.</button>
                            <button type="button" class="g-preset" data-w="2400" data-h="1600">6×4″</button>
                            <button type="button" class="g-preset" data-w="1500" data-h="1000">3:2 Wide</button>
                            <button type="button" class="g-preset" data-w="2000" data-h="2000">Square</button>
                            <button type="button" class="g-preset" data-w="2480" data-h="3508">A4 Port.</button>
                            <button type="button" class="g-preset" data-w="3508" data-h="2480">A4 Land.</button>
                            <button type="button" class="g-preset" data-w="3600" data-h="1200">Strip ×3</button>
                        </div>
                    </div>

                    <div class="g-card">
                        <div class="g-card-title"><i class="fa fa-arrows-alt"></i> Custom Dimensions</div>
                        <div class="g-row2">
                            <div class="g-field">
                                <div class="g-label">Width (px)</div>
                                <?= AdminInput::renderInput([
                                    'type' => 'number', 'name' => 'final_width', 'value' => '1500',
                                    'attributes' => ['data-trigger' => 'general', 'min' => '100'],
                                ], 'collage:generator:final_width') ?>
                            </div>
                            <div class="g-field">
                                <div class="g-label">Height (px)</div>
                                <?= AdminInput::renderInput([
                                    'type' => 'number', 'name' => 'final_height', 'value' => '1000',
                                    'attributes' => ['data-trigger' => 'general', 'min' => '100'],
                                ], 'collage:generator:final_height') ?>
                            </div>
                        </div>
                        <button type="button" class="g-detect-btn" id="gen-detect-btn2">
                            <i class="fa fa-magic"></i> Detect size from background image
                        </button>
                        <p class="g-hint" style="margin-top:.4rem;">
                            Use ↑/↓ arrow keys on inputs. Hold <kbd>Shift</kbd> ×10 or <kbd>Ctrl</kbd> ×100.
                        </p>
                    </div>
                </div>

                <!-- ══ STEP 3: Photo Frames ══ -->
                <div class="gen-panel" data-panel="3">
                    <div class="g-step-h">
                        Photo Frames
                        <small>Drag frames on the canvas to reposition. Corner handle to resize.</small>
                    </div>

                    <!-- Couple mode -->
                    <div class="g-couple">
                        <span class="g-couple-icon">💑</span>
                        <div class="g-couple-text">
                            <strong>Couple Split Mode</strong>
                            <span>3 shots → 6 images. Mirrors frames so each person in the couple gets a copy.</span>
                        </div>
                        <label class="g-switch" style="width:40px;height:22px;flex-shrink:0;">
                            <input type="checkbox" id="gen-couple-toggle">
                            <span class="g-slider" style="border-radius:11px;"></span>
                        </label>
                    </div>

                    <!-- Frame overlay -->
                    <div class="g-card">
                        <div class="g-card-title"><i class="fa fa-picture-o"></i> Frame Overlay</div>
                        <div class="g-field">
                            <div class="g-label">Frame Image (PNG overlay)</div>
                            <?= AdminInput::renderImageSelect([
                                'name'       => 'generator-frame',
                                'value'      => $config['collage']['frame'] ?? '',
                                'paths'      => [
                                    PathUtility::getAbsolutePath('resources/img/frames'),
                                    PathUtility::getAbsolutePath('private/images/frames'),
                                ],
                                'attributes' => ['data-trigger' => 'general'],
                            ], 'collage:collage_frame') ?>
                        </div>
                        <div class="g-row2" style="margin-top:.5rem; align-items: flex-end;">
                            <div class="g-field">
                                <div class="g-label">Apply Frame</div>
                                <?= AdminInput::renderSelect([
                                    'type'    => 'select', 'name' => 'apply_frame',
                                    'options' => ['off' => 'Off', 'always' => 'Always', 'once' => 'Once'],
                                    'value'   => $config['collage']['take_frame'] ?? 'once',
                                    'attributes' => ['data-trigger' => 'general'],
                                ], 'collage:collage_take_frame') ?>
                            </div>
                            <div class="g-field">
                                <div class="g-toggle-row" style="border:none;padding:.4rem 0;">
                                    <span class="g-toggle-lbl" style="font-size:.76rem;">Show Frame</span>
                                    <label class="g-switch">
                                        <?= AdminInput::renderCheckbox([
                                            'name'       => 'show-frame',
                                            'value'      => !empty($config['collage']['frame']) ? 'true' : 'false',
                                            'attributes' => ['data-trigger' => 'general'],
                                        ], 'collage:generator:show_frame') ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Photo slots -->
                    <div class="g-card">
                        <div class="g-card-title"><i class="fa fa-object-group"></i> Photo Slots</div>

                        <!-- Alignment tools (shown when a slot is selected) -->
                        <div id="gen-align-row" style="display:none;">
                            <div class="g-label" style="margin-bottom:.3rem;">Align selected slot</div>
                            <div class="gen-align-tools">
                                <button type="button" class="gen-align-btn" data-align="left"   title="Align left"><i class="fa fa-align-left"></i></button>
                                <button type="button" class="gen-align-btn" data-align="center-h" title="Center H"><i class="fa fa-align-center"></i>H</button>
                                <button type="button" class="gen-align-btn" data-align="right"  title="Align right"><i class="fa fa-align-right"></i></button>
                                <button type="button" class="gen-align-btn" data-align="top"    title="Align top"><i class="fa fa-arrow-up"></i></button>
                                <button type="button" class="gen-align-btn" data-align="center-v" title="Center V"><i class="fa fa-align-center"></i>V</button>
                                <button type="button" class="gen-align-btn" data-align="bottom" title="Align bottom"><i class="fa fa-arrow-down"></i></button>
                            </div>
                            <div style="height:.5rem;"></div>
                        </div>
                        <div id="layout_containers">
                        <?php for ($i = 0; $i < count($demoImages); $i++) {
                            $hidden_class = ($i === 0) ? '' : 'hidden';
                            $demoRelPath  = ltrim(str_replace('\\', '/', str_replace(PathUtility::getRootPath(), '', $demoImages[$i])), '/');
                            ?>
                            <div data-picture="picture-<?= $i ?>" class="gen-frame-card image_layout open <?= $hidden_class ?>">
                                <div class="gen-frame-hd" onclick="genToggleFrame(this)">
                                    <span class="gen-frame-badge"><i class="fa fa-camera"></i>&nbsp;Slot <?= $i + 1 ?></span>
                                    <div style="display:flex;align-items:center;gap:.2rem;">
                                        <button type="button" class="gen-frame-toggle"><i class="fa fa-chevron-down"></i></button>
                                        <button type="button" class="gen-frame-del" onclick="event.stopPropagation();hideImage('picture-<?= $i ?>')" title="Remove slot">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="gen-frame-body">
                                    <div style="margin-bottom:.5rem;">
                                        <?= AdminInput::renderImageSelect([
                                            'name'  => 'picture-image-' . $i,
                                            'value' => $demoRelPath,
                                            'paths' => [
                                                PathUtility::getAbsolutePath('resources/img/demo'),
                                                PathUtility::getAbsolutePath('private/images/placeholder'),
                                                PathUtility::getAbsolutePath('data/tmp'),
                                                PathUtility::getAbsolutePath('data/images'),
                                            ],
                                            'attributes' => ['data-trigger' => 'image'],
                                        ], 'choose_image') ?>
                                    </div>
                                    <div class="g-row2" style="margin-bottom:.4rem;">
                                        <div>
                                            <div class="g-label">X Position</div>
                                            <?= AdminInput::renderInput([
                                                'type' => 'number', 'name' => 'picture-x-position-' . $i,
                                                'value' => rand(100, 500),
                                                'attributes' => ['data-prop' => 'left', 'data-trigger' => 'image'],
                                            ], 'collage:generator:x_position') ?>
                                        </div>
                                        <div>
                                            <div class="g-label">Y Position</div>
                                            <?= AdminInput::renderInput([
                                                'type' => 'number', 'name' => 'picture-y-position-' . $i,
                                                'value' => rand(100, 500),
                                                'attributes' => ['data-prop' => 'top', 'data-trigger' => 'image'],
                                            ], 'collage:generator:y_position') ?>
                                        </div>
                                    </div>
                                    <div class="g-row2" style="margin-bottom:.4rem;">
                                        <div>
                                            <div class="g-label">Width</div>
                                            <?= AdminInput::renderInput([
                                                'type' => 'text', 'name' => 'picture-width-' . $i,
                                                'value' => 'x*0.5',
                                                'attributes' => ['data-prop' => 'width', 'data-trigger' => 'image'],
                                            ], 'collage:generator:image_width') ?>
                                        </div>
                                        <div>
                                            <div class="g-label">Height</div>
                                            <?= AdminInput::renderInput([
                                                'type' => 'text', 'name' => 'picture-height-' . $i,
                                                'value' => 'y*0.5',
                                                'attributes' => ['data-prop' => 'height', 'data-trigger' => 'image'],
                                            ], 'collage:generator:image_height') ?>
                                        </div>
                                    </div>
                                    <div class="g-row2">
                                        <div>
                                            <div class="g-label">Rotation</div>
                                            <?= AdminInput::renderRange([
                                                'type'       => 'number', 'name' => 'picture-rotation-' . $i,
                                                'value'      => '0', 'unit' => 'deg',
                                                'range_min'  => '-180', 'range_max' => '180', 'range_step' => '1',
                                                'attributes' => ['data-prop' => 'transform', 'data-trigger' => 'image'],
                                            ], 'collage:generator:image_rotation') ?>
                                        </div>
                                        <div style="display:flex;align-items:flex-end;padding-bottom:.15rem;">
                                            <div style="display:flex;align-items:center;gap:.4rem;width:100%;">
                                                <span style="font-size:.65rem;color:var(--g-muted);font-weight:700;text-transform:uppercase;letter-spacing:.04em;">Single Frame</span>
                                                <label class="g-switch">
                                                    <?= AdminInput::renderCheckbox([
                                                        'name'       => 'picture-show-frame-' . $i,
                                                        'value'      => 'false',
                                                        'attributes' => ['data-prop' => 'single_frame', 'data-trigger' => 'image'],
                                                    ], 'collage:generator:show_single_frame') ?>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div><!-- /.gen-frame-body -->
                            </div>
                        <?php } ?>
                        </div><!-- /#layout_containers -->
                        <button type="button" class="g-add-frame" id="addImage">
                            <i class="fa fa-plus"></i> Add Photo Slot
                        </button>
                    </div>
                </div>

                <!-- ══ STEP 4: Details & Save ══ -->
                <div class="gen-panel" data-panel="4">
                    <div class="g-step-h">
                        Details &amp; Save
                        <small>Configure text overlay, placeholder image, then save.</small>
                    </div>

                    <!-- Text overlay -->
                    <div class="g-card">
                        <div class="g-card-title" style="justify-content:space-between;">
                            <span><i class="fa fa-font"></i> Text Overlay</span>
                            <label class="g-switch">
                                <?= AdminInput::renderCheckbox([
                                    'name' => 'text_enabled', 'value' => 'false',
                                    'attributes' => ['data-trigger' => 'general'],
                                ], 'collage:textoncollage_enabled') ?>
                            </label>
                        </div>
                        <div class="g-row3" style="margin-bottom:.5rem;">
                            <div><div class="g-label">Line 1</div><?= AdminInput::renderInput(['type'=>'text','name'=>'text_line_1','value'=>'Photobooth','attributes'=>['data-trigger'=>'general']],'collage:textoncollage_line1') ?></div>
                            <div><div class="g-label">Line 2</div><?= AdminInput::renderInput(['type'=>'text','name'=>'text_line_2','value'=>'we love','attributes'=>['data-trigger'=>'general']],'collage:textoncollage_line2') ?></div>
                            <div><div class="g-label">Line 3</div><?= AdminInput::renderInput(['type'=>'text','name'=>'text_line_3','value'=>'OpenSource','attributes'=>['data-trigger'=>'general']],'collage:textoncollage_line3') ?></div>
                        </div>
                        <div class="g-field">
                            <div class="g-label">Font</div>
                            <?= AdminInput::renderFontSelect([
                                'name' => 'text_font_family', 'value' => '',
                                'paths' => [PathUtility::getAbsolutePath('resources/fonts'), PathUtility::getAbsolutePath('private/fonts')],
                                'attributes' => ['data-trigger' => 'general'],
                            ], 'collage:textoncollage_font') ?>
                        </div>
                        <div class="g-row2" style="margin-bottom:.4rem;">
                            <div><div class="g-label">Color</div><?= AdminInput::renderColor(['name'=>'text_font_color','value'=>'#000000','attributes'=>['data-trigger'=>'general']],'collage:textoncollage_font_color') ?></div>
                            <div><div class="g-label">Font Size</div><?= AdminInput::renderInput(['type'=>'number','name'=>'text_font_size','value'=>'50','attributes'=>['data-trigger'=>'general']],'collage:textoncollage_font_size') ?></div>
                        </div>
                        <div class="g-row2" style="margin-bottom:.4rem;">
                            <div><div class="g-label">Position X</div><?= AdminInput::renderInput(['type'=>'number','name'=>'text_location_x','value'=>'1470','attributes'=>['data-trigger'=>'general']],'collage:textoncollage_locationx') ?></div>
                            <div><div class="g-label">Position Y</div><?= AdminInput::renderInput(['type'=>'number','name'=>'text_location_y','value'=>'250','attributes'=>['data-trigger'=>'general']],'collage:textoncollage_locationy') ?></div>
                        </div>
                        <div class="g-row2">
                            <div><div class="g-label">Rotation</div><?= AdminInput::renderRange(['type'=>'number','name'=>'text_rotation','value'=>'0','unit'=>'deg','range_min'=>'-180','range_max'=>'180','range_step'=>'5','attributes'=>['data-trigger'=>'general']],'collage:textoncollage_rotation') ?></div>
                            <div><div class="g-label">Line Space</div><?= AdminInput::renderInput(['type'=>'number','name'=>'text_line_space','value'=>'90','attributes'=>['data-trigger'=>'general']],'collage:textoncollage_linespace') ?></div>
                        </div>
                    </div>

                    <!-- Placeholder -->
                    <div class="g-card">
                        <div class="g-card-title" style="justify-content:space-between;">
                            <span><i class="fa fa-image"></i> Placeholder</span>
                            <label class="g-switch">
                                <?= AdminInput::renderCheckbox([
                                    'name' => 'enable_placeholder_image', 'value' => 'false',
                                    'attributes' => ['data-trigger' => 'general'],
                                ], 'collage:collage_placeholder') ?>
                            </label>
                        </div>
                        <div class="g-row2">
                            <div class="g-field">
                                <div class="g-label">Slot #</div>
                                <?= AdminInput::renderInput(['type'=>'number','name'=>'placeholder_image_position','value'=>'1','attributes'=>['min'=>'1','max'=>'8','data-trigger'=>'general']],'collage:collage_placeholderposition') ?>
                            </div>
                            <div class="g-field">
                                <div class="g-label">Placeholder Image</div>
                                <?= AdminInput::renderImageSelect([
                                    'name' => 'placeholder_image', 'value' => '',
                                    'paths' => [PathUtility::getAbsolutePath('resources/img/demo'), PathUtility::getAbsolutePath('private/images/placeholder')],
                                    'attributes' => ['data-trigger' => 'general'],
                                ], 'choose_placeholder') ?>
                            </div>
                        </div>
                    </div>

                    <!-- JSON preview -->
                    <div class="g-card">
                        <div class="g-card-title"><i class="fa fa-code"></i> Configuration JSON</div>
                        <div class="g-json-box" id="config-display-box">
                            <button class="g-json-copy" onclick="genCopyConfig()">Copy</button>
                            <code id="config-json-content"></code>
                        </div>
                    </div>

                    <button type="button" class="g-save-btn" onclick="saveConfiguration()">
                        <i class="fa fa-save"></i> Save Configuration
                    </button>
                </div>

            </div><!-- /.gen-scroll -->

            <!-- Footer nav -->
            <div class="gen-nav">
                <button type="button" class="g-nav-btn" id="gen-prev" onclick="genStep(-1)" disabled>
                    <i class="fa fa-chevron-left"></i> Back
                </button>
                <span class="g-step-lbl" id="gen-step-lbl">Step 1 of 4</span>
                <button type="button" class="g-nav-btn primary" id="gen-next" onclick="genStep(1)">
                    Next <i class="fa fa-chevron-right"></i>
                </button>
            </div>
        </aside>

        <!-- Canvas -->
        <div class="gen-canvas-area">
            <div class="gen-canvas-bar" id="canvas-toolbar">
                <div style="display:flex;align-items:center;gap:.5rem;flex:1;min-width:0;flex-wrap:wrap;">
                    <span class="gen-canvas-dims" id="canvas-dim-label">— × — px</span>
                    <span class="gen-drag-tip" id="gen-drag-tip"><i class="fa fa-arrows"></i> Drag · corner handle resize · <kbd style="font-size:.55rem;padding:.05rem .25rem;background:rgba(0,0,0,.4);color:#7dd3fc;border:1px solid rgba(255,255,255,.15);border-radius:3px;">Space</kbd> pan</span>
                    <div class="gen-toolbar-grp" id="gen-canvas-tools" style="display:none;">
                        <div class="gen-toolbar-sep"></div>
                        <button type="button" class="g-tool-btn" id="gen-undo-btn" title="Undo (Ctrl+Z)"><i class="fa fa-undo"></i></button>
                        <div class="gen-toolbar-sep"></div>
                        <button type="button" class="g-tool-btn" id="gen-grid-btn" title="Toggle grid"><i class="fa fa-th"></i></button>
                        <button type="button" class="gen-snap-badge" id="gen-snap-btn" title="Toggle snap to grid">
                            <i class="fa fa-magnet"></i> Snap
                        </button>
                        <div class="gen-toolbar-sep"></div>
                        <button type="button" class="g-tool-btn" id="gen-center-h-btn" title="Center selected horizontally"><i class="fa fa-align-center"></i> H</button>
                        <button type="button" class="g-tool-btn" id="gen-center-v-btn" title="Center selected vertically"><i class="fa fa-align-center"></i> V</button>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:.4rem;">
                    <button type="button" class="g-tool-btn" id="gen-shortcuts-btn" title="Keyboard shortcuts"><i class="fa fa-keyboard-o"></i></button>
                    <div class="gen-toolbar-sep"></div>
                    <div class="gen-zoom-grp">
                        <button type="button" class="g-zoom-btn" id="zoom-out" title="Zoom out (−)">−</button>
                        <span id="zoom-level" title="Click to reset to 100%">100%</span>
                        <button type="button" class="g-zoom-btn" id="zoom-in" title="Zoom in (+)">+</button>
                        <button type="button" class="g-zoom-btn" id="zoom-fit" title="Fit to viewport (0)">FIT</button>
                    </div>
                </div>
            </div>

            <div id="canvas-viewport">
                <div id="result_canvas" class="relative shadow-xl">

                    <div id="collage_background" class="absolute h-full w-full" style="z-index:0;">
                        <img class="h-full hidden object-contain object-top" src="" alt="">
                    </div>

                    <?php for ($i = 0; $i < count($demoImages); $i++) {
                        $imagePath = PathUtility::getPublicPath($demoImages[$i]);
                        $hiddenCls = ($i === 0) ? '' : 'hidden';
                        echo "<div id='picture-$i' class='absolute overflow-hidden w-full h-full $hiddenCls' style='z-index:1;'>
                                <img class='absolute object-left-top rotate-0 max-w-none' data-src='$imagePath'>
                                <img class='picture-frame absolute object-left-top rotate-0 max-w-none hidden'>
                                <div class='gen-drag-ov' data-fidx='$i'>
                                    <span class='gen-drag-lbl'>#" . ($i + 1) . "</span>
                                    <span class='gen-pos-tooltip'></span>
                                    <div class='gen-resize-hdl' data-fidx='$i' data-dir='nw'></div>
                                    <div class='gen-resize-hdl' data-fidx='$i' data-dir='n'></div>
                                    <div class='gen-resize-hdl' data-fidx='$i' data-dir='ne'></div>
                                    <div class='gen-resize-hdl' data-fidx='$i' data-dir='e'></div>
                                    <div class='gen-resize-hdl' data-fidx='$i' data-dir='se'></div>
                                    <div class='gen-resize-hdl' data-fidx='$i' data-dir='s'></div>
                                    <div class='gen-resize-hdl' data-fidx='$i' data-dir='sw'></div>
                                    <div class='gen-resize-hdl' data-fidx='$i' data-dir='w'></div>
                                </div>
                              </div>\n";
                    } ?>

                    <div id="gen-grid-overlay"></div>
                    <div class="gen-snap-line h" id="snap-h"></div>
                    <div class="gen-snap-line v" id="snap-v"></div>
                    <div id="gen-couple-divider"></div>

                    <div id="collage_frame" class="absolute h-full w-full" style="z-index:10;">
                        <img class="h-full w-full hidden" src="" alt="">
                    </div>

                    <div id="collage_text" class="absolute h-full font-selected" style="z-index:15;">
                        <div class="relative">
                            <div class="absolute whitespace-nowrap origin-top-left text-line-1 leading-none"></div>
                            <div class="absolute whitespace-nowrap origin-top-left text-line-2 leading-none"></div>
                            <div class="absolute whitespace-nowrap origin-top-left text-line-3 leading-none"></div>
                        </div>
                    </div>

                </div><!-- /#result_canvas -->
            </div><!-- /#canvas-viewport -->
        </div><!-- /.gen-canvas-area -->

    </div><!-- /.gen-body -->
</div><!-- /.gen-root -->

<!-- Keyboard shortcuts overlay -->
<div class="gen-shortcut-overlay" id="gen-shortcut-overlay">
    <div class="gen-shortcut-box">
        <div class="gen-shortcut-title"><i class="fa fa-keyboard-o"></i>&nbsp; Keyboard Shortcuts</div>
        <div class="gen-shortcut-grid">
            <div class="gen-shortcut-row"><kbd>↑ ↓ ← →</kbd> Nudge frame 1px</div>
            <div class="gen-shortcut-row"><kbd>Shift</kbd>+<kbd>↑↓←→</kbd> Nudge 10px</div>
            <div class="gen-shortcut-row"><kbd>Ctrl</kbd>+<kbd>↑↓←→</kbd> Nudge 100px</div>
            <div class="gen-shortcut-row"><kbd>Ctrl</kbd>+<kbd>Z</kbd> Undo last move</div>
            <div class="gen-shortcut-row"><kbd>+</kbd> / <kbd>−</kbd> Zoom in / out</div>
            <div class="gen-shortcut-row"><kbd>0</kbd> Fit canvas to view</div>
            <div class="gen-shortcut-row"><kbd>Space</kbd>+drag Pan canvas</div>
            <div class="gen-shortcut-row"><kbd>G</kbd> Toggle grid</div>
            <div class="gen-shortcut-row"><kbd>S</kbd> Toggle snap</div>
            <div class="gen-shortcut-row"><kbd>Del</kbd> Delete selected slot</div>
            <div class="gen-shortcut-row"><kbd>Tab</kbd> Next slot</div>
            <div class="gen-shortcut-row"><kbd>?</kbd> This panel</div>
        </div>
        <button class="gen-shortcut-close" onclick="document.getElementById('gen-shortcut-overlay').classList.remove('show')">Close</button>
    </div>
</div>

<!-- Undo toast -->
<div class="gen-undo-toast" id="gen-undo-toast">
    <i class="fa fa-undo"></i> <span id="gen-undo-msg">Undone</span>
</div>

<form id="configuration_form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data" class="hidden">
    <input type="hidden" name="new-configuration" value="">
</form>

<?php
$assetService = AssetService::getInstance();
include PathUtility::getAbsolutePath('admin/components/footer.scripts.php');
echo '<script src="' . $assetService->getUrl('resources/js/admin/generator.js') . '"></script>';
?>
<?php
if ($success) {
    echo '<script>setTimeout(function(){openToast("' . $languageService->translate('collage:generator:configuration_saved') . '")},500);</script>';
}
if ($error !== false) {
    echo '<script>setTimeout(function(){openToast("' . $languageService->translate('collage:generator:configuration_saving_error') . '","isError",5000)},500);</script>';
}
if ($warning) {
    echo '<script>setTimeout(function(){openToast("' . $languageService->translate('collage:generator:save_config_manually') . '","isWarning",5000)},500);</script>';
}
include PathUtility::getAbsolutePath('admin/components/footer.admin.php');
?>
