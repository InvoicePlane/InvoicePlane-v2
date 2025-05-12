<?php

namespace Modules\Core\Support;

class CurrencyFormatter extends NumberFormatter
{
    /**
     * Formats currency according to FI config.
     *
     * @param float  $amount
     * @param object $currency
     * @param int    $decimalPlaces
     *
     * @return string
     */
    public static function format($amount, $currency = null, $decimalPlaces = null)
    {
        $currency      = ($currency) ?: config('ip.currency');
        $decimalPlaces = ($decimalPlaces) ?: config('ip.amountDecimals');

        $amount = parent::format($amount, $currency, $decimalPlaces);

        if ($currency->placement == 'before') {
            return $currency->symbol . $amount;
        }

        return $amount . $currency->symbol;
    }

    public static function formatTrimmed(float $number, $decimalPlaces = null): string
    {
        $decimalPlaces ??= config('ip.currency');
        $decimalPlaces ??= config('ip.amountDecimals');

        $number = number_format($number, $decimalPlaces, '.', '');
        $number = rtrim(rtrim($number, '0'), '.');

        return $decimalPlaces->placement === 'before'
            ? $decimalPlaces->symbol . $number
            : $number . $decimalPlaces->symbol;
    }
}
