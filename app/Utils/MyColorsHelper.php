<?php

namespace App\Utils;

use Carbon\CarbonInterval;
use Filament\Support\Facades\FilamentColor;

class MyColorsHelper
{
    public static function getDefaultColors(string $color = 'primary', int $shade = 500, string | null $format = null)
    {
        $cor = FilamentColor::getColors()[$color][$shade];
        if($format === 'hex') {
            $rgb = explode(",", $cor);
            $r = str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
            $g = str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
            $b = str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);
            return "#$r$g$b";
        }else {
            return $cor;
        }
    }
}
