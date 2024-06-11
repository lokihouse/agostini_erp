<?php

namespace App\Utils;

class NumberFormater
{
    public static function fromMoney($value){
        return str_replace(['R$ ','.',','], ['','', '.'], $value);
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
