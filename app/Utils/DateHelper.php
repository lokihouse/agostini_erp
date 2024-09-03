<?php

namespace App\Utils;

use Carbon\CarbonInterval;

class DateHelper
{
    public static function fromSecondsToTime($seconds)
    {
        return CarbonInterval::seconds($seconds)->cascade()->format('%H:%I:%S');
    }
}
