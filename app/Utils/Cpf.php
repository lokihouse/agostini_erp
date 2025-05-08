<?php

namespace App\Utils;

class Cpf
{
    public static function format($cpf): string
    {
        return preg_replace_callback(
            '/(.{3})(.{3})(.{3})(.{2})/',
            function ($_) {
                return "{$_[1]}.{$_[2]}.{$_[3]}-{$_[4]}";
            },
            $cpf
        );
    }

    public static function clear($cpf): string
    {
        return preg_replace('/[^0-9]/', '', $cpf);
    }
}
