<?php

namespace Modules\Core\Support;

class NumberFormatter
{
    public static function format($number, $currency = null, $decimalPlaces = null): float|string
    {
        $currency = $currency ?: config('ip.currency');
        $decimalPlaces ??= config('ip.amountDecimals');

        return number_format($number, $decimalPlaces, $currency->decimal, $currency->thousands);
    }

    public static function formatTrimmed(float $number, int $decimalPlaces = 4): string
    {
        $formatted = number_format($number, $decimalPlaces, '.', '');

        return rtrim(rtrim($formatted, '0'), '.');
    }

    public static function unformat($number, $currency = null): float
    {
        $currency = $currency ?: config('ip.currency');

        $number = str_replace([$currency->decimal, $currency->thousands, 'D'], ['D', '', '.'], $number);

        return $number;
    }
}
