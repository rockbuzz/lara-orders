<?php

if (!function_exists('percentage_of')) {
    function percentage_of(int $percentage, float $float)
    {
        return (float) $float * ($percentage / 100);
    }
}

if (!function_exists('format_currency')) {
    function format_currency(int $cents)
    {
        return (float) number_format($cents / 100, 2, '.', '');
    }
}

if (!function_exists('to_pennies')) {
    function to_pennies($value)
    {
        return (int) (string) (float) preg_replace("/[^0-9.]/", "", $value) * 100;
    }
}
