<?php

namespace App\Utils;

class NumberFormater
{
    public static function fromMoney(string $value): float
    {
        $value = str_replace('R$','', $value);
        $value = str_replace('.','', $value);
        $value = str_replace(' ','', $value);
        $value = str_replace(',','.', $value);
        return (float) $value;
    }

    public static function fromString($value, $type = 'int'){
        switch ($type) {
            case 'int':
                return (int) $value;
                break;
            case 'float':
                return (float) $value;
                break;
            case 'string':
                return (string) $value;
                break;
            default:
                return $value;
                break;
        }
    }
}
