<?php

namespace App\Utils;

use Carbon\Carbon;

class TextFormater
{
    public static function toMoney($value){
        return "R$ " . number_format($value, 2, ',', '.');
    }
    public static function toCnpj($cnpj){
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
    }

    public static function toCpf($cpf){
        return preg_replace('/(\(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }

    public static function toDate($date, $format = 'd/m/Y'){
        return Carbon::parse($date)->format($format);
    }

    public static function clear($str)
    {
        return preg_replace('/[^0-9]/', '', $str);
    }

    public static function toTelefone(string $str)
    {
        if(strlen($str) == 10){
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $str);
        }else{
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $str);
        }
    }
}
