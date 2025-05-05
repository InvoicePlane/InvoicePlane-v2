<?php

namespace App\IpModules\Currencies\Support;

class CurrencyConverterFactory
{
    public static function create()
    {
        $class = 'App\IpModules\Currencies\Support\Drivers\\' . config('ip.currencyConversionDriver');

        return new $class();
    }
}
