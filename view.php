<?php

use Photobooth\Enum\FolderEnum;
use Photobooth\Service\ApplicationService;
use Photobooth\Service\DatabaseManagerService;
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

// Session = รูปที่ถ่ายในรอบนั้นๆ (ช่วงเวลาตามเวลาของรูปปัจจุบัน)
$SESSION_WINDOW_MINUTES = 15;

/**
 * Get Unix timestamp for an image for session grouping.
 * Uses filename date (Ymd_His) when naming is dateformatted, else file mtime.
 */
$getImageTime = function (string $imageName) use ($config): ?int {
    $base = basename($imageName);
    $nameWithoutExt = pathinfo($base, PATHINFO_FILENAME);
    if (!empty($config['picture']['naming']) && $config['picture']['naming'] === 'dateformatted') {
        $dt = \DateTime::createFromFormat('Ymd_His', $nameWithoutExt);
        return $dt ? $dt->getTimestamp() : null;
    }
    $path = FolderEnum::IMAGES->absolute() . DIRECTORY_SEPARATOR . $imageName;
    return is_file($path) ? filemtime($path) : null;
};

// Get all images for ordering (same as gallery)
$database = DatabaseManagerService::getInstance();
if (!empty($config['database']['enabled'])) {
    $allImages = $database->getContentFromDB();
} else {
    $allImages = $database->getFilesFromDirectory();
}
if (!empty($config['gallery']['newest_first']) && !empty($allImages)) {
    $allImages = array_reverse($allImages);
}
$allImages = array_values($allImages);

$currentTime = $getImageTime($image);
$windowSeconds = $SESSION_WINDOW_MINUTES * 60;

// Only images in the same time window as the current image (รอบนั้นๆ)
$sessionImages = array_values(array_filter($allImages, function ($img) use ($getImageTime, $currentTime, $windowSeconds) {
    $t = $getImageTime($img);
    return $t !== null && $currentTime !== null && abs($t - $currentTime) <= $windowSeconds;
}));

// If current image not in session (e.g. no timestamp), fallback to single-image "session"
if (!in_array($image, $sessionImages, true)) {
    $sessionImages = [$image];
}

$currentIndex = array_search($image, $sessionImages, true);
$currentIndex = $currentIndex === false ? 0 : (int) $currentIndex;

// Previous/next within session only
$prevImage = $currentIndex > 0 ? $sessionImages[$currentIndex - 1] : null;
$nextImage = $currentIndex < count($sessionImages) - 1 ? $sessionImages[$currentIndex + 1] : null;

$extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
$isVideo = in_array($extension, ['mp4', 'mov', 'webm'], true);
$mime = match ($extension) {
    'png' => 'image/png',
    'gif' => 'image/gif',
    default => 'image/jpeg',
};
$baseUrl = (!empty($config['webserver']['url']) && PathUtility::isFullUrl($config['webserver']['url']))
    ? $config['webserver']['url']
    : null;
if ($baseUrl !== null) {
    $imageUrl = PathUtility::getPublicPath(FolderEnum::IMAGES->value . '/' . rawurlencode($image), true, $baseUrl);
    $downloadUrl = PathUtility::getPublicPath('api/download.php?image=' . rawurlencode($image), true, $baseUrl);
} else {
    $imageUrl = PathUtility::getPublicPath(FolderEnum::IMAGES->value . '/' . rawurlencode($image));
    $downloadUrl = PathUtility::getPublicPath('api/download.php?image=' . rawurlencode($image));
}
$languageService = LanguageService::getInstance();
$pageTitle = ApplicationService::getInstance()->getTitle() . ' - ' . $languageService->translate('viewer_photo_title');
$photoswipe = false;
$remoteBuzzer = false;

