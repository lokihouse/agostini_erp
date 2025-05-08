<?php

namespace App\Utils;

class Cnpj
{
    public static function format($cnpj): string
    {
        return preg_replace_callback(
            '/(.{2})(.{3})(.{3})(.{4})(.{2})/',
            function ($_) {
                return "{$_[1]}.{$_[2]}.{$_[3]}/{$_[4]}-{$_[5]}";
            },
            $cnpj
        );
    }

    public static function clear($state): string
    {
        return preg_replace('/[^a-zA-Z0-9]/', '', $state);
    }
}
