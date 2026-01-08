<?php

namespace Modules\Core\Support\PDF;

class PDFFactory
{
    public static function create()
    {
        $class = 'Modules\Core\Support\PDF\Drivers\\' . config('ip.pdfDriver');

        return new $class();
    }

    public static function getDrivers(): array
    {
        //Directory::listContents(app_path('Support/PDF/MerchantDrivers'))
        $driverFiles = [];
        $drivers     = [];

        foreach ($driverFiles as $driverFile) {
            $driver = str_replace('.php', '', $driverFile);

            $drivers[$driver] = $driver;
        }

        return $drivers;
    }
}
