<?php

use Photobooth\Utility\PathUtility;
use Photobooth\Service\LanguageService;

$languageService = LanguageService::getInstance();
?>
<!-- Start Page - Magazine/Street Style (Fun Edition) -->
<div class="stage stage--start stage--magazine rotarygroup" data-stage="start">

    <!-- Fun Corner Decorations with Colors -->
    <div class="corner-decoration corner-decoration--tl"></div>
    <div class="corner-decoration corner-decoration--tr"></div>
    <div class="corner-decoration corner-decoration--bl"></div>
    <div class="corner-decoration corner-decoration--br"></div>

    <!-- Magazine Header -->
    <div class="magazine-header">
        <div class="magazine-header__issue">ISSUE 01</div>
        <div class="magazine-header__year"><?php echo date('Y'); ?></div>
    </div>

    <!-- Fun Side Labels -->
    <div class="magazine-side-label magazine-side-label--left">
        <span>PHOTO</span>
    </div>
    <div class="magazine-side-label magazine-side-label--right">
        <span>BOOTH</span>
    </div>

    <!-- Logo -->
    <?php include PathUtility::getAbsolutePath('template/components/start.logo.php'); ?>

    <!-- Main Content -->
    <div class="stage-inner stage-inner--magazine">

        <?php if ($config['event']['enabled'] || $config['start_screen']['title_visible']): ?>
            <div class="names names--magazine<?= ($config['ui']['decore_lines']) ? ' names--decoration' : '' ?>">
                <div class="names-inner names-inner--magazine">

                    <!-- Category Tag -->
                    <div class="magazine-tag">
                        <span class="sticker sticker--lime">EXCLUSIVE</span>
                    </div>

                    <?php if ($config['event']['enabled']): ?>
                        <!-- Main Event Title -->
                        <h1 class="event-text event-text--magazine">
                            <?= $config['event']['textLeft'] ?>
                            <span class="event-text__icon">
                                <i class="fa <?= $config['event']['symbol'] ?>" aria-hidden="true"></i>
                            </span>
                            <?= $config['event']['textRight'] ?>
                        </h1>

                        <?php if ($config['start_screen']['title_visible']): ?>
                            <div class="magazine-divider">
                                <span class="magazine-divider__line"></span>
                                <span class="magazine-divider__text">PRESENTS</span>
                                <span class="magazine-divider__line"></span>
                            </div>
                            <h2 class="start-text start-text--magazine"><?= $config['start_screen']['title'] ?></h2>
                        <?php endif; ?>

                        <?php if ($config['start_screen']['subtitle_visible']): ?>
                            <div class="magazine-subtitle-wrapper">
                                <span class="magazine-corner magazine-corner--tl"></span>
                                <span class="magazine-corner magazine-corner--tr"></span>
                                <span class="magazine-corner magazine-corner--bl"></span>
                                <span class="magazine-corner magazine-corner--br"></span>
                                <h3 class="start-text start-text--subtitle"><?= $config['start_screen']['subtitle'] ?></h3>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- No Event Mode - Show Title Only -->
                        <div class="magazine-tag">
                            <span class="sticker">WELCOME</span>
                        </div>

                        <?php if ($config['start_screen']['title_visible']): ?>
                            <h1 class="start-text start-text--magazine start-text--main"><?= $config['start_screen']['title'] ?></h1>
                        <?php endif; ?>

                        <?php if ($config['start_screen']['subtitle_visible']): ?>
                            <div class="magazine-divider">
                                <span class="magazine-divider__line"></span>
                                <span class="magazine-divider__text">GET READY</span>
                                <span class="magazine-divider__line"></span>
                            </div>
                            <h2 class="start-text start-text--subtitle"><?= $config['start_screen']['subtitle'] ?></h2>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Visual = TAP TO START (กดแล้วเริ่มคอลลาจ) -->
                    <?php if (!empty($config['collage']['enabled'])): ?>
                        <div class="magazine-visual takeCollage rotaryfocus" role="button" tabindex="0" data-command="takeCollage">
                            <div class="magazine-visual__box">
                                <span class="magazine-visual__icon">📸</span>
                            </div>
                            <div class="magazine-visual__text">
                                <span class="text-caption">TAP TO START</span>
                            </div>
                        </div>
                    <?php elseif (!empty($config['picture']['enabled'])): ?>
                        <div class="magazine-visual takePic rotaryfocus" role="button" tabindex="0" data-command="takePic">
                            <div class="magazine-visual__box">
                                <span class="magazine-visual__icon">📸</span>
                            </div>
                            <div class="magazine-visual__text">
                                <span class="text-caption">TAP TO START</span>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <?php
        if ($config['ui']['selfie_mode']) {
            include PathUtility::getAbsolutePath('template/components/selfieAction.php');
        } else {
            include PathUtility::getAbsolutePath('template/components/actionBtn.php');
            if ($config['collage']['enabled'] && $config['collage']['allow_selection']) {
                include PathUtility::getAbsolutePath('template/components/collageSelection.php');
            }
        }
        ?>

    </div>

    <!-- Marquee Bar -->
    <div class="marquee-bar">
        <div class="marquee-bar__content">
            ★ SNAP YOUR BEST SHOT ★ CREATE MEMORIES ★ SHARE THE FUN ★ SNAP YOUR BEST SHOT ★ CREATE MEMORIES ★ SHARE THE FUN ★
        </div>
    </div>

    <!-- Screensaver -->
    <?php
    $screensaverMode = $config['screensaver']['mode'] ?? 'image';
    $screensaverImageSource = $config['screensaver']['image_source'] ?? '';
    $screensaverVideoSource = $config['screensaver']['video_source'] ?? '';
    $screensaverSource = '';
    if ($screensaverMode === 'image' && $screensaverImageSource) {
        $screensaverSource = PathUtility::getPublicPath($screensaverImageSource);
    } elseif ($screensaverMode === 'video' && $screensaverVideoSource) {
        $screensaverSource = PathUtility::getPublicPath($screensaverVideoSource);
    }
    ?>
    <div
        id="screensaver-overlay"
        class="screensaver-overlay"
        data-mode="<?= $screensaverMode ?>"
        data-source="<?= $screensaverSource ?>"
        style="display: none;">
        <div id="screensaver-text-top" class="screensaver-overlay__text screensaver-overlay__text--top"></div>
        <div id="screensaver-text-center" class="screensaver-overlay__text screensaver-overlay__text--center"></div>
        <img id="screensaver-image" class="screensaver-overlay__image" alt="screensaver">
        <video id="screensaver-video" loop muted playsinline></video>
        <div id="screensaver-text-bottom" class="screensaver-overlay__text screensaver-overlay__text--bottom"></div>
    </div>

    <!-- GitHub Corner -->
    <?php include PathUtility::getAbsolutePath('template/components/github-corner.php'); ?>

</div>
