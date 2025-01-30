<?php

namespace App\Utils;

class Telefone
{
    public static function format($telefone): ?string
    {
        if(!!!$telefone) return $telefone;
        $telefone = Telefone::clear($telefone);
        $pattern8digitos = '/(.{2})(.{4})(.{4})/';
        $pattern9digitos = '/(.{2})(.{5})(.{4})/';
        return preg_replace_callback(
            strlen($telefone) === 8 ? $pattern8digitos : $pattern9digitos,
            function ($_) {
                return "({$_[1]}) {$_[2]}-{$_[3]}";
            },
            $telefone
        );
    }

    public static function clear($telefone): string
    {
        return preg_replace('/[^0-9]/', '', $telefone);
    }
}
