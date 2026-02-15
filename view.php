<?php

use Photobooth\Enum\FolderEnum;
use Photobooth\Service\ApplicationService;
use Photobooth\Service\LanguageService;
use Photobooth\Utility\ComponentUtility;
use Photobooth\Utility\PathUtility;

require_once __DIR__ . '/lib/boot.php';

$imageParam = $_GET['image'] ?? '';
$image = basename((string) $imageParam);

if ($image === '') {
    http_response_code(400);
    echo 'No image specified.';
    exit();
}

$imagePath = FolderEnum::IMAGES->absolute() . DIRECTORY_SEPARATOR . $image;
if (!is_file($imagePath)) {
    http_response_code(404);
    echo 'Image not found.';
    exit();
}

$extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
$isVideo = in_array($extension, ['mp4', 'mov', 'webm'], true);
$mime = match ($extension) {
    'png' => 'image/png',
    'gif' => 'image/gif',
    default => 'image/jpeg',
};
$imageUrl = PathUtility::getPublicPath(FolderEnum::IMAGES->value . '/' . rawurlencode($image));
$downloadUrl = PathUtility::getPublicPath('api/download.php?image=' . rawurlencode($image));
$languageService = LanguageService::getInstance();
$pageTitle = ApplicationService::getInstance()->getTitle() . ' - ' . $languageService->translate('viewer_photo_title');
$photoswipe = false;
$remoteBuzzer = false;

include PathUtility::getAbsolutePath('template/components/main.head.php');
?>
<style>
    /* Gen-Z / Fun Style - Green & White Theme */
    :root {
        --genz-bg: #f0fff4;
        --genz-green: #00ff88; /* Bright Neon Green */
        --genz-dark: #1a1a1a;
        --genz-border: 3px solid var(--genz-dark);
        --genz-shadow: 6px 6px 0px var(--genz-dark);
    }

    body.viewer-page {
        background-color: var(--genz-bg);
        background-image: radial-gradient(var(--genz-green) 2px, transparent 2px);
        background-size: 30px 30px;
        font-family: 'Verdana', sans-serif;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        margin: 0;
        padding: 20px;
    }

    .viewer {
        width: 100%;
        max-width: 600px;
        background: #fff;
        border: var(--genz-border);
        box-shadow: var(--genz-shadow);
        border-radius: 20px;
        padding: 2rem;
        text-align: center;
        transition: transform 0.3s ease;
        position: relative;
    }

    .viewer:hover {
        transform: translateY(-5px);
    }

    .viewer__header {
        margin-bottom: 2rem;
    }

    .viewer__title {
        font-size: 1.8rem;
        font-weight: 900;
        text-transform: uppercase;
        color: var(--genz-dark);
        background: var(--genz-green);
        display: inline-block;
        padding: 0.5rem 1.5rem;
        border: var(--genz-border);
        box-shadow: 4px 4px 0px var(--genz-dark);
        transform: rotate(-2deg);
        border-radius: 10px;
    }

    .viewer__media {
        border: var(--genz-border);
        border-radius: 15px;
        overflow: hidden;
        margin-bottom: 2rem;
        background: #000;
        box-shadow: 4px 4px 0px rgba(0,0,0,0.1);
    }

    .viewer__media img, .viewer__media video {
        width: 100%;
        height: auto;
        display: block;
    }

    .viewer__actions {
        display: flex;
        justify-content: center;
        gap: 1rem;
    }

    /* Custom Button Style overriding framework defaults if necessary */
    .viewer__actions .button {
        background: var(--genz-green) !important;
        color: var(--genz-dark) !important;
        font-weight: bold !important;
        text-transform: uppercase;
        padding: 1rem 2rem !important;
        border: var(--genz-border) !important;
        border-radius: 50px !important;
        font-size: 1.2rem !important;
        cursor: pointer;
        box-shadow: 4px 4px 0px var(--genz-dark) !important;
        transition: all 0.2s !important;
        text-decoration: none !important;
        display: inline-flex !important;
        align-items: center;
        gap: 10px;
        height: auto !important;
        line-height: normal !important;
    }

    .viewer__actions .button:hover {
        transform: translate(2px, 2px);
        box-shadow: 2px 2px 0px var(--genz-dark) !important;
        background: #fff !important;
    }

    .viewer__actions .button i {
        font-size: 1.4rem;
    }

    /* Responsive */
    @media (max-width: 600px) {
        .viewer {
            padding: 1.5rem;
            width: 95%;
        }
        .viewer__title {
            font-size: 1.2rem;
            padding: 0.5rem 1rem;
        }
        .viewer__actions .button {
            width: 100%;
            justify-content: center;
            padding: 0.8rem 1rem !important;
            font-size: 1rem !important;
        }
    }
</style>

<body class="viewer-page">
    <main class="viewer">
        <div class="viewer__inner">
            <header class="viewer__header">
                <div class="viewer__title">
                    <?php if ($config['event']['enabled']): ?>
                        <span class="viewer__title-line"><?= htmlspecialchars($config['event']['textLeft']) ?></span>
                        <?php if (!empty($config['event']['symbol'])): ?>
                            <span class="viewer__title-line">
                                <i class="fa <?= htmlspecialchars($config['event']['symbol']) ?>" aria-hidden="true"></i>
                            </span>
                        <?php endif; ?>
                        <span class="viewer__title-line"><?= htmlspecialchars($config['event']['textRight']) ?></span>
                    <?php else: ?>
                        <span class="viewer__title-line"><?= htmlspecialchars(ApplicationService::getInstance()->getTitle()) ?></span>
                    <?php endif; ?>
                </div>
            </header>

            <div class="viewer__media" aria-label="Captured media preview">
                <?php if ($isVideo): ?>
                    <video src="<?=$imageUrl?>" controls playsinline controlsList="nodownload">
                        <?=htmlspecialchars($languageService->translate('viewer_video_fallback'))?>
                    </video>
                <?php else: ?>
                    <img id="viewer-image" src="<?=$imageUrl?>" alt="Captured photo">
                <?php endif; ?>
            </div>

            <div class="viewer__actions buttonbar">
                <a href="<?=$downloadUrl?>" class="button" download="download">
                    <span class="button--icon"><i class="<?=$config['icons']['download']?>"></i></span>
                    <span class="button--label"><?=htmlspecialchars($languageService->translate('download'))?></span>
                </a>
            </div>

        </div>
    </main>

    <?php include PathUtility::getAbsolutePath('template/components/main.footer.php'); ?>
</body>
</html>
