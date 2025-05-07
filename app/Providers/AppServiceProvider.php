<?php

namespace App\Providers;

use Filament\Tables\Actions\CreateAction as TableCreateAction;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Modules\Invoices\Models\Invoice;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Relation::morphMap([
            'invoice' => Invoice::class,
        ]);

        //TableEditAction::configureUsing(fn (TableEditAction $action) => $action->modalWidth('7xl'));
        TableCreateAction::configureUsing(fn (TableCreateAction $action) => $action->modalWidth('7xl'));
    }
}
