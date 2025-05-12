<?php

namespace Modules\Payments\Support;

use Modules\Core\Support\Directory;

class MerchantFactory
{
    public static function getDrivers($enabledOnly = false): array
    {
        $files = Directory::listContents(app_path('IpModules/Merchant/Support/MerchantDrivers'));

        $drivers = [];

        foreach ($files as $file) {
            $file = basename($file, '.php');

            $driver = self::create($file);

            if ( ! $enabledOnly || ($enabledOnly && $driver->getSetting('enabled'))) {
                $drivers[$file] = $driver;
            }
        }

        return $drivers;
    }

    /**
     * @param $driver
     *
     * @return MerchantDriver
     */
    public static function create($driver): MerchantDriver
    {
        $driver = 'Modules\Core\\IpModules\\Merchant\\Support\\MerchantDrivers\\' . $driver;

        return new $driver();
    }

    /*public static function make(string $merchantName): BankTransferClient|PaypalClient|StripeClient
    {
        return match (mb_strtolower($merchantName)) {
            'paypal'        => new PaypalClient(),
            'stripe'        => new StripeClient(),
            'bank_transfer' => new BankTransferClient(),
            default         => throw new InvalidArgumentException("Unknown merchant: {$merchantName}"),
        };
    }*/
}
