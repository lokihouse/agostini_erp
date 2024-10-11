<?php

namespace App\Utils;

use Carbon\Carbon;
use Carbon\CarbonInterval;

class DateHelper
{
    public static function fromSecondsToTime($seconds)
    {
        return CarbonInterval::seconds($seconds)->cascade()->format('%H:%I:%S');
    }

    public static function fromYYYYMMDD2String($date)
    {
        return is_null($date) ? '-' : Carbon::createFromFormat('Y-m-d', $date)->format('d/m/Y');
    }
}
