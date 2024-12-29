<?php

namespace Modules\Core\tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    public function createApplication(): Application
    {
        $app = require base_path() . '/bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
