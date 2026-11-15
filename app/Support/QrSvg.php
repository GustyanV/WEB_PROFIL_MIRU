<?php
namespace App\Support;

use BaconQrCode\Writer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\{RendererStyle,Fill,EyeFill};
use BaconQrCode\Renderer\Color\Rgb;

final class QrSvg
{
    public function generate(string $value): string
    {
        $fill=Fill::withForegroundColor(new Rgb(255,255,255),new Rgb(0,0,0),EyeFill::inherit(),EyeFill::inherit(),EyeFill::inherit());
        return (new Writer(new ImageRenderer(new RendererStyle(150,0,null,null,$fill),new SvgImageBackEnd())))->writeString($value);
    }
}