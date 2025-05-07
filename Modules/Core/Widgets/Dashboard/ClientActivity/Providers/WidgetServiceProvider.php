<?php

namespace Modules\Core\Widgets\Dashboard\ClientActivity\Providers;

use Illuminate\Support\ServiceProvider;

class WidgetServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register the view path.
        view()->addLocation(app_path('Widgets/Dashboard/ClientActivity/Views'));

        // Register the widget view composer.
        view()->composer('ClientActivityWidget', 'Modules\Core\Widgets\Dashboard\ClientActivity\Composers\ClientActivityWidgetComposer');
    }

    public function register(): void {}
}
