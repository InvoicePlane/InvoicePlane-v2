<?php

namespace Modules\Core\Support;

use Modules\Core\Support\NumberFormatter;

class NumberFormatter
{
    /**
     * Formats a number accordingly.
     *
     * @param float  $number
     * @param object $currency
     * @param int    $decimalPlaces
     *
     * @return float
     */
    public static function format($number, $currency = null, $decimalPlaces = null)
    {
        $currency      = ($currency) ?: config('ip.currency');
        $decimalPlaces = ($decimalPlaces) ?: config('ip.amountDecimals');

        return number_format($number, $decimalPlaces, $currency->decimal, $currency->thousands);
    }

    /**
     * Unformats a formatted number.
     *
     * @param float  $number
     * @param object $currency
     *
     * @return float
     */
    public static function unformat($number, $currency = null)
    {
        $currency = ($currency) ?: config('ip.currency');

        $number = str_replace($currency->decimal, 'D', $number);
        $number = str_replace($currency->thousands, '', $number);
        $number = str_replace('D', '.', $number);

        return $number;
    }
}
