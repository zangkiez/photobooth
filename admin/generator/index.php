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
<style>
/* ═══════════════════════════════════════════════════════════════════
   COLLAGE GENERATOR — Complete UI Redesign v2
═══════════════════════════════════════════════════════════════════ */
:root {
    --g-primary: #6366f1;
    --g-primary-dark: #4f46e5;
    --g-primary-light: #eef2ff;
    --g-success: #22c55e;
    --g-danger: #ef4444;
    --g-warning: #f59e0b;
    --g-bg: #f1f5f9;
    --g-surface: #ffffff;
    --g-border: #e2e8f0;
    --g-text: #1e293b;
    --g-muted: #64748b;
    --g-radius: 12px;
    --g-radius-sm: 8px;
    --g-shadow: 0 1px 3px rgba(0,0,0,.08),0 1px 2px rgba(0,0,0,.05);
    --g-shadow-md: 0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -2px rgba(0,0,0,.1);
    --g-shadow-lg: 0 10px 15px -3px rgba(0,0,0,.1),0 4px 6px -4px rgba(0,0,0,.1);
    --g-sidebar-w: 420px;
}
*, *::before, *::after { box-sizing: border-box; }

.gen-root {
    position: fixed; inset: 0;
    display: flex; flex-direction: column;
    background: var(--g-bg); overflow: hidden;
    font-family: system-ui, -apple-system, sans-serif;
    font-size: 14px; color: var(--g-text);
}

