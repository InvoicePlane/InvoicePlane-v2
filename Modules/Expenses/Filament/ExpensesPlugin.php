<?php

namespace Modules\Expenses\Filament;

use Coolsam\Modules\Concerns\ModuleFilamentPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Modules\Expenses\Filament\Resources\ExpenseCategoryResource;
use Modules\Expenses\Filament\Resources\ExpenseResource;
use Modules\Expenses\Filament\Resources\ExpenseVendorResource;

class ExpensesPlugin implements Plugin
{
    use ModuleFilamentPlugin;

    public function getModuleName(): string
    {
        return 'Expenses';
    }

    public function getId(): string
    {
        return 'expenses';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                ExpenseResource::class,
                ExpenseCategoryResource::class,
                ExpenseVendorResource::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }
}
