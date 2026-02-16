<?php

namespace Photobooth\Utility;

use Exception;
use GdImage;
use Photobooth\Enum\ImageFilterEnum;

class ImageUtility
{
    /** @var string[] Paths to scan for .cube LUT files (relative to project root) */
    public const CUBE_LUT_PATHS = [
        'resources/lut',
        'private/lut',
    ];

    public const CUBE_FILTER_PREFIX = 'cube:';
    public const supportedFileExtensionsProcessing = [
        'gif',
        'png',
        'jpeg',
        'jpg',
    ];

    public const supportedMimeTypesSelect = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/svg+xml',
        'image/bmp',
        'image/webp',
    ];

    public const supportedFileExtensionsSelect = [
        'gif',
        'png',
        'jpeg',
        'jpg',
        'svg',
        'bmp',
        'webp',
    ];

    public const resourcePaths = [
        'resources/img/background',
        'resources/img/frames',
        'resources/img/demo',
        'data/images',
    ];

    /**
     * @throws Exception
     */
    public static function getImagesFromPath(string $path, bool $processing = true): array
    {
        $allowedExtensions = $processing ? self::supportedFileExtensionsProcessing : self::supportedFileExtensionsSelect;

        return FileUtility::getFilesFromPath($path, $allowedExtensions);
    }

    public static function getRandomImageFromPath(string $path): string
    {
        if ($path === '' || $path === 'demoframes') {
            $path = 'resources/img/frames';
        }

        if (!in_array($path, self::resourcePaths)) {
            $path = 'private/' . $path;
        }

        $files = self::getImagesFromPath($path);
        if (count($files) === 0) {
            throw new Exception('Path ' . $path . ' does not contain images.');
        }

        return $files[array_rand($files)];
    }

    public static function getDemoImages(int $filecount = 0): array
    {
        $primaryFolder = 'private/images/demo';
        $secondaryFolder = 'data/tmp';
        $tertiaryFolder = 'resources/img/demo';
        $demoImages = [];

        $demoImages = self::getImagesFromPath($primaryFolder);

        if (empty($demoImages)) {
            $demoImages = self::getImagesFromPath($secondaryFolder);
        }

        if (empty($demoImages)) {
            $demoImages = self::getImagesFromPath($tertiaryFolder);
        }

        if (empty($demoImages)) {
            throw new Exception('No images found in any of the demo folders.');
        }

        if ($filecount > 0) {
            $demoCounted = [];
            for ($i = 0; $i < $filecount; $i++) {
                if (empty($demoImages)) {
                    $demoImages = $demoCounted;
                }

                $randomIndex = array_rand($demoImages);
                $demoCounted[] = $demoImages[$randomIndex];
                unset($demoImages[$randomIndex]);
                $demoImages = array_values($demoImages);
            }

            return $demoCounted;
        } else {

            return $demoImages;
        }
    }

    /**
     * Returns list of available .cube (Lightroom-style) filters for the UI.
     * Each item: ['value' => 'cube:Basename', 'label' => 'Human label'].
     *
     * @return list<array{value: string, label: string}>
     */
    public static function getCubeFilters(): array
    {
        $list = [];
        $seen = [];
        foreach (self::CUBE_LUT_PATHS as $relPath) {
            $path = PathUtility::getAbsolutePath($relPath);
            if (!is_dir($path)) {
                continue;
            }
            try {
                $files = FileUtility::getFilesFromPath($path, ['cube']);
                foreach ($files as $absPath) {
                    $base = pathinfo($absPath, PATHINFO_FILENAME);
                    if (isset($seen[$base])) {
                        continue;
                    }
                    $seen[$base] = true;
                    $list[] = [
                        'value' => self::CUBE_FILTER_PREFIX . $base,
                        'label' => self::cubeNameToLabel($base),
                    ];
                }
            } catch (\Throwable $e) {
                // skip invalid paths
            }
        }
        usort($list, static fn ($a, $b) => strcasecmp($a['label'], $b['label']));
        return $list;
    }

    private static function cubeNameToLabel(string $base): string
    {
        return trim(preg_replace('/[-_]+/', ' ', $base));
    }

    /**
     * Resolve cube filter value (e.g. "cube:BlueHour") to absolute .cube file path.
     *
     * @throws Exception if not found
     */
    public static function resolveCubePath(string $cubeFilterValue): string
    {
        if (str_starts_with($cubeFilterValue, self::CUBE_FILTER_PREFIX)) {
            $cubeFilterValue = substr($cubeFilterValue, strlen(self::CUBE_FILTER_PREFIX));
        }
        $base = preg_replace('/[^a-zA-Z0-9._-]/', '', $cubeFilterValue);
        if ($base === '') {
            throw new Exception('Invalid cube filter name.');
        }
        foreach (self::CUBE_LUT_PATHS as $relPath) {
            $path = PathUtility::getAbsolutePath($relPath);
            $file = $path . DIRECTORY_SEPARATOR . $base . '.cube';
            if (is_file($file)) {
                return $file;
            }
        }
        throw new Exception('Cube file not found: ' . $base . '.cube');
    }

    /**
     * Apply a 3D LUT from a .cube file (e.g. from Lightroom) to the image.
     *
     * @throws Exception
     */
    public static function applyCubeLut(string $cubeFilePath, GdImage $image): void
    {
        if (!is_readable($cubeFilePath)) {
            throw new Exception('Cube file not readable: ' . $cubeFilePath);
        }
        $lut = self::parseCubeFile($cubeFilePath);
        $width = imagesx($image);
        $height = imagesy($image);
        $size = $lut['size'];
        $data = $lut['data'];
        $n2 = $size * $size;
        $n1 = $size;
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $idx = imagecolorat($image, $x, $y);
                $r = ($idx >> 16) & 0xFF;
                $g = ($idx >> 8) & 0xFF;
                $b = $idx & 0xFF;
                $ri = (int) round($r / 255.0 * ($size - 1));
                $gi = (int) round($g / 255.0 * ($size - 1));
                $bi = (int) round($b / 255.0 * ($size - 1));
                $ri = max(0, min($size - 1, $ri));
                $gi = max(0, min($size - 1, $gi));
                $bi = max(0, min($size - 1, $bi));
                $pos = $ri * $n2 + $gi * $n1 + $bi;
                $out = $data[$pos];
                $newR = (int) round($out[0] * 255);
                $newG = (int) round($out[1] * 255);
                $newB = (int) round($out[2] * 255);
                $newR = max(0, min(255, $newR));
                $newG = max(0, min(255, $newG));
                $newB = max(0, min(255, $newB));
                imagesetpixel($image, $x, $y, ($newR << 16) | ($newG << 8) | $newB);
            }
        }
    }

    /**
     * Parse .cube file into size and 1D array of [R,G,B] (0-1) per LUT cell.
     *
     * @return array{size: int, data: list<array{0: float, 1: float, 2: float}>}
     * @throws Exception
     */
    private static function parseCubeFile(string $cubeFilePath): array
    {
        $content = file_get_contents($cubeFilePath);
        if ($content === false) {
            throw new Exception('Cannot read cube file.');
        }
        $size = null;
        $lines = preg_split('/\r\n|\r|\n/', $content);
        $data = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || str_starts_with($line, 'TITLE') || str_starts_with($line, 'DOMAIN')) {
                continue;
            }
            if (preg_match('/^LUT_3D_SIZE\s+(\d+)/', $line, $m)) {
                $size = (int) $m[1];
                if ($size < 2 || $size > 256) {
                    throw new Exception('Invalid LUT_3D_SIZE in cube file.');
                }
                continue;
            }
            $parts = preg_split('/\s+/', $line, 4);
            if (count($parts) >= 3 && is_numeric($parts[0]) && is_numeric($parts[1]) && is_numeric($parts[2])) {
                $data[] = [
                    (float) $parts[0],
                    (float) $parts[1],
                    (float) $parts[2],
                ];
            }
        }
        if ($size === null || count($data) !== $size * $size * $size) {
            throw new Exception('Invalid or incomplete cube file.');
        }
        return ['size' => $size, 'data' => $data];
    }

    /**
     * Apply filter: either a built-in enum or a .cube LUT (value starting with "cube:").
     *
     * @param ImageFilterEnum|string|null $filter enum, or "cube:Name", or null for no filter
     */
    public static function applyFilter(ImageFilterEnum|string|null $filter, GdImage $image): void
    {
        if ($filter === null) {
            return;
        }
        if (is_string($filter)) {
            if (str_starts_with($filter, self::CUBE_FILTER_PREFIX)) {
                $path = self::resolveCubePath($filter);
                self::applyCubeLut($path, $image);
            }
            return;
        }
        self::applyFilterEnum($filter, $image);
    }

    public static function applyFilterEnum(?ImageFilterEnum $filter, GdImage $image): void
    {
        if ($filter === null) {
            return;
        }
        switch ($filter) {
            case ImageFilterEnum::ANTIQUE:
                imagefilter($image, IMG_FILTER_BRIGHTNESS, 0);
                imagefilter($image, IMG_FILTER_CONTRAST, -30);
                imagefilter($image, IMG_FILTER_COLORIZE, 75, 50, 25);
                break;
            case ImageFilterEnum::AQUA:
                imagefilter($image, IMG_FILTER_COLORIZE, 0, 70, 0, 30);
                break;
            case ImageFilterEnum::BLUE:
                imagefilter($image, IMG_FILTER_COLORIZE, 0, 0, 100);
                break;
            case ImageFilterEnum::BLUR:
                $blur = 5;
                for ($i = 0; $i < $blur; $i++) {
                    // each 5th time apply '_FILTER_SMOOTH' with 'level of smoothness' set to -7
                    if ($i % 5 == 0) {
                        imagefilter($image, IMG_FILTER_SMOOTH, -7);
                    }
                    imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);
                }
                break;
            case ImageFilterEnum::COLOR:
                imagefilter($image, IMG_FILTER_CONTRAST, -40);
                break;
            case ImageFilterEnum::COOL:
                imagefilter($image, IMG_FILTER_MEAN_REMOVAL);
                imagefilter($image, IMG_FILTER_CONTRAST, -50);
                break;
            case ImageFilterEnum::EDGE:
                $emboss = [[1, 1, 1], [1, -7, 1], [1, 1, 1]];
                imageconvolution($image, $emboss, 1, 0);
                break;
            case ImageFilterEnum::EMBOSS:
                $emboss = [[-2, -1, 0], [-1, 1, 1], [0, 1, 2]];
                imageconvolution($image, $emboss, 1, 0);
                break;
            case ImageFilterEnum::EVERGLOW:
                imagefilter($image, IMG_FILTER_BRIGHTNESS, -30);
                imagefilter($image, IMG_FILTER_CONTRAST, -5);
                imagefilter($image, IMG_FILTER_COLORIZE, 30, 30, 0);
                break;
            case ImageFilterEnum::GRAYSCALE:
                imagefilter($image, IMG_FILTER_GRAYSCALE);
                break;
            case ImageFilterEnum::GREEN:
                imagefilter($image, IMG_FILTER_COLORIZE, 0, 100, 0);
                break;
            case ImageFilterEnum::MEAN:
                imagefilter($image, IMG_FILTER_MEAN_REMOVAL);
                break;
            case ImageFilterEnum::NEGATE:
                imagefilter($image, IMG_FILTER_NEGATE);
                break;
            case ImageFilterEnum::PINK:
                imagefilter($image, IMG_FILTER_COLORIZE, 50, -50, 50);
                break;
            case ImageFilterEnum::PIXELATE:
                imagefilter($image, IMG_FILTER_PIXELATE, 20);
                break;
            case ImageFilterEnum::RED:
                imagefilter($image, IMG_FILTER_COLORIZE, 100, 0, 0);
                break;
            case ImageFilterEnum::RETRO:
                imagefilter($image, IMG_FILTER_GRAYSCALE);
                imagefilter($image, IMG_FILTER_COLORIZE, 100, 25, 25, 50);
                break;
            case ImageFilterEnum::SELECTIVE_BLUR:
                $blur = 5;
                for ($i = 0; $i <= $blur; $i++) {
                    imagefilter($image, IMG_FILTER_SELECTIVE_BLUR);
                }
                break;
            case ImageFilterEnum::SEPIA_DARK:
                imagefilter($image, IMG_FILTER_GRAYSCALE);
                imagefilter($image, IMG_FILTER_BRIGHTNESS, -30);
                imagefilter($image, IMG_FILTER_COLORIZE, 90, 55, 30);
                break;
            case ImageFilterEnum::SEPIA_LIGHT:
                imagefilter($image, IMG_FILTER_GRAYSCALE);
                imagefilter($image, IMG_FILTER_COLORIZE, 90, 60, 40);
                break;
            case ImageFilterEnum::SMOOTH:
                imagefilter($image, IMG_FILTER_SMOOTH, 2);
                break;
            case ImageFilterEnum::SUMMER:
                imagefilter($image, IMG_FILTER_COLORIZE, 0, 150, 0, 50);
                imagefilter($image, IMG_FILTER_NEGATE);
                imagefilter($image, IMG_FILTER_COLORIZE, 25, 50, 0, 50);
                imagefilter($image, IMG_FILTER_NEGATE);
                break;
            case ImageFilterEnum::VINTAGE:
                imagefilter($image, IMG_FILTER_BRIGHTNESS, 10);
                imagefilter($image, IMG_FILTER_GRAYSCALE);
                imagefilter($image, IMG_FILTER_COLORIZE, 40, 10, -15);
                break;
            case ImageFilterEnum::WASHED:
                imagefilter($image, IMG_FILTER_BRIGHTNESS, 30);
                imagefilter($image, IMG_FILTER_NEGATE);
                imagefilter($image, IMG_FILTER_COLORIZE, -50, 0, 20, 50);
                imagefilter($image, IMG_FILTER_NEGATE);
                imagefilter($image, IMG_FILTER_BRIGHTNESS, 10);
                break;
            case ImageFilterEnum::YELLOW:
                imagefilter($image, IMG_FILTER_COLORIZE, 100, 100, -100);
                break;
            default:
                break;
        }
    }
}
