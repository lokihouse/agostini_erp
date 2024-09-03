<?php

namespace App\Utils;

class TextHelper
{
    public static function clear($state, bool $spaces = false): string | null
    {
        $toRemove = ['.', '-', '/', '(', ')'];
        if ($spaces)  $toRemove[] = ' ';
        return str_replace(
            $toRemove,
            '',
            $state
        );
    }

    public static function toFormatedTelephone($state): string | null
    {
        $state = self::clear($state, true);
        if(strlen($state) === 10){
            return "({$state[0]}{$state[1]}) {$state[2]}{$state[3]}{$state[4]}{$state[5]}-{$state[6]}{$state[7]}{$state[8]}{$state[9]}";
        }
        return "({$state[0]}{$state[1]}) {$state[2]}{$state[3]}{$state[4]}{$state[5]}{$state[6]}-{$state[7]}{$state[8]}{$state[9]}{$state[10]}";
    }

    public static function toFormatedMoney($state)
    {
        return 'R$ ' . number_format($state, 2, ',', '.');
    }
}
