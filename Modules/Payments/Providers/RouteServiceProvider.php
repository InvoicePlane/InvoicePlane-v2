<?php

namespace Modules\Payments\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $moduleNamespace = 'Modules\Payments\Http\Controllers';

    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    protected function mapApiRoutes(): void
    {
        Route::prefix('api')
            ->name('api.')
            ->group(function (): void {
                foreach (File::files(module_path('Payments', 'Routes/Api/')) as $file) {
                    require $file;
                }
            });
    }

    protected function mapWebRoutes(): void
    {
        foreach (File::files(module_path('Payments', 'Routes/Web/')) as $file) {
            require $file;
        }
    }
}
