<?php

use Rockbuzz\LaraOrders\Models\OrderCoupon as Coupon;

if (!function_exists('percentage_of')) {
    function percentage_of(int $percentage, $value)
    {
        return floatval($value * ($percentage / 100));
    }
}

if (!function_exists('value_format')) {
    function value_format(int $cents)
    {
        return number_format($cents / 100, 2, '.', '');
    }
}

if (!function_exists('convert_to_cents')) {
    function convert_to_cents($value)
    {
        return intval($value * 100);
    }
}

if (!function_exists('calculate_discount')) {
    function calculate_discount(Coupon $coupon, $total)
    {
        if ($coupon->isPercentage()) {
            return percentage_of($coupon->value, $total);
        }

        return $coupon->value / 100;
    }
}
