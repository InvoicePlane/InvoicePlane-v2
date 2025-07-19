<?php

namespace App\Providers;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
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

        // Route model binding for company by search_code only
        /*\Illuminate\Support\Facades\Route::bind('tenant', function ($value) {
            // Use the new static method on the Company model to find the company
            // This method includes the logic for case conversion and debugging.
            return Company::findBySearchCode($value);
        });*/

        // Filament handles tenant model binding via ->tenant() in the panel provider.

        /*DB::listen(function ($query) {
            logger('SQL', [$query->sql, $query->bindings]);
        });*/
    }
}
