<?php

namespace App\Support\PDF;

use App\Support\Directory;

class PDFFactory
{
    public static function create()
    {
        $class = 'App\Support\PDF\Drivers\\' . config('ip.pdfDriver');

        return new $class();
    }

    public static function getDrivers()
    {
        $driverFiles = Directory::listContents(app_path('Support/PDF/Drivers'));
        $drivers     = [];

        foreach ($driverFiles as $driverFile) {
            $driver = str_replace('.php', '', $driverFile);

            $drivers[$driver] = $driver;
        }

        return $drivers;
    }
}
