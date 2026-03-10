<?php

namespace Photobooth\Utility;

/**
 * Pure-PHP animated GIF encoder.
 *
 * Uses PHP GD to quantise JPEG frames into individual GIF89a files and then
 * stitches them together into a single GIF89a animation using the Netscape
 * Application Extension loop block.
 *
 * Each frame carries its own Local Color Table so pixel indices are always
 * interpreted against the correct per-frame palette – this avoids the
 * psychedelic / colour-shifted artefacts caused by reusing a single global
 * palette across frames that were quantised independently.
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

            // Quantise to 255-colour palette with Floyd-Steinberg dithering.
            // Each frame gets its OWN palette – buildAnimated() will attach it
            // as a per-frame Local Color Table so indices stay correct.
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

        return self::buildAnimated($gifFrames, $outW, $outH, $frameDelay, $loops);
    }

    // -------------------------------------------------------------------------

    /**
     * Combine single-frame GIF89a binaries into one animated GIF89a.
     *
     * Every frame is given a Local Color Table (LCT) that overrides the global
     * palette for that frame.  This is critical when frames were quantised
     * independently: without per-frame LCTs the pixel index values would be
     * decoded using the wrong (first-frame) palette, producing psychedelic
     * colour artefacts.
     *
     * @param  string[] $frames   Single-frame GIF89a binary strings.
     * @param  int      $width    Canvas width (pixels).
     * @param  int      $height   Canvas height (pixels).
     * @param  int      $delayCs  Per-frame delay in centiseconds.
     * @param  int      $loops    0 = infinite.
     */
    private static function buildAnimated(array $frames, int $width, int $height, int $delayCs, int $loops): string
    {
        // ---- Parse first frame to extract a valid global palette (required by
        //      GIF89a spec even when frames use LCTs). ----
        $first      = $frames[0];
        $lsdPacked  = ord($first[10]);                 // byte 10 of GIF header
        $hasGCT     = ($lsdPacked >> 7) & 1;
        $gctField   = $lsdPacked & 0x07;
        $gctSize    = $hasGCT ? (3 * (1 << ($gctField + 1))) : 0;
        $firstGCT   = $hasGCT ? substr($first, 13, $gctSize) : '';

        // Build Logical Screen Descriptor with the first frame's dimensions/flags
        // but mark it as having a GCT (the first frame's palette becomes global).
        $out  = 'GIF89a';
        $out .= pack('vv', $width, $height);           // canvas width, height
        $out .= chr($lsdPacked);                        // packed field (preserves GCT info)
        $out .= "\x00\x00";                            // bg colour index, aspect ratio
        $out .= $firstGCT;                              // Global Color Table

        // ---- Netscape 2.0 loop extension ----
        $out .= "\x21\xFF\x0B";                        // Extension + App label size=11
        $out .= 'NETSCAPE2.0';                          // Application identifier + auth code
        $out .= "\x03\x01";                            // Sub-block size (3) + sub-block ID (1)
        $out .= pack('v', $loops);                      // Loop count (little-endian uint16)
        $out .= "\x00";                                // Block terminator

        // ---- Per-frame blocks ----
        foreach ($frames as $gif) {
            // Parse this frame's header
            $fPacked  = ord($gif[10]);
            $fHasGCT  = ($fPacked >> 7) & 1;
            $fGctFld  = $fPacked & 0x07;
            $fGctSz   = $fHasGCT ? (3 * (1 << ($fGctFld + 1))) : 0;
            $framePalette = $fHasGCT ? substr($gif, 13, $fGctSz) : $firstGCT;
            $palField     = strlen($framePalette) > 0 ? $fGctFld : 0;

            // Payload starts after header(6) + LSD(7) + GCT
            $payload = substr($gif, 13 + $fGctSz);

            // Strip GIF trailer
            if (strlen($payload) > 0 && $payload[strlen($payload) - 1] === "\x3B") {
                $payload = substr($payload, 0, -1);
            }

            // Graphic Control Extension
            $out .= "\x21\xF9\x04";                    // Extension + GCE label + block size=4
            $out .= "\x00";                            // packed: disposal=0 (keep), no transparency
            $out .= pack('v', $delayCs);                // delay in 1/100 s
            $out .= "\x00";                            // transparent colour index (unused)
            $out .= "\x00";                            // block terminator

            // Find Image Descriptor (0x2C) in payload.
            // Strip any leading extension blocks (e.g. a residual GCE from PHP).
            $imgDescPos = false;
            for ($i = 0; $i < strlen($payload); $i++) {
                if ($payload[$i] === "\x2C") {
                    $imgDescPos = $i;
                    break;
                } elseif ($payload[$i] === "\x21") {
                    // Skip extension block
                    $i += 2; // label + block-size byte
                    $blockSize = ord($payload[$i]);
                    while ($blockSize > 0 && $i < strlen($payload)) {
                        $i += $blockSize;
                        $blockSize = $i + 1 < strlen($payload) ? ord($payload[$i + 1]) : 0;
                        $i++;
                    }
                }
            }

            if ($imgDescPos === false || $imgDescPos + 10 > strlen($payload)) {
                // Fallback: just append as-is (frame will look wrong but won't crash)
                $out .= $payload;
                continue;
            }

            $beforeDesc  = substr($payload, 0, $imgDescPos);   // usually empty
            $imgDesc     = substr($payload, $imgDescPos, 10);   // 10-byte Image Descriptor
            $imageData   = substr($payload, $imgDescPos + 10);

            // Patch Image Descriptor: set Local Color Table flag + size, clear interlace
            $idPacked = ord($imgDesc[9]);
            $idPacked = ($idPacked & 0x1F) | 0x80 | ($palField & 0x07);  // set LCT flag + size
            $imgDesc  = substr($imgDesc, 0, 9) . chr($idPacked);

            $out .= $beforeDesc . $imgDesc . $framePalette . $imageData;
        }

        $out .= "\x3B"; // GIF Trailer

        return $out;
    }

    /**
     * Return [width, height] scaled so width <= $maxWidth.
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
