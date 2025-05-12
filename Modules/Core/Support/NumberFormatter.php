<?php

namespace Modules\Core\Support;

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
    public static function format($number, $currency = null, $decimalPlaces = null): float
    {
        $currency = $currency ?: config('ip.currency');
        $decimalPlaces ??= config('ip.amountDecimals');

        return number_format($number, $decimalPlaces, $currency->decimal, $currency->thousands);
    }

    /**
     * Formats a number and trims unnecessary trailing zeros.
     *
     * @param float    $number
     * @param object   $currency
     * @param int|null $decimalPlaces
     *
     * @return string
     */
    public static function formatTrimmed(float $number, int $decimalPlaces = 4): string
    {
        $formatted = number_format($number, $decimalPlaces, '.', '');

        return rtrim(rtrim($formatted, '0'), '.');
    }

    /**
     * Unformats a formatted number.
     *
     * @param float  $number
     * @param object $currency
     *
     * @return float
     */
    public static function unformat($number, $currency = null): float
    {
        $currency = $currency ?: config('ip.currency');

        $number = str_replace([$currency->decimal, $currency->thousands, 'D'], ['D', '', '.'], $number);

        return $number;
    }
}
