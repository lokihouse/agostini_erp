<?php

namespace App\Utils;

use Carbon\CarbonInterval;

class MyDateTimeFormater
{
    public static function secondsToClock(int $seconds = null): string
    {
        if(is_null($seconds)) return '-';
        return CarbonInterval::seconds($seconds)->cascade()->format('%H:%I:%S');
    }

    public static function clockToSeconds(string $clock): int | null
    {
        if(empty($clock)) return null;
        $clock = explode(':', $clock);
        return $clock[0] * 3600 + $clock[1] * 60 + $clock[2];
    }
}
