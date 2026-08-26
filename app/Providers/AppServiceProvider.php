<?php

namespace App\Providers;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Filament\Responses\LoginResponse;
use Modules\Invoices\Models\Invoice;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            LoginResponseContract::class,
            LoginResponse::class
        );
    }

    public function boot(): void
    {
        Relation::morphMap([
            'invoice' => Invoice::class,
        ]);

        if ( ! app()->isLocal()) {
            URL::forceScheme('https');
        }
    }
}
