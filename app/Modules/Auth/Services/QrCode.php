<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * Renders an otpauth:// URI as an inline SVG.
 *
 * Pulled in rather than written out, unlike the TOTP itself: a QR code is a
 * data encoding with no decision in it, so owning the implementation would buy
 * nothing. SVG keeps it inline, with no image route and no external request.
 */
final readonly class QrCode
{
    public function svg(string $text, int $size = 200): string
    {
        $writer = new Writer(new ImageRenderer(
            new RendererStyle($size, 0),
            new SvgImageBackEnd,
        ));

        // The writer emits a full document; the screen embeds it in one.
        return preg_replace('/<\?xml[^>]*\?>\s*/', '', $writer->writeString($text)) ?? '';
    }
}