/* Top bar */
.gen-topbar {
    height: 54px; background: var(--g-surface);
    border-bottom: 1px solid var(--g-border);
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 1.25rem; flex-shrink: 0;
    box-shadow: var(--g-shadow); z-index: 50;
}
.gen-topbar-title {
    font-size: 1rem; font-weight: 700; color: var(--g-text);
    display: flex; align-items: center; gap: .5rem;
}
.gen-topbar-icon {
    width: 28px; height: 28px; border-radius: 8px;
    background: linear-gradient(135deg, var(--g-primary), #7c3aed);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: .85rem;
}
.gen-topbar-actions { display: flex; align-items: center; gap: .5rem; }
.gen-topbar-btn {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .3rem .75rem; border-radius: 7px;
    border: 1.5px solid var(--g-border); background: var(--g-surface);
    color: var(--g-muted); font-size: .78rem; font-weight: 600;
    cursor: pointer; text-decoration: none; transition: all .15s;
}
.gen-topbar-btn:hover { border-color: var(--g-primary); color: var(--g-primary); background: var(--g-primary-light); }

/* Stepper */
.gen-stepper {
    height: 46px; background: var(--g-surface);
    border-bottom: 1px solid var(--g-border);
    display: flex; align-items: stretch; flex-shrink: 0;
    overflow-x: auto; scrollbar-width: none;
}
.gen-stepper::-webkit-scrollbar { display: none; }
.gen-step-tab {
    display: flex; align-items: center; gap: .5rem;
    padding: 0 1.125rem; cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all .2s; white-space: nowrap;
    color: var(--g-muted); font-size: .8rem; font-weight: 500;
    user-select: none; flex-shrink: 0;
}
.gen-step-tab:hover { color: var(--g-primary); background: var(--g-primary-light); }
.gen-step-tab.active { color: var(--g-primary); border-bottom-color: var(--g-primary); background: var(--g-primary-light); }
.gen-step-tab.done .gen-step-num { background: var(--g-success); color: #fff; }
.gen-step-num {
    width: 20px; height: 20px; border-radius: 50%;
    background: var(--g-border); color: var(--g-muted);
    font-size: .68rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; transition: all .2s;
}
.gen-step-tab.active .gen-step-num { background: var(--g-primary); color: #fff; }
.gen-step-sep { color: #d1d5db; font-size: .6rem; display: flex; align-items: center; padding: 0 .1rem; flex-shrink: 0; }

/* Body */
.gen-body { flex: 1; display: flex; min-height: 0; overflow: hidden; }

/* Sidebar */
.gen-sidebar {
    width: var(--g-sidebar-w); min-width: 300px; max-width: 460px;
    flex-shrink: 0; background: var(--g-surface);
    border-right: 1px solid var(--g-border);
    display: flex; flex-direction: column; overflow: hidden;
}
.gen-scroll {
    flex: 1; overflow-y: auto; overflow-x: hidden;
    padding: 1rem 1.125rem; scroll-behavior: smooth;
}
.gen-scroll::-webkit-scrollbar { width: 4px; }
.gen-scroll::-webkit-scrollbar-thumb { background: var(--g-border); border-radius: 2px; }
.gen-nav {
    border-top: 1px solid var(--g-border);
    padding: .75rem 1.125rem;
    display: flex; align-items: center; justify-content: space-between;
    flex-shrink: 0; background: var(--g-surface); gap: .5rem;
}

/* Step panels — animated transitions */
.gen-panel { display: none; opacity: 0; transform: translateX(8px); }
.gen-panel.active { display: block; animation: panelIn .22s cubic-bezier(.4,0,.2,1) forwards; }
@keyframes panelIn { to { opacity: 1; transform: translateX(0); } }

/* Cards */
.g-card {
    background: var(--g-surface); border: 1px solid var(--g-border);
    border-radius: var(--g-radius); padding: .875rem 1rem;
    margin-bottom: .875rem;
}
.g-card-title {
    font-size: .7rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .06em;
    color: var(--g-muted); margin-bottom: .75rem;
    display: flex; align-items: center; gap: .4rem;
}
.g-card-title i { font-size: .8rem; }

/* Step header */
.g-step-h {
    font-size: .95rem; font-weight: 700; color: var(--g-text);
    margin-bottom: .875rem; padding-bottom: .625rem;
    border-bottom: 2px solid var(--g-border);
}
.g-step-h small {
    display: block; font-size: .72rem; font-weight: 400;
    color: var(--g-muted); margin-top: .2rem; line-height: 1.4;
}

/* Fields */
.g-label {
    display: block; font-size: .68rem; font-weight: 700;
    color: var(--g-muted); margin-bottom: .2rem;
    text-transform: uppercase; letter-spacing: .04em;
}
.g-hint { font-size: .66rem; color: var(--g-muted); margin-top: .2rem; line-height: 1.4; }
.g-row2 { display: grid; grid-template-columns: 1fr 1fr; gap: .625rem; }
.g-row3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: .5rem; }
.g-field { margin-bottom: .625rem; }
.g-field:last-child { margin-bottom: 0; }

/* Presets */
.g-presets { display: flex; flex-wrap: wrap; gap: .35rem; margin-bottom: .625rem; }
.g-preset {
    padding: .22rem .55rem; border-radius: 5px;
    border: 1.5px solid var(--g-border); background: #fff;
    font-size: .7rem; font-weight: 600; color: var(--g-muted);
    cursor: pointer; transition: all .15s; line-height: 1.5;
}
.g-preset:hover { border-color: var(--g-primary); color: var(--g-primary); background: var(--g-primary-light); }
.g-preset.active { border-color: var(--g-primary); background: var(--g-primary); color: #fff; }

/* Toggle switch */
.g-toggle-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: .4rem 0; border-bottom: 1px solid #f8fafc;
}
.g-toggle-row:last-child { border-bottom: none; }
.g-toggle-lbl { font-size: .8rem; font-weight: 500; color: var(--g-text); }
.g-toggle-sub { font-size: .66rem; color: var(--g-muted); margin-top: .1rem; }
.g-switch { position: relative; width: 34px; height: 19px; flex-shrink: 0; }
.g-switch input { opacity: 0; width: 0; height: 0; }
.g-slider {
    position: absolute; cursor: pointer; inset: 0;
    background: var(--g-border); border-radius: 10px; transition: background .2s;
}
.g-slider::before {
    content: ''; position: absolute;
    width: 13px; height: 13px; left: 3px; top: 3px;
    background: #fff; border-radius: 50%;
    transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,.2);
}
.g-switch input:checked + .g-slider { background: var(--g-primary); }
.g-switch input:checked + .g-slider::before { transform: translateX(15px); }

/* Number spinner */
.g-spin {
    display: flex; align-items: stretch;
    border: 1.5px solid var(--g-border); border-radius: var(--g-radius-sm);
    overflow: hidden; background: #fff; height: 32px;
    transition: border-color .15s;
}
.g-spin:focus-within { border-color: var(--g-primary); box-shadow: 0 0 0 3px rgba(99,102,241,.1); }
.g-spin input[type="number"], .g-spin input[type="text"] {
    flex: 1 1 0%; min-width: 0;
    border: none !important; border-radius: 0 !important;
    outline: none !important; box-shadow: none !important;
    text-align: center; padding: 0 .2rem;
    font-size: .8rem; background: transparent; color: var(--g-text);
}
.g-spin-btn {
    flex: 0 0 26px; width: 26px;
    background: #f8fafc; border: none; cursor: pointer;
    font-size: .85rem; font-weight: 700; color: var(--g-muted);
    display: flex; align-items: center; justify-content: center;
    transition: background .1s, color .1s; padding: 0; user-select: none;
}
.g-spin-btn:hover { background: #e2e8f0; color: var(--g-text); }
.g-spin-btn:active { background: #cbd5e1; }

/* Frame card */
.gen-frame-card {
    background: #fafafa; border: 1.5px solid var(--g-border);
    border-radius: var(--g-radius); padding: .625rem .75rem;
    margin-bottom: .5rem; transition: border-color .2s, box-shadow .2s, background .2s;
    will-change: transform;
}
.gen-frame-card:hover { border-color: #a5b4fc; box-shadow: var(--g-shadow-md); }
.gen-frame-card.selected { border-color: var(--g-primary); background: var(--g-primary-light); box-shadow: 0 0 0 3px rgba(99,102,241,.12); }
.gen-frame-hd { display: flex; align-items: center; justify-content: space-between; margin-bottom: .4rem; cursor: pointer; }
.gen-frame-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    background: var(--g-primary); color: #fff; border-radius: 4px;
    padding: .12rem .4rem; font-size: .68rem; font-weight: 700;
    transition: background .15s;
}
.gen-frame-card:not(.selected) .gen-frame-badge { background: #94a3b8; }
.gen-frame-card.selected .gen-frame-badge { background: var(--g-primary); }
.gen-frame-del {
    width: 24px; height: 24px; border-radius: 5px; border: none;
    background: #fee2e2; color: var(--g-danger);
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    font-size: .75rem; transition: all .15s; flex-shrink: 0;
}
.gen-frame-del:hover { background: var(--g-danger); color: #fff; transform: scale(1.1); }
.gen-frame-body {
    overflow: hidden;
    max-height: 0;
    opacity: 0;
    transition: max-height .3s cubic-bezier(.4,0,.2,1), opacity .25s ease, padding .3s ease;
    padding-top: 0;
}
.gen-frame-card.open .gen-frame-body {
    max-height: 800px;
    opacity: 1;
    padding-top: .4rem;
}
.gen-frame-toggle {
    width: 18px; height: 18px; border: none; background: transparent;
    color: var(--g-muted); cursor: pointer; display: flex;
    align-items: center; justify-content: center; font-size: .65rem;
    transition: transform .25s cubic-bezier(.4,0,.2,1), color .15s; margin-left: .2rem;
}
.gen-frame-card.open .gen-frame-toggle { transform: rotate(180deg); color: var(--g-primary); }

/* Couple mode */
.g-couple {
    background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff;
    border-radius: var(--g-radius); padding: .7rem .875rem;
    margin-bottom: .875rem; display: flex; align-items: center; gap: .625rem;
}
.g-couple-icon { font-size: 1.3rem; flex-shrink: 0; }
.g-couple-text { flex: 1; }
.g-couple-text strong { display: block; font-size: .82rem; }
.g-couple-text span { font-size: .7rem; opacity: .85; line-height: 1.4; }

/* Load saved banner */
.g-load-banner {
    background: var(--g-primary-light); border: 1.5px solid var(--g-primary);
    border-radius: var(--g-radius); padding: .6rem .875rem;
    margin-bottom: .875rem; display: flex; align-items: center; gap: .625rem;
}
.g-load-banner i { color: var(--g-primary); font-size: .95rem; }
.g-load-banner span { flex: 1; font-size: .78rem; color: var(--g-primary); font-weight: 500; }
.g-load-banner button {
    padding: .28rem .65rem; border-radius: 5px; border: none;
    background: var(--g-primary); color: #fff;
    font-size: .72rem; font-weight: 600; cursor: pointer; white-space: nowrap;
}
.g-load-banner button:hover { background: var(--g-primary-dark); }

/* Detect-from-BG button */
.g-detect-btn {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .38rem .7rem; border-radius: 6px;
    border: 1.5px solid var(--g-primary); background: var(--g-primary-light);
    color: var(--g-primary); font-size: .72rem; font-weight: 600;
    cursor: pointer; transition: all .15s; margin-top: .4rem;
}
.g-detect-btn:hover { background: var(--g-primary); color: #fff; }

/* Add frame button */
.g-add-frame {
    width: 100%; padding: .5rem;
    border: 2px dashed var(--g-border); border-radius: var(--g-radius);
    background: transparent; color: var(--g-muted);
    font-size: .8rem; font-weight: 600; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: .4rem;
    transition: all .15s; margin-top: .25rem;
}
.g-add-frame:hover { border-color: var(--g-primary); color: var(--g-primary); background: var(--g-primary-light); }

/* Nav buttons */
.g-nav-btn {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .42rem .875rem; border-radius: var(--g-radius-sm);
    border: 1.5px solid var(--g-border); background: #fff;
    font-size: .78rem; font-weight: 600; color: var(--g-text);
    cursor: pointer; transition: all .15s;
}
.g-nav-btn:hover { border-color: var(--g-primary); color: var(--g-primary); }
.g-nav-btn:disabled { opacity: .4; cursor: not-allowed; pointer-events: none; }
.g-nav-btn.primary { background: var(--g-primary); border-color: var(--g-primary); color: #fff; }
.g-nav-btn.primary:hover { background: var(--g-primary-dark); border-color: var(--g-primary-dark); }
.g-step-lbl { font-size: .72rem; color: var(--g-muted); font-weight: 500; }

/* Save button */
.g-save-btn {
    width: 100%; padding: .7rem; border-radius: 9999px; border: none;
    background: linear-gradient(135deg, var(--g-primary), #7c3aed); color: #fff;
    font-size: .9rem; font-weight: 700; cursor: pointer;
    box-shadow: 0 4px 16px rgba(99,102,241,.4);
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    transition: transform .15s, box-shadow .15s;
}
.g-save-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,.5); }
.g-save-btn:active { transform: scale(.98); }

/* JSON box */
.g-json-box {
    background: #1e293b; color: #94a3b8;
    padding: .7rem; border-radius: 8px;
    font-family: monospace; font-size: .68rem;
    max-height: 140px; overflow-y: auto;
    white-space: pre-wrap; word-break: break-all;
    margin-top: .5rem; position: relative;
}
.g-json-copy {
    position: absolute; top: .3rem; right: .3rem;
    background: rgba(255,255,255,.1); border: none; color: #94a3b8;
    padding: .12rem .35rem; border-radius: 3px; font-size: .6rem; cursor: pointer;
}
.g-json-copy:hover { background: rgba(255,255,255,.2); color: #fff; }

/* Canvas area */
.gen-canvas-area {
    flex: 1; display: flex; flex-direction: column;
    background: #0f172a; min-height: 0; overflow: hidden;
}
.gen-canvas-bar {
    display: flex; align-items: center; justify-content: space-between;
    padding: .38rem 1rem; background: rgba(15,23,42,.95);
    flex-shrink: 0; border-bottom: 1px solid rgba(255,255,255,.07);
    gap: .5rem;
}
.gen-canvas-dims { color: #475569; font-size: .66rem; font-family: monospace; }
.gen-drag-tip {
    font-size: .65rem; color: rgba(99,102,241,.85);
    font-style: italic; white-space: nowrap; display: none;
    background: rgba(99,102,241,.1); padding: .12rem .4rem; border-radius: 4px;
}
.gen-zoom-grp { display: flex; align-items: center; gap: 4px; }
.g-zoom-btn {
    width: 22px; height: 22px; border-radius: 50%; border: none;
    background: rgba(255,255,255,.1); color: #e2e8f0;
    font-size: .82rem; font-weight: 700; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background .15s; padding: 0; line-height: 1;
}
.g-zoom-btn:hover { background: rgba(255,255,255,.25); }
#zoom-level {
    color: #e2e8f0; min-width: 36px; text-align: center;
    font-size: .66rem; font-family: monospace;
}
#zoom-fit { width: 28px; border-radius: 4px; font-size: .55rem; letter-spacing: .02em; }
#canvas-viewport {
    flex: 1; overflow: hidden;
    display: flex; align-items: center; justify-content: center;
    padding: 1.5rem; min-height: 200px;
}
#result_canvas {
    transform-origin: center;
    transition: transform .18s cubic-bezier(.4,0,.2,1);
    flex-shrink: 0; width: 100%; position: relative;
}

/* Canvas couple divider */
#gen-couple-divider {
    position: absolute; top: 0; bottom: 0; left: 50%;
    width: 3px; background: rgba(99,102,241,.7);
    z-index: 20; display: none; pointer-events: none;
}
#gen-couple-divider::after {
    content: 'SPLIT';
    position: absolute; top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(99,102,241,.9); color: #fff;
    font-size: .5rem; font-weight: 700; letter-spacing: .05em;
    padding: .18rem .28rem; border-radius: 3px; white-space: nowrap;
}

/* Drag overlays on canvas */
.gen-drag-ov {
    position: absolute; inset: 0;
    border: 2px dashed transparent; box-sizing: border-box;
    display: none; cursor: grab; z-index: 8;
    transition: border-color .15s;
}
.gen-drag-ov.visible { display: block; }
.gen-drag-ov:hover { border-color: rgba(99,102,241,.5); }
.gen-drag-ov.sel { border-color: var(--g-primary); border-style: solid; }
.gen-drag-ov.dragging { cursor: grabbing !important; }
.gen-drag-lbl {
    position: absolute; top: 3px; left: 3px;
    background: var(--g-primary); color: #fff;
    font-size: .58rem; font-weight: 700;
    padding: .08rem .28rem; border-radius: 3px;
    pointer-events: none; opacity: 0; transition: opacity .15s;
}
.gen-drag-ov:hover .gen-drag-lbl, .gen-drag-ov.sel .gen-drag-lbl { opacity: 1; }
.gen-resize-hdl {
    position: absolute; bottom: 0; right: 0;
    width: 13px; height: 13px;
    background: var(--g-primary); border-radius: 3px 0 0 0;
    cursor: se-resize; opacity: 0; transition: opacity .15s; z-index: 9;
}
.gen-drag-ov:hover .gen-resize-hdl, .gen-drag-ov.sel .gen-resize-hdl { opacity: .9; }
.gen-resize-hdl:hover { opacity: 1 !important; }

/* Modal */
.modal{display:flex;position:fixed;inset:0;background:rgba(0,0,0,.8);justify-content:center;align-items:center;z-index:16777372;}
.modal-inner{flex-direction:column;display:flex;position:relative;color:#313131;background:#fff;max-width:calc(100dvw - 4rem);max-height:calc(100dvh - 4rem);}
.modal-body{overflow-y:scroll;padding:2rem;white-space:pre;}
.modal-buttonbar{display:flex;background:color-mix(in srgb,#6366f1,#fff 60%);gap:1px;}
.modal-button{flex-grow:1;display:inline-flex;padding:1rem;gap:.25rem;font-size:1rem;color:#fff;text-align:center;cursor:pointer;user-select:none;border:none;background:#6366f1;justify-content:center;align-items:center;}
.modal-button:hover{background:#4f46e5;}

/* Suppress AdminInput default labels inside our G-label wrapped fields */
.gen-frame-body .adminLabel { display: none !important; }
.gen-frame-body .adminInput { margin-bottom: 0 !important; }
.gen-frame-body .adminImageSelection-preview { max-height: 65px; border-radius: 5px; }

/* Responsive */
@media (max-width: 900px) {
    .gen-body { flex-direction: column; }
    .gen-sidebar { width: 100%; max-width: 100%; min-width: 0; border-right: none; border-bottom: 1px solid var(--g-border); max-height: 56vh; }
    .gen-canvas-area { min-height: 44vh; }
}
@media (max-width: 560px) {
    .gen-step-tab .gen-step-label { display: none; }
    .gen-step-tab { padding: 0 .7rem; }
}
</style>

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
                <span class="gen-canvas-dims" id="canvas-dim-label">— × — px</span>
                <span class="gen-drag-tip" id="gen-drag-tip"><i class="fa fa-hand-paper-o"></i> Drag to move &bull; corner handle to resize</span>
                <div class="gen-zoom-grp">
                    <button type="button" class="g-zoom-btn" id="zoom-out" title="Zoom out">−</button>
                    <span id="zoom-level">100%</span>
                    <button type="button" class="g-zoom-btn" id="zoom-in" title="Zoom in">+</button>
                    <button type="button" class="g-zoom-btn" id="zoom-fit">FIT</button>
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
                                    <div class='gen-resize-hdl' data-fidx='$i'></div>
                                </div>
                              </div>\n";
                    } ?>

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

<form id="configuration_form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data" class="hidden">
    <input type="hidden" name="new-configuration" value="">
</form>

<?php
$assetService = AssetService::getInstance();
include PathUtility::getAbsolutePath('admin/components/footer.scripts.php');
echo '<script src="' . $assetService->getUrl('resources/js/admin/generator.js') . '"></script>';
?>
<script>
/* ════════════════════════════════════════════════════════════════
   GENERATOR — Wizard + Drag-Drop + Couple Mode + Spinners + Zoom
════════════════════════════════════════════════════════════════ */

// ── Wizard ───────────────────────────────────────────────────
var genCurStep = 1, GEN_STEPS = 4;

function genGoTo(step) {
    step = Math.max(1, Math.min(GEN_STEPS, step));
    genCurStep = step;
    document.querySelectorAll('.gen-panel').forEach(function(el) {
        el.classList.toggle('active', +el.dataset.panel === step);
    });
    document.querySelectorAll('.gen-step-tab[data-go]').forEach(function(el) {
        var s = +el.dataset.go;
        el.classList.toggle('active', s === step);
        el.classList.toggle('done',   s < step);
    });
    var prev = document.getElementById('gen-prev');
    var next = document.getElementById('gen-next');
    var lbl  = document.getElementById('gen-step-lbl');
    if (prev) prev.disabled = step === 1;
    if (lbl)  lbl.textContent = 'Step ' + step + ' of ' + GEN_STEPS;
    if (next) {
        if (step === GEN_STEPS) {
            next.innerHTML = '<i class="fa fa-save"></i> Save';
            next.onclick   = function() { saveConfiguration(); };
        } else {
            next.innerHTML = 'Next <i class="fa fa-chevron-right"></i>';
            next.onclick   = function() { genStep(1); };
        }
    }
    var tip = document.getElementById('gen-drag-tip');
    if (tip) tip.style.display = (step === 3) ? 'flex' : 'none';
    document.querySelectorAll('.gen-drag-ov').forEach(function(ov) {
        ov.classList.toggle('visible', step === 3);
        ov.style.pointerEvents = (step === 3) ? 'auto' : 'none';
    });
}
function genStep(dir) { genGoTo(genCurStep + dir); }
document.querySelectorAll('.gen-step-tab[data-go]').forEach(function(el) {
    el.addEventListener('click', function() { genGoTo(+el.dataset.go); });
});

// ── Collapse/expand frame card ────────────────────────────────
function genToggleFrame(hd) {
    var card = hd.closest('.gen-frame-card');
    if (!card) return;
    card.classList.toggle('open');
    var idx = +card.dataset.picture.replace('picture-', '');
    genSelectFrame(idx);
}

// ── Select a frame slot ───────────────────────────────────────
function genSelectFrame(idx) {
    document.querySelectorAll('.gen-frame-card').forEach(function(c) { c.classList.remove('selected'); });
    var card = document.querySelector('[data-picture="picture-' + idx + '"]');
    if (card) { card.classList.add('selected'); card.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }
    document.querySelectorAll('.gen-drag-ov').forEach(function(o) { o.classList.remove('sel'); });
    var ov = document.querySelector('.gen-drag-ov[data-fidx="' + idx + '"]');
    if (ov) ov.classList.add('sel');
}

// ── Auto-detect canvas size from BG image ────────────────────
function genDetectBgSize() {
    var bgPath = $('input[name="generator-background"]').val();
    if (!bgPath) {
        if (typeof openToast === 'function') openToast('Please select a background image first', 'isWarning', 3000);
        return;
    }
    var tmp = new Image();
    tmp.onload = function() {
        var w = tmp.naturalWidth, h = tmp.naturalHeight;
        $('input[name="final_width"]').val(w).trigger('change');
        $('input[name="final_height"]').val(h).trigger('change');
        if (typeof openToast === 'function') openToast('Canvas set to ' + w + ' × ' + h + ' px', 'isSuccess', 2500);
        setTimeout(function() { if (window._genFit) window._genFit(); }, 300);
    };
    tmp.onerror = function() {
        if (typeof openToast === 'function') openToast('Could not read image dimensions', 'isError', 3000);
    };
    var src = (typeof toPublicUrl === 'function') ? toPublicUrl(bgPath) : bgPath;
    tmp.src = src + (src.indexOf('?') >= 0 ? '&' : '?') + '_t=' + Date.now();
}
document.getElementById('gen-detect-btn1') && document.getElementById('gen-detect-btn1').addEventListener('click', genDetectBgSize);
document.getElementById('gen-detect-btn2') && document.getElementById('gen-detect-btn2').addEventListener('click', genDetectBgSize);

// ── Canvas size presets ───────────────────────────────────────
document.querySelectorAll('.g-preset').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.g-preset').forEach(function(b) { b.classList.remove('active'); });
        btn.classList.add('active');
        $('input[name="final_width"]').val(btn.dataset.w).trigger('change');
        $('input[name="final_height"]').val(btn.dataset.h).trigger('change');
    });
});

// ── Drag-move + resize frames on canvas ──────────────────────
(function() {
    var mode = null; // 'drag' | 'resize'
    var activeIdx = null;
    var sx, sy, sl, st, sw, sh;

    function getScale() {
        var cv = document.getElementById('result_canvas');
        var cw = parseFloat($('input[name="final_width"]').val())  || 1;
        var ch = parseFloat($('input[name="final_height"]').val()) || 1;
        return { x: cv.offsetWidth / cw, y: cv.offsetHeight / ch };
    }
    function numVal(name) {
        return parseFloat($('input[name="' + name + '"]').val()) || 0;
    }
    function resolveFrameSize(idx) {
        var wRaw = $('input[name="picture-width-'  + idx + '"]').val();
        var hRaw = $('input[name="picture-height-' + idx + '"]').val();
        var s = getScale();
        var el = document.getElementById('picture-' + idx);
        var fw = /^-?\d+\.?\d*$/.test(wRaw.trim()) ? parseFloat(wRaw) : (el ? el.offsetWidth  / s.x : 400);
        var fh = /^-?\d+\.?\d*$/.test(hRaw.trim()) ? parseFloat(hRaw) : (el ? el.offsetHeight / s.y : 300);
        return { w: fw, h: fh };
    }

    document.addEventListener('mousedown', function(e) {
        if (genCurStep !== 3) return;
        var rh = e.target.closest('.gen-resize-hdl');
        if (rh) {
            activeIdx = +rh.dataset.fidx;
            var sz = resolveFrameSize(activeIdx);
            sx = e.clientX; sy = e.clientY; sw = sz.w; sh = sz.h;
            $('input[name="picture-width-'  + activeIdx + '"]').val(Math.round(sw));
            $('input[name="picture-height-' + activeIdx + '"]').val(Math.round(sh));
            mode = 'resize'; e.preventDefault(); genSelectFrame(activeIdx); return;
        }
        var ov = e.target.closest('.gen-drag-ov');
        if (ov) {
            activeIdx = +ov.dataset.fidx;
            sx = e.clientX; sy = e.clientY;
            sl = numVal('picture-x-position-' + activeIdx);
            st = numVal('picture-y-position-' + activeIdx);
            mode = 'drag'; ov.classList.add('dragging'); e.preventDefault(); genSelectFrame(activeIdx);
        }
    });

    document.addEventListener('mousemove', function(e) {
        if (!mode) return;
        var s  = getScale();
        var dx = e.clientX - sx, dy = e.clientY - sy;
        var cw = parseFloat($('input[name="final_width"]').val());
        var ch = parseFloat($('input[name="final_height"]').val());
        if (mode === 'drag') {
            var nx = Math.max(0, Math.min(cw - 10, sl + dx / s.x));
            var ny = Math.max(0, Math.min(ch - 10, st + dy / s.y));
            $('input[name="picture-x-position-' + activeIdx + '"]').val(Math.round(nx));
            $('input[name="picture-y-position-' + activeIdx + '"]').val(Math.round(ny));
            $('input[name="picture-x-position-' + activeIdx + '"]').trigger('change');
        } else {
            var nw = Math.max(40, sw + dx / s.x);
            var nh = Math.max(40, sh + dy / s.y);
            $('input[name="picture-width-'  + activeIdx + '"]').val(Math.round(nw));
            $('input[name="picture-height-' + activeIdx + '"]').val(Math.round(nh));
            $('input[name="picture-width-'  + activeIdx + '"]').trigger('change');
        }
    });

    document.addEventListener('mouseup', function() {
        document.querySelectorAll('.gen-drag-ov').forEach(function(o) { o.classList.remove('dragging'); });
        mode = null; activeIdx = null;
    });

    // Touch
    document.addEventListener('touchstart', function(e) {
        if (genCurStep !== 3) return;
        var ov = e.target.closest('.gen-drag-ov');
        if (ov) {
            var t = e.touches[0]; activeIdx = +ov.dataset.fidx;
            sx = t.clientX; sy = t.clientY;
            sl = numVal('picture-x-position-' + activeIdx);
            st = numVal('picture-y-position-' + activeIdx);
            mode = 'drag'; genSelectFrame(activeIdx);
        }
    }, { passive: true });
    document.addEventListener('touchmove', function(e) {
        if (mode !== 'drag') return;
        var t = e.touches[0], s = getScale();
        $('input[name="picture-x-position-' + activeIdx + '"]').val(Math.round(sl + (t.clientX - sx) / s.x)).trigger('change');
        $('input[name="picture-y-position-' + activeIdx + '"]').val(Math.round(st + (t.clientY - sy) / s.y)).trigger('change');
    }, { passive: true });
    document.addEventListener('touchend', function() { mode = null; activeIdx = null; });
}());

// ── Couple Mode ───────────────────────────────────────────────
(function() {
    var tog = document.getElementById('gen-couple-toggle');
    if (!tog) return;
    tog.addEventListener('change', function() {
        var on = this.checked;
        var div = document.getElementById('gen-couple-divider');
        if (div) div.style.display = on ? 'block' : 'none';
        if (on) {
            genApplyCouple();
            if (typeof openToast === 'function') openToast('Couple mode ON — 3 + 3 mirrored slots', 'isSuccess', 2500);
        } else {
            if (typeof openToast === 'function') openToast('Couple mode OFF', 'isSuccess', 2000);
        }
    });
}());

function genApplyCouple() {
    var cw = parseFloat($('input[name="final_width"]').val())  || 1500;
    var ch = parseFloat($('input[name="final_height"]').val()) || 1000;
    var cv = document.getElementById('result_canvas');
    var sx = cv.offsetWidth / cw, sy = cv.offsetHeight / ch;

    for (var i = 0; i < 3; i++) {
        var card = document.querySelector('[data-picture="picture-' + i + '"]');
        if (!card || card.classList.contains('hidden')) continue;
        var el   = document.getElementById('picture-' + i);
        var wRaw = $('input[name="picture-width-'  + i + '"]').val();
        var hRaw = $('input[name="picture-height-' + i + '"]').val();
        var fw   = /^-?\d+\.?\d*$/.test(wRaw.trim()) ? parseFloat(wRaw) : (el ? el.offsetWidth  / sx : 400);
        var fh   = /^-?\d+\.?\d*$/.test(hRaw.trim()) ? parseFloat(hRaw) : (el ? el.offsetHeight / sy : 300);
        var fx   = parseFloat($('input[name="picture-x-position-' + i + '"]').val()) || 0;
        var fy   = parseFloat($('input[name="picture-y-position-' + i + '"]').val()) || 0;
        var mi   = i + 3;
        var mc   = document.querySelector('[data-picture="picture-' + mi + '"]');
        var md   = document.getElementById('picture-' + mi);
        if (mc) mc.classList.remove('hidden');
        if (md) md.classList.remove('hidden');
        $('input[name="picture-x-position-' + mi + '"]').val(Math.round(cw - fx - fw)).trigger('change');
        $('input[name="picture-y-position-' + mi + '"]').val(Math.round(fy)).trigger('change');
        $('input[name="picture-width-'  + mi + '"]').val(Math.round(fw)).trigger('change');
        $('input[name="picture-height-' + mi + '"]').val(Math.round(fh)).trigger('change');
    }
    changeGeneralSetting();
}

// ── Canvas zoom ───────────────────────────────────────────────
(function() {
    var zoom = 1.0, MIN = 0.1, MAX = 4.0, STEP = 0.1;
    function setZoom(z) {
        zoom = Math.min(MAX, Math.max(MIN, +z.toFixed(2)));
        var cv = document.getElementById('result_canvas');
        if (cv) cv.style.transform = 'scale(' + zoom + ')';
        var lbl = document.getElementById('zoom-level');
        if (lbl) lbl.textContent = Math.round(zoom * 100) + '%';
    }
    function fit() {
        var vp = document.getElementById('canvas-viewport');
        var cv = document.getElementById('result_canvas');
        if (!vp || !cv) return;
        var vw = vp.clientWidth - 48, vh = vp.clientHeight - 48;
        var cw = cv.offsetWidth,     ch = cv.offsetHeight;
        if (cw > 0 && ch > 0) setZoom(Math.min(vw / cw, vh / ch, 1.0));
    }
    window._genFit = fit;
    function updateDimLabel() {
        var w = $('input[name="final_width"]').val();
        var h = $('input[name="final_height"]').val();
        var el = document.getElementById('canvas-dim-label');
        if (el && w && h) el.textContent = w + ' × ' + h + ' px';
    }
    $(function() {
        $('#zoom-in').on('click',  function() { setZoom(zoom + STEP); });
        $('#zoom-out').on('click', function() { setZoom(zoom - STEP); });
        $('#zoom-fit').on('click', fit);
        var vp = document.getElementById('canvas-viewport');
        if (vp) vp.addEventListener('wheel', function(e) {
            e.preventDefault();
            setZoom(zoom + (e.deltaY < 0 ? STEP : -STEP));
        }, { passive: false });
        $(document).on('keydown', function(e) {
            if ($(e.target).is('input,select,textarea')) return;
            if (e.key === '=' || e.key === '+') { e.preventDefault(); setZoom(zoom + STEP); }
            if (e.key === '-' || e.key === '_') { e.preventDefault(); setZoom(zoom - STEP); }
            if (e.key === '0')                  { e.preventDefault(); fit(); }
        });
        $('input[name="final_width"],input[name="final_height"]').on('change keyup', updateDimLabel);
        setTimeout(updateDimLabel, 600);
        setTimeout(fit, 750);
        $(window).on('resize', function() { setTimeout(fit, 100); });
    });
}());

// ── Keyboard ↑/↓ for number/text inputs ──────────────────────
$(document).on('keydown', 'input[type="number"],input[type="text"]', function(e) {
    if (e.key !== 'ArrowUp' && e.key !== 'ArrowDown') return;
    var v = this.value;
    if (!/^-?\d*\.?\d+$/.test(v.trim())) return;
    e.preventDefault();
    var step = (e.ctrlKey || e.metaKey) ? 100 : e.shiftKey ? 10 : 1;
    var n = parseFloat(v) + (e.key === 'ArrowUp' ? 1 : -1) * step;
    var mn = parseFloat(this.getAttribute('min')), mx = parseFloat(this.getAttribute('max'));
    if (!isNaN(mn)) n = Math.max(mn, n);
    if (!isNaN(mx)) n = Math.min(mx, n);
    this.value = n; $(this).trigger('change');
});

// ── Number spinner +/- buttons ───────────────────────────────
function _spin(inp, dir, ev) {
    var step = (ev && (ev.ctrlKey || ev.metaKey)) ? 100 : (ev && ev.shiftKey) ? 10 : 1;
    var v = inp.value;
    if (!/^-?\d*\.?\d+$/.test(v.trim())) return;
    var n = parseFloat(v) + dir * step;
    var mn = parseFloat(inp.getAttribute('min')), mx = parseFloat(inp.getAttribute('max'));
    if (!isNaN(mn)) n = Math.max(mn, n);
    if (!isNaN(mx)) n = Math.min(mx, n);
    inp.value = n; $(inp).trigger('change');
}
function initSpinners() {
    document.querySelectorAll('input[type="number"]').forEach(function(inp) {
        if (inp.closest('.g-spin')) return;
        var wrap = document.createElement('div'); wrap.className = 'g-spin';
        var minus = document.createElement('button');
        minus.type = 'button'; minus.className = 'g-spin-btn'; minus.innerHTML = '&minus;';
        minus.title = 'Decrease (Shift ×10, Ctrl ×100)';
        minus.addEventListener('mousedown', function(ev) { ev.preventDefault(); });
        minus.addEventListener('click',     function(ev) { _spin(inp, -1, ev); });
        var plus = document.createElement('button');
        plus.type = 'button'; plus.className = 'g-spin-btn'; plus.innerHTML = '+';
        plus.title = 'Increase (Shift ×10, Ctrl ×100)';
        plus.addEventListener('mousedown', function(ev) { ev.preventDefault(); });
        plus.addEventListener('click',     function(ev) { _spin(inp, 1, ev); });
        inp.parentNode.insertBefore(wrap, inp);
        wrap.appendChild(minus); wrap.appendChild(inp); wrap.appendChild(plus);
    });
}

// ── Live JSON preview ─────────────────────────────────────────
function updateConfigDisplay() {
    var cfg = {
        width: $('input[name="final_width"]').val(),
        height: $('input[name="final_height"]').val(),
        text_custom_style: $('input[name="text_enabled"][data-trigger="general"]').is(':checked'),
        text_font_size: $('input[name="text_font_size"]').val(),
        text_rotation: $('input[name="text_rotation"]').val(),
        text_locationx: $('input[name="text_location_x"]').val(),
        text_locationy: $('input[name="text_location_y"]').val(),
        text_font_color: $('input[name="text_font_color"]').val(),
        text_font: $('input[name="text_font_family"]').val(),
        text_line1: $('input[name="text_line_1"]').val(),
        text_line2: $('input[name="text_line_2"]').val(),
        text_line3: $('input[name="text_line_3"]').val(),
        text_linespace: $('input[name="text_line_space"]').val(),
        apply_frame: $('select[name="apply_frame"]').val(),
        frame: $('input[name="generator-frame"]').val(),
        background: $('input[name="generator-background"]').val(),
        background_color: $('input[name="background_color"]').val(),
        background_on_top: $('input[name="generator-background_on_top"][data-trigger="general"]').is(':checked'),
        placeholder: $('input[name="enable_placeholder_image"][data-trigger="general"]').is(':checked'),
        placeholderpath: $('input[name="placeholder_image"]').val(),
        placeholderposition: $('input[name="placeholder_image_position"]').val(),
        layout: []
    };
    $('div.image_layout:visible').each(function() {
        var row = [];
        $(this).find('input[data-prop]').each(function() {
            var v = $(this).val();
            if ($(this).attr('type') === 'checkbox') v = $(this).is(':checked') && cfg.apply_frame === 'always';
            row.push(v);
        });
        cfg.layout.push(row);
    });
    var box = document.getElementById('config-json-content');
    if (box) box.textContent = JSON.stringify(cfg, null, 2);
}
$(document).on('change keyup', 'input,select', function() { setTimeout(updateConfigDisplay, 100); });
$(function() { setTimeout(updateConfigDisplay, 500); });

function genCopyConfig() {
    var c = document.getElementById('config-json-content');
    if (!c) return;
    navigator.clipboard.writeText(c.innerText).then(function() {
        if (typeof openToast === 'function') openToast('JSON copied!', 'isSuccess', 2000);
    });
}

// ── Init ──────────────────────────────────────────────────────
$(function() {
    genGoTo(1);
    setTimeout(initSpinners, 450);
    setTimeout(function() { if (window._genFit) window._genFit(); }, 800);
});
</script>
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