include PathUtility::getAbsolutePath('template/components/main.head.php');
?>
<style>
    /* ========================================
       MAGAZINE VIEWER - FUN EDITION
       ธีมญี่ปุ่น-เกาหลี Street/Pop Art
       ======================================== */
    
    /* Viewer Page Layout */
    .magazine-viewer {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        position: relative;
        overflow: hidden;
        touch-action: pan-y;
        user-select: none;
    }
    
    /* Swipe Hints */
    .swipe-hint {
        position: fixed;
        top: 50%;
        transform: translateY(-50%);
        font-size: 3rem;
        color: var(--mag-black, #0a0a0a);
        opacity: 0.3;
        z-index: 25;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }
    
    .swipe-hint--left {
        left: 20px;
    }
    
    .swipe-hint--right {
        right: 20px;
    }
    
    .swipe-hint.visible {
        opacity: 0.8;
    }
    
    /* Corner Decorations - มุมสีสันสดใส */
    .magazine-viewer::before,
    .magazine-viewer::after {
        content: '';
        position: fixed;
        width: 150px;
        height: 150px;
        z-index: 10;
        pointer-events: none;
    }
    
    /* มุมบนซ้าย - ชมพู */
    .magazine-viewer::before {
        top: 0;
        left: 0;
        background: linear-gradient(135deg, var(--fun-hot-pink, #FF006E) 50%, transparent 50%);
    }
    
    /* มุมบนขวา - ฟ้า */
    .corner-top-right {
        position: fixed;
        top: 0;
        right: 0;
        width: 150px;
        height: 150px;
        background: linear-gradient(-135deg, var(--fun-electric-blue, #00F5FF) 50%, transparent 50%);
        z-index: 10;
        pointer-events: none;
    }
    
    /* มุมล่างซ้าย - เขียว */
    .corner-bottom-left {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 150px;
        height: 150px;
        background: linear-gradient(45deg, var(--fun-acid-green, #39FF14) 50%, transparent 50%);
        z-index: 10;
        pointer-events: none;
    }
    
    /* มุมล่างขวา - เหลือง */
    .corner-bottom-right {
        position: fixed;
        bottom: 0;
        right: 0;
        width: 150px;
        height: 150px;
        background: linear-gradient(-45deg, var(--fun-sunshine, #FFD700) 50%, transparent 50%);
        z-index: 10;
        pointer-events: none;
    }
    
    /* Side Labels - ป้ายซ้ายขวาแบบ Magazine */
    .side-label {
        position: fixed;
        font-family: 'Bungee', 'Kanit', sans-serif;
        font-weight: 700;
        font-size: 2rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        padding: 1rem;
        background: var(--mag-black, #0a0a0a);
        color: var(--mag-white, #ffffff);
        border: 4px solid var(--mag-black, #0a0a0a);
        box-shadow: 4px 4px 0 var(--fun-hot-pink, #FF006E);
        z-index: 20;
        pointer-events: none;
    }
    
    .side-label--left {
        left: 0;
        top: 50%;
        transform: translateY(-50%) rotate(-90deg);
        transform-origin: left center;
    }
    
    .side-label--right {
        right: 0;
        top: 50%;
        transform: translateY(-50%) rotate(90deg);
        transform-origin: right center;
    }
    
    /* Photo Counter */
    .photo-counter {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        font-family: 'Fredoka', 'Kanit', sans-serif;
        font-weight: 700;
        font-size: 1rem;
        background: var(--mag-black, #0a0a0a);
        color: var(--mag-white, #ffffff);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        z-index: 30;
        border: 3px solid var(--mag-black, #0a0a0a);
        box-shadow: 3px 3px 0 var(--fun-hot-pink, #FF006E);
    }
    
    /* Viewer Card */
    .viewer-card {
        position: relative;
        z-index: 5;
        width: 100%;
        max-width: 550px;
        background: var(--mag-white, #ffffff);
        border: var(--border-thick, 4px solid var(--mag-black, #0a0a0a));
        box-shadow: var(--shadow-hard, 6px 6px 0 var(--mag-black, #0a0a0a));
        border-radius: 16px;
        padding: 2.5rem;
        text-align: center;
    }
    
    /* Fun Sticker - สติกเกอร์ตกแต่ง */
    .fun-sticker {
        position: absolute;
        top: -20px;
        right: -20px;
        display: inline-block;
        padding: 0.75rem 1.25rem;
        font-family: 'Fredoka', 'Kanit', sans-serif;
        font-weight: 700;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background: var(--fun-hot-pink, #FF006E);
        color: var(--mag-white, #ffffff);
        border: 4px solid var(--mag-black, #0a0a0a);
        box-shadow: 4px 4px 0 var(--mag-black, #0a0a0a);
        transform: rotate(12deg);
        border-radius: 8px;
        z-index: 15;
    }
    
    /* Viewer Title */
    .viewer-title {
        font-family: 'Fredoka', 'Kanit', sans-serif;
        font-size: 2rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--mag-white, #ffffff);
        background: var(--fun-hot-pink, #FF006E);
        display: inline-block;
        padding: 0.75rem 1.5rem;
        border: 4px solid var(--mag-black, #0a0a0a);
        box-shadow: 4px 4px 0 var(--mag-black, #0a0a0a);
        transform: rotate(-2deg);
        border-radius: 12px;
        margin-bottom: 1.5rem;
        letter-spacing: 0.02em;
    }
    
    /* Event text styling */
    .viewer-title span {
        display: inline-block;
    }
    
    .viewer-title .fa {
        margin: 0 0.5rem;
    }
    
    /* Frame Pop - กรอบรูปแบบ Pop */
    .frame-pop {
        border: 4px solid var(--mag-black, #0a0a0a);
        border-radius: 12px;
        overflow: hidden;
        background: var(--mag-black, #0a0a0a);
        box-shadow: 6px 6px 0 var(--fun-electric-blue, #00F5FF);
        margin-bottom: 2rem;
        transform: rotate(1deg);
        transition: transform 0.3s ease;
        position: relative;
        touch-action: pan-y;
    }
    
    .frame-pop:hover {
        transform: rotate(0deg) scale(1.02);
    }
    
    .frame-pop img,
    .frame-pop video {
        width: 100%;
        height: auto;
        display: block;
        pointer-events: none;
    }
    
    /* Swipe Animation Classes */
    .frame-pop.swipe-left {
        animation: swipeLeft 0.3s ease-out;
    }
    
    .frame-pop.swipe-right {
        animation: swipeRight 0.3s ease-out;
    }
    
    @keyframes swipeLeft {
        0% { transform: translateX(0) rotate(1deg); opacity: 1; }
        100% { transform: translateX(-100%) rotate(1deg); opacity: 0; }
    }
    
    @keyframes swipeRight {
        0% { transform: translateX(0) rotate(1deg); opacity: 1; }
        100% { transform: translateX(100%) rotate(1deg); opacity: 0; }
    }
    
    /* Button Container */
    .viewer-actions {
        display: flex;
        justify-content: center;
        gap: 1rem;
        flex-wrap: wrap;
    }
    
    /* Fun Button - ใช้ class จากธีม */
    .viewer-actions .button-fun {
        font-family: 'Fredoka', 'Kanit', sans-serif;
        font-weight: 700;
        font-size: 1.25rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 1.25rem 2.5rem;
        background: var(--fun-hot-pink, #FF006E);
        color: var(--mag-white, #ffffff);
        border: 4px solid var(--mag-black, #0a0a0a);
        box-shadow: 6px 6px 0 var(--mag-black, #0a0a0a);
        cursor: pointer;
        transition: all 0.15s ease;
        position: relative;
        overflow: hidden;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        border-radius: 50px;
    }
    
    /* Save Button - สีฟ้า */
    .viewer-actions .button-fun.button-save {
        background: var(--fun-electric-blue, #00F5FF);
        color: var(--mag-black, #0a0a0a);
        box-shadow: 6px 6px 0 var(--mag-black, #0a0a0a);
    }
    
    .viewer-actions .button-fun:hover {
        transform: translate(-4px, -4px);
        box-shadow: 10px 10px 0 var(--mag-black, #0a0a0a);
    }
    
    .viewer-actions .button-fun:active {
        transform: translate(2px, 2px);
        box-shadow: 2px 2px 0 var(--mag-black, #0a0a0a);
    }
    
    /* Wiggle Animation */
    .viewer-actions .button-fun:hover {
        animation: buttonWiggle 0.5s ease-in-out;
    }
    
    @keyframes buttonWiggle {
        0%, 100% { transform: translate(-4px, -4px) rotate(0deg); }
        25% { transform: translate(-4px, -4px) rotate(-2deg); }
        75% { transform: translate(-4px, -4px) rotate(2deg); }
    }
    
    /* Toast Notification */
    .toast {
        position: fixed;
        bottom: 100px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: var(--mag-black, #0a0a0a);
        color: var(--mag-white, #ffffff);
        padding: 1rem 2rem;
        border-radius: 50px;
        font-family: 'Fredoka', 'Kanit', sans-serif;
        font-weight: 700;
        font-size: 1rem;
        border: 4px solid var(--mag-black, #0a0a0a);
        box-shadow: 4px 4px 0 var(--fun-hot-pink, #FF006E);
        z-index: 100;
        opacity: 0;
        transition: all 0.3s ease;
        pointer-events: none;
    }
    
    .toast.show {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .magazine-viewer {
            padding: 1rem;
            padding-bottom: 80px;
        }
        
        .viewer-card {
            padding: 1.5rem;
            max-width: 95%;
        }
        
        .viewer-title {
            font-size: 1.5rem;
            padding: 0.5rem 1rem;
        }
        
        .side-label {
            display: none;
        }
        
        .corner-top-right,
        .corner-bottom-left,
        .corner-bottom-right,
        .magazine-viewer::before {
            width: 60px;
            height: 60px;
        }
        
        .swipe-hint {
            font-size: 2rem;
        }
        
        .fun-sticker {
            top: -15px;
            right: -10px;
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
        }
        
        .viewer-actions {
            flex-direction: column;
            width: 100%;
        }
        
        .viewer-actions .button-fun {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            width: 100%;
            justify-content: center;
        }
        
        .photo-counter {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
        }
    }
</style>

<body class="viewer-page">
    <div class="magazine-viewer bg-halftone" id="viewer-container">
        <!-- Photo Counter -->
        <div class="photo-counter">
            <?= ($currentIndex + 1) ?> / <?= count($sessionImages) ?>
        </div>
        
        <!-- Swipe Hints -->
        <?php if ($prevImage): ?>
        <div class="swipe-hint swipe-hint--left" id="hint-left">
            <i class="fa fa-chevron-left"></i>
        </div>
        <?php endif; ?>
        <?php if ($nextImage): ?>
        <div class="swipe-hint swipe-hint--right" id="hint-right">
            <i class="fa fa-chevron-right"></i>
        </div>
        <?php endif; ?>
        
        <!-- Corner Decorations -->
        <div class="corner-top-right"></div>
        <div class="corner-bottom-left"></div>
        <div class="corner-bottom-right"></div>
        
        <!-- Side Labels -->
        <div class="side-label side-label--left">PHOTO</div>
        <div class="side-label side-label--right">BOOTH</div>
        
        <!-- Main Viewer Card -->
        <div class="viewer-card">
            <!-- Fun Sticker -->
            <span class="fun-sticker">SWIPE!</span>
            
            <!-- Title -->
            <div class="viewer-title">
                <?php if ($config['event']['enabled']): ?>
                    <span><?= htmlspecialchars($config['event']['textLeft']) ?></span>
                    <?php if (!empty($config['event']['symbol'])): ?>
                        <i class="fa <?= htmlspecialchars($config['event']['symbol']) ?>" aria-hidden="true"></i>
                    <?php endif; ?>
                    <span><?= htmlspecialchars($config['event']['textRight']) ?></span>
                <?php else: ?>
                    <span><?= htmlspecialchars(ApplicationService::getInstance()->getTitle()) ?></span>
                <?php endif; ?>
            </div>
            
            <!-- Photo Frame -->
            <div class="frame-pop" id="photo-frame" aria-label="Captured media preview"
                 data-prev="<?= $prevImage ? htmlspecialchars($prevImage) : '' ?>"
                 data-next="<?= $nextImage ? htmlspecialchars($nextImage) : '' ?>">
                <?php if ($isVideo): ?>
                    <video src="<?=$imageUrl?>" controls playsinline controlsList="nodownload" id="viewer-media">
                        <?=htmlspecialchars($languageService->translate('viewer_video_fallback'))?>
                    </video>
                <?php else: ?>
                    <img src="<?=$imageUrl?>" alt="Captured photo" id="viewer-media">
                <?php endif; ?>
            </div>
            
            <!-- Action Buttons -->
            <div class="viewer-actions">
                <button type="button" class="button-fun button-save" id="save-btn" onclick="saveToGallery()">
                    <i class="fa fa-heart"></i>
                    <span>บันทึกเข้าอัลบัม</span>
                </button>
                <a href="<?=$downloadUrl?>" class="button-fun" download="<?= htmlspecialchars($image) ?>">
                    <i class="<?=$config['icons']['download']?>"></i>
                    <span>ดาวน์โหลด</span>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Toast Notification -->
    <div class="toast" id="toast"></div>

    <?php include PathUtility::getAbsolutePath('template/components/main.footer.php'); ?>
    
    <script>
        // Swipe functionality
        let startX = 0;
        let startY = 0;
        let isSwiping = false;
        
        const frame = document.getElementById('photo-frame');
        const container = document.getElementById('viewer-container');
        const hintLeft = document.getElementById('hint-left');
        const hintRight = document.getElementById('hint-right');
        
        const prevImage = frame.dataset.prev;
        const nextImage = frame.dataset.next;
        
        // Touch events
        frame.addEventListener('touchstart', handleTouchStart, { passive: true });
        frame.addEventListener('touchmove', handleTouchMove, { passive: true });
        frame.addEventListener('touchend', handleTouchEnd, { passive: true });
        
        // Mouse events (for desktop testing)
        frame.addEventListener('mousedown', handleMouseDown);
        document.addEventListener('mousemove', handleMouseMove);
        document.addEventListener('mouseup', handleMouseUp);
        
        function handleTouchStart(e) {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
            isSwiping = true;
            
            // Show hints
            if (hintLeft) hintLeft.classList.add('visible');
            if (hintRight) hintRight.classList.add('visible');
        }
        
        function handleMouseDown(e) {
            startX = e.clientX;
            startY = e.clientY;
            isSwiping = true;
            
            if (hintLeft) hintLeft.classList.add('visible');
            if (hintRight) hintRight.classList.add('visible');
        }
        
        function handleTouchMove(e) {
            if (!isSwiping) return;
            
            const currentX = e.touches[0].clientX;
            const diffX = startX - currentX;
            
            // Visual feedback during swipe
            if (Math.abs(diffX) > 50) {
                frame.style.transform = `translateX(${-diffX * 0.3}px) rotate(1deg)`;
            }
        }
        
        function handleMouseMove(e) {
            if (!isSwiping) return;
            
            const currentX = e.clientX;
            const diffX = startX - currentX;
            
            if (Math.abs(diffX) > 50) {
                frame.style.transform = `translateX(${-diffX * 0.3}px) rotate(1deg)`;
            }
        }
        
        function handleTouchEnd(e) {
            if (!isSwiping) return;
            isSwiping = false;
            
            const endX = e.changedTouches[0].clientX;
            const diffX = startX - endX;
            const diffY = Math.abs(startY - e.changedTouches[0].clientY);
            
            frame.style.transform = '';
            
            // Hide hints
            if (hintLeft) hintLeft.classList.remove('visible');
            if (hintRight) hintRight.classList.remove('visible');
            
            // Check if horizontal swipe (not vertical scroll)
            if (Math.abs(diffX) > 80 && diffY < 100) {
                if (diffX > 0 && nextImage) {
                    // Swipe left -> next
                    navigateToImage(nextImage, 'left');
                } else if (diffX < 0 && prevImage) {
                    // Swipe right -> prev
                    navigateToImage(prevImage, 'right');
                }
            }
        }
        
        function handleMouseUp(e) {
            if (!isSwiping) return;
            isSwiping = false;
            
            const endX = e.clientX;
            const diffX = startX - endX;
            
            frame.style.transform = '';
            
            if (hintLeft) hintLeft.classList.remove('visible');
            if (hintRight) hintRight.classList.remove('visible');
            
            if (Math.abs(diffX) > 80) {
                if (diffX > 0 && nextImage) {
                    navigateToImage(nextImage, 'left');
                } else if (diffX < 0 && prevImage) {
                    navigateToImage(prevImage, 'right');
                }
            }
        }
        
        function navigateToImage(imageName, direction) {
            frame.classList.add(direction === 'left' ? 'swipe-left' : 'swipe-right');
            
            setTimeout(() => {
                window.location.href = 'view.php?image=' + encodeURIComponent(imageName);
            }, 300);
        }
        
        // Save to Gallery functionality
        async function saveToGallery() {
            const toast = document.getElementById('toast');
            const saveBtn = document.getElementById('save-btn');
            
            // Show loading state
            saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i><span>กำลังบันทึก...</span>';
            
            try {
                const imageUrl = document.getElementById('viewer-media').src;
                const response = await fetch(imageUrl);
                const blob = await response.blob();
                const file = new File([blob], '<?= htmlspecialchars($image) ?>', { type: blob.type });
                
                // Try Web Share API first (best for mobile)
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    await navigator.share({
                        files: [file],
                        title: 'Photo from Photobooth',
                        text: 'บันทึกรูปภาพ'
                    });
                    showToast('✨ บันทึกเรียบร้อย!');
                } else {
                    // Fallback: Create download link
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = '<?= htmlspecialchars($image) ?>';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                    showToast('✨ ดาวน์โหลดเรียบร้อย!');
                }
            } catch (error) {
                console.error('Save error:', error);
                // If share was canceled, don't show error
                if (error.name !== 'AbortError') {
                    showToast('❌ ไม่สามารถบันทึกได้');
                }
            } finally {
                // Reset button
                saveBtn.innerHTML = '<i class="fa fa-heart"></i><span>บันทึกเข้าอัลบัม</span>';
            }
        }
        
        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.classList.add('show');
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft' && prevImage) {
                navigateToImage(prevImage, 'right');
            } else if (e.key === 'ArrowRight' && nextImage) {
                navigateToImage(nextImage, 'left');
            }
        });
    </script>
</body>
</html>