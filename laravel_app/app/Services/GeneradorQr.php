<?php

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * QR como SVG (PHP puro, sin extensiones del hosting): se imprime y se amplía sin perder nitidez.
 */
class GeneradorQr
{
    public function svg(string $url, int $tamano = 320): string
    {
        $escritor = new Writer(new ImageRenderer(new RendererStyle($tamano, 2), new SvgImageBackEnd()));

        return $escritor->writeString($url);
    }
}
