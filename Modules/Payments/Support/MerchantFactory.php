<?php

namespace App\IpModules\Merchant\Support;

use App\Support\Directory;

class MerchantFactory
{
    public static function getDrivers($enabledOnly = false)
    {
        $files = Directory::listContents(app_path('IpModules/Merchant/Support/Drivers'));

        $drivers = [];

        foreach ($files as $file) {
            $file = basename($file, '.php');

            $driver = self::create($file);

            if ( ! $enabledOnly || $enabledOnly && $driver->getSetting('enabled')) {
                $drivers[$file] = $driver;
            }
        }

        return $drivers;
    }

    /**
     * @return MerchantDriver
     */
    public static function create($driver)
    {
        $driver = 'App\\IpModules\\Merchant\\Support\\Drivers\\' . $driver;

        return new $driver();
    }
}
