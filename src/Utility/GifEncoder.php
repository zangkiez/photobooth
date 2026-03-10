<?php

namespace Photobooth\Utility;

/**
 * Pure-PHP animated GIF encoder.
 *
 * Uses PHP GD to quantise JPEG frames into individual GIF89a files and then
 * stitches them together into a single GIF89a animation using the Netscape
 * Application Extension loop block.
 *
 * No external dependencies (no ffmpeg, no Imagick) – GD only.
 */
class GifEncoder
{
    /**
     * Build an animated GIF from an ordered array of JPEG file paths.
     *
     * @param  string[] $jpegPaths   Absolute paths to source JPEG frames (playback order).
     * @param  int      $maxWidth    Frames are scaled so their width does not exceed this value.
     * @param  int      $frameDelay  Delay between frames in centiseconds (1/100 s).
     * @param  int      $loops       Repetition count – 0 means infinite.
     * @return string                Raw GIF89a binary data.
     * @throws \RuntimeException     On file-not-found or GD failure.
     */
    public static function createAnimatedGif(
        array $jpegPaths,
        int $maxWidth = 640,
        int $frameDelay = 80,
        int $loops = 0
    ): string {
        if (empty($jpegPaths)) {
            throw new \RuntimeException('GifEncoder: no frames provided.');
        }

        [$outW, $outH] = self::calcDimensions($jpegPaths[0], $maxWidth);

        $gifFrames = [];
        foreach ($jpegPaths as $path) {
            if (!file_exists($path)) {
                throw new \RuntimeException("GifEncoder: frame not found – $path");
            }

            $src = @imagecreatefromjpeg($path);
            if (!$src instanceof \GdImage) {
                throw new \RuntimeException("GifEncoder: cannot read JPEG – $path");
            }

            $canvas = imagecreatetruecolor($outW, $outH);
            imagecopyresampled($canvas, $src, 0, 0, 0, 0, $outW, $outH, imagesx($src), imagesy($src));
            imagedestroy($src);

            // Convert to 255-colour palette with Floyd-Steinberg dithering before
            // encoding as GIF — dramatically reduces the muddy / blurry look that
            // comes from GIF's 256-colour limit when PHP does the conversion itself.
            imagetruecolortopalette($canvas, true, 255);

            ob_start();
            imagegif($canvas);
            $raw = ob_get_clean();
            imagedestroy($canvas);

            if ($raw === false || $raw === '') {
                throw new \RuntimeException("GifEncoder: imagegif() produced no output for – $path");
            }

            $gifFrames[] = $raw;
        }

        return self::buildAnimated($gifFrames, $frameDelay, $loops);
    }

    // -------------------------------------------------------------------------

    /**
     * Combine single-frame GIF89a binaries into one animated GIF89a.
     *
     * @param  string[] $frames   Single-frame GIF89a binary strings.
     * @param  int      $delayCs  Per-frame delay in centiseconds.
     * @param  int      $loops    0 = infinite.
     */
    private static function buildAnimated(array $frames, int $delayCs, int $loops): string
    {
        // ---- Global header from first frame ----
        $first    = $frames[0];
        $lsd      = substr($first, 6, 7);            // Logical Screen Descriptor (7 bytes)
        $packed   = ord($lsd[4]);
        $hasGCT   = ($packed >> 7) & 1;
        $gctField = $packed & 0x07;
        $gctSize  = $hasGCT ? (3 * (1 << ($gctField + 1))) : 0;

        $out  = 'GIF89a';
        $out .= $lsd;
        $out .= substr($first, 13, $gctSize);        // Global Color Table

        // ---- Netscape 2.0 loop extension ----
        $out .= "\x21\xFF\x0B";                      // Extension introducer + App label + size=11
        $out .= 'NETSCAPE2.0';                        // Application identifier + auth code
        $out .= "\x03\x01";                           // Sub-block size (3) + sub-block ID (1)
        $out .= pack('v', $loops);                    // Loop count (little-endian uint16)
        $out .= "\x00";                               // Block terminator

        // ---- Per-frame blocks ----
        foreach ($frames as $gif) {
            $frameLsd    = substr($gif, 6, 7);
            $framePacked = ord($frameLsd[4]);
            $frameHasGCT = ($framePacked >> 7) & 1;
            $frameGctFld = $framePacked & 0x07;
            $frameGctSz  = $frameHasGCT ? (3 * (1 << ($frameGctFld + 1))) : 0;

            // Image payload = everything after GIF89a(6) + LSD(7) + GCT
            $payload = substr($gif, 13 + $frameGctSz);

            // Strip GIF trailer byte if present
            if (substr($payload, -1) === "\x3B") {
                $payload = substr($payload, 0, -1);
            }

            // Graphic Control Extension (sets disposal & per-frame delay)
            $out .= "\x21\xF9\x04";                  // Extension + GCE label + block size=4
            $out .= "\x04";                           // packed: disposal=1 (restore background)
            $out .= pack('v', $delayCs);              // Delay in 1/100 s (little-endian)
            $out .= "\x00";                           // Transparent colour index (not used)
            $out .= "\x00";                           // Block terminator

            $out .= $payload;
        }

        $out .= "\x3B"; // GIF Trailer

        return $out;
    }

    /**
     * Return [width, height] scaled so width ≤ $maxWidth.
     *
     * @return int[]
     */
    private static function calcDimensions(string $path, int $maxWidth): array
    {
        $info = @getimagesize($path);
        if (!$info) {
            throw new \RuntimeException("GifEncoder: cannot read image dimensions – $path");
        }
        [$w, $h] = $info;
        if ($w <= $maxWidth) {
            return [$w, $h];
        }
        return [$maxWidth, (int) round($h * $maxWidth / $w)];
    }
}
