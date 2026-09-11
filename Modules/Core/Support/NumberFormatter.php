<?php

namespace Modules\Core\Support;

class NumberFormatter
{
    public static function format($number, $currency = null, $decimalPlaces = null): float|string
    {
        $currency = $currency ?: config('ip.currency');
        $decimalPlaces ??= config('ip.amountDecimals') ?? 2;
        $decimal   = is_object($currency) ? ($currency->decimal ?? '.') : '.';
        $thousands = is_object($currency) ? ($currency->thousands ?? ',') : ',';

        return number_format((float) $number, (int) $decimalPlaces, $decimal, $thousands);
    }

    public static function formatCurrency($number, ?string $currencyCode = null): string
    {
        $formatted = self::format($number);
        $code      = $currencyCode ?: (string) (config('ip.currency_code') ?: 'USD');

        return match ($code) {
            'USD'   => '$' . $formatted,
            'EUR'   => '€' . $formatted,
            'GBP'   => '£' . $formatted,
            'JPY'   => '¥' . $formatted,
            default => $code . ' ' . $formatted,
        };
    }

    public static function formatTrimmed(float $number, int $decimalPlaces = 4): string
    {
        $formatted = number_format($number, $decimalPlaces, '.', '');

        return mb_rtrim(mb_rtrim($formatted, '0'), '.');
    }

    public static function unformat($number, $currency = null): float|string
    {
        $currency  = $currency ?: config('ip.currency');
        $decimal   = is_object($currency) ? ($currency->decimal ?? '.') : '.';
        $thousands = is_object($currency) ? ($currency->thousands ?? ',') : ',';

        $number = str_replace([$decimal, $thousands, 'D'], ['D', '', '.'], (string) $number);

        return $number;
    }
}
