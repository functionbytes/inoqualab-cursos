<?php

namespace App\Model;

use Carbon\Carbon;

class Utilities
{
    public static function validateDate($create)
    {
        return Carbon::createFromFormat('Y-m-d', $create, 'America/Bogota');
    }

    public static function validateDateOrder($create)
    {
        return Carbon::createFromFormat('Y-m-d', $create, 'America/Bogota')->addMonths(3);
    }

    public static function validateYear($create)
    {
        return Carbon::createFromFormat('Y-m-d', $create, 'America/Bogota')->addMonths(12);
    }
}
