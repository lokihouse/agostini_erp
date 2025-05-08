<?php

namespace App\Utils;

use Filament\Support\Facades\FilamentColor;
use Spatie\Color\Rgb;

class StyleSheet
{
    public static function getColorByName(string $name, int $shade = 500): string{
        $rgb = FilamentColor::getColors()[$name][$shade];
        return Rgb::fromString("rgb($rgb)")->toHex();
    }
}
