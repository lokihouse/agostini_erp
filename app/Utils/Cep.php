<?php

namespace App\Utils;

class Cep
{
    public static function format($cep): string
    {
        return preg_replace_callback(
            '/(\d{5})(\d{3})/',
            function ($_) {
                return "{$_[1]}-{$_[2]}";
            },
            $cep
        );
    }

    public static function clear($cep): string
    {
        return preg_replace('/[^0-9]/', '', $cep);
    }
}
