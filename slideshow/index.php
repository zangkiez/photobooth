<?php

require_once '../lib/boot.php';

use Photobooth\Service\ApplicationService;
use Photobooth\Service\AssetService;
use Photobooth\Service\LanguageService;
use Photobooth\Utility\PathUtility;

$languageService = LanguageService::getInstance();
$assetService = AssetService::getInstance();
$pageTitle = 'Slideshow - ' . ApplicationService::getInstance()->getTitle();
$photoswipe = true;
$randomImage = $config['slideshow']['randomPicture'];
$remoteBuzzer = false;

/** Use same gallery template as main app; title = "Slideshow" */
$galleryTitle = 'slideshow';

include PathUtility::getAbsolutePath('template/components/main.head.php');

?>
<body class="gallery-mode--overlay">
    <?php include PathUtility::getAbsolutePath('template/components/gallery.php'); ?>
    <script>
        document.getElementById('gallery').classList.add('gallery--open');
    </script>
    <?php include PathUtility::getAbsolutePath('template/components/main.footer.php'); ?>
    <script src="<?=$assetService->getUrl('resources/js/slideshow.js')?>"></script>
</body>
</html>
