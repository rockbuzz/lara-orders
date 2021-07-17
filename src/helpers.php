<?php

if (!function_exists('percentage_of')) {
    function percentage_of(int $percentage, $value)
    {
        return floatval($value * ($percentage / 100));
    }
}
