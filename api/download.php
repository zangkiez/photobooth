<?php

/** @var array $config */

use Photobooth\Enum\FolderEnum;
use Photobooth\Enum\ImageFilterEnum;
use Photobooth\Image;
use Photobooth\Utility\ImageUtility;

require_once '../lib/boot.php';

$image = (isset($_GET['image']) && $_GET['image']) != '' ? $_GET['image'] : false;
if ($image) {
    $image = explode('?', $image)[0];
    $basename = basename($image);
    if ($basename === '' || !preg_match('/^[A-Za-z0-9._-]+$/', $basename)) {
        http_response_code(400);
        echo 'Invalid image name.';
        exit();
    }

    $path = FolderEnum::IMAGES->absolute() . DIRECTORY_SEPARATOR . $basename;

    if (!is_file($path)) {
        http_response_code(404);
        echo $image . ' does not exist.';
        exit();
    }

    $filterParam = isset($_GET['filter']) && $_GET['filter'] !== '' ? trim((string) $_GET['filter']) : null;
    $applyFilter = null;
    if ($filterParam !== null && !empty($config['filters']['enabled'])) {
        $applyFilter = ImageFilterEnum::tryFrom($filterParam);
        if ($applyFilter === null && is_string($filterParam) && str_starts_with($filterParam, ImageUtility::CUBE_FILTER_PREFIX)) {
            $applyFilter = $filterParam;
        }
        if ($applyFilter !== null && $applyFilter !== ImageFilterEnum::PLAIN) {
            $applyFilter = $applyFilter instanceof ImageFilterEnum ? $applyFilter->value : $applyFilter;
        } else {
            $applyFilter = null;
        }
    }

    try {
        $pathinfo = pathinfo($path);

        if (!isset($pathinfo['extension'])) {
            throw new \Exception('Extension not found!');
        }
        $extension = strtolower($pathinfo['extension']);
        if ($config['download']['thumbs'] && $extension !== 'mp4' && $extension !== 'gif') {
            $thumb = FolderEnum::THUMBS->absolute() . DIRECTORY_SEPARATOR . $basename;
            if (is_file($thumb)) {
                $path = $thumb;
            }
        }

        if ($applyFilter !== null && in_array($extension, ['jpg', 'jpeg', 'png', 'gif'], true)) {
            $imageHandler = new Image();
            $imageHandler->jpegQuality = (int) ($config['jpeg_quality']['image'] ?? 90);
            $fullImagePath = FolderEnum::IMAGES->absolute() . DIRECTORY_SEPARATOR . $basename;
            $imageResource = $imageHandler->createFromImage(is_file($fullImagePath) ? $fullImagePath : $path);
            if ($imageResource instanceof \GdImage) {
                $filterValue = ImageFilterEnum::tryFrom($applyFilter) ?? $applyFilter;
                ImageUtility::applyFilter($filterValue, $imageResource);
                header('Content-Type: image/jpeg');
                $disposition = (isset($_GET['display']) && $_GET['display'] !== '') ? 'inline' : 'attachment';
                $filename = $disposition === 'attachment'
                    ? 'photobooth-' . pathinfo($basename, PATHINFO_FILENAME) . '-filtered.jpg'
                    : pathinfo($basename, PATHINFO_FILENAME) . '.jpg';
                header('Content-Disposition: ' . $disposition . '; filename="' . $filename . '"');
                imagejpeg($imageResource, null, $imageHandler->jpegQuality);
                imagedestroy($imageResource);
                exit();
            }
        }

        header('Content-Type: application/octet-stream');
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: attachment; filename="photobooth-' . $basename . '"');
        echo file_get_contents($path);
    } catch (\Exception $e) {
        http_response_code(500);
        echo 'Error downloading the file ' . $image;
        if ($config['dev']['loglevel'] > 1) {
            echo $e->getMessage();
        }
    }
} else {
    http_response_code(400);
    echo 'No image defined.';
}
exit();
