<?php

namespace App\Support\ProfileImage;

class ProfileImageFactory
{
    public static function create()
    {
        $class = 'App\Support\ProfileImage\Drivers\\' . config('ip.profileImageDriver', 'Gravatar');

        return new $class();
    }

    public static function getDrivers()
    {
        $driverFiles = Directory::listContents(app_path('Support/ProfileImage/Drivers'));
        $drivers     = [];

        foreach ($driverFiles as $driverFile) {
            $driver = str_replace('.php', '', $driverFile);

            $drivers[$driver] = $driver;
        }

        return $drivers;
    }
}
