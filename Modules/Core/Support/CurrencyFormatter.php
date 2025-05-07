<?php

namespace Modules\Core\Support;

use Modules\Core\Support\CurrencyFormatter;

use Modules\Core\Support\NumberFormatter;

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
}
