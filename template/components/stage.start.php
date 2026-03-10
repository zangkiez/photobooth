<?php

use Photobooth\Utility\PathUtility;
use Photobooth\Service\LanguageService;

$languageService = LanguageService::getInstance();
?>
<!-- Start Page - Magazine/Street Style -->
<div class="stage stage--start stage--magazine rotarygroup" data-stage="start">

    <!-- Grid Overlay -->
    <div class="grid-overlay"></div>

    <!-- Corner Decorations -->
    <div class="corner-decoration corner-decoration--tl"></div>
    <div class="corner-decoration corner-decoration--tr"></div>
    <div class="corner-decoration corner-decoration--bl"></div>
    <div class="corner-decoration corner-decoration--br"></div>

    <!-- Top Bar -->
    <div class="magazine-header">
        <div class="magazine-header__issue">ISSUE 01</div>
        <div class="magazine-header__year"><?php echo date('Y'); ?></div>
    </div>

    <!-- Side Labels -->
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

        <!-- MAGAZINE TITLE SECTION - Always Show -->
        <div class="names names--magazine names--decoration">
            <div class="names-inner names-inner--magazine">

                <!-- Category Tag -->
                <div class="magazine-tag">
                    <span class="sticker sticker--lime">PHOTOBOOTH</span>
                </div>

                <?php if ($config['event']['enabled']): ?>
                    <!-- Event Mode -->
                    <h1 class="event-text event-text--magazine">
                        <?= $config['event']['textLeft'] ?>
                        <span class="event-text__icon">
                            <i class="fa <?= $config['event']['symbol'] ?>" aria-hidden="true"></i>
                        </span>
                        <?= $config['event']['textRight'] ?>
                    </h1>
                <?php else: ?>
                    <!-- Default Title -->
                    <h1 class="start-text start-text--magazine start-text--main">
                        <?= $config['start_screen']['title_visible'] ? $config['start_screen']['title'] : 'SNAP\nSHOT' ?>
                    </h1>
                <?php endif; ?>

                <!-- Divider -->
                <div class="magazine-divider">
                    <span class="magazine-divider__line"></span>
                    <span class="magazine-divider__text">
                        <?= $config['start_screen']['subtitle_visible'] ? $config['start_screen']['subtitle'] : 'CREATE MEMORIES' ?>
                    </span>
                    <span class="magazine-divider__line"></span>
                </div>

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

    <!-- Screensaver - Subtle Indicator Style -->
    <div id="screensaver-overlay" class="screensaver-overlay screensaver-overlay--subtle" data-mode="none">
        <div class="screensaver-indicator">
            <span class="screensaver-indicator__dot"></span>
            <span class="screensaver-indicator__text">IDLE</span>
        </div>
    </div>

</div>
