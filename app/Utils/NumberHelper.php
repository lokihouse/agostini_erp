<?php

namespace App\Utils;

class NumberHelper
{
    public static function fromMoney($state)
    {
        return number_format($state, 2, ',', '.');
    }
    public static function toMoney(string $state)
    {
        return str_replace(['.', ','], ['', '.'], $state);
    }
}
