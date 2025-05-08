<?php

namespace Modules\Payments\Support;

use Modules\Core\Support\Directory;

class MerchantFactory
{
    public static function getDrivers($enabledOnly = false)
    {
        $files = Directory::listContents(app_path('IpModules/Merchant/Support/MerchantDrivers'));

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
        $driver = 'Modules\Core\\IpModules\\Merchant\\Support\\MerchantDrivers\\' . $driver;

        return new $driver();
    }
}
