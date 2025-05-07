<?php

namespace Modules\Core\Support;

use Modules\Core\Support\Directory;

use Modules\Core\Support\ProfileImage\Drivers\Gravatar;

class ProfileImageFactory
{
    public static function create()
    {
        $class = 'Modules\Core\Support\ProfileImage\Drivers\\' . config('ip.profileImageDriver', 'Gravatar');

        return new $class();
    }

    public static function getDrivers()
    {
        $driverFiles = Directory::listContents(app_path('Support/ProfileImage/MerchantDrivers'));
        $drivers     = [];

        foreach ($driverFiles as $driverFile) {
            $driver = str_replace('.php', '', $driverFile);

            $drivers[$driver] = $driver;
        }

        return $drivers;
    }
}
