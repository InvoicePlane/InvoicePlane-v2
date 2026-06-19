<?php

namespace Modules\Invoices\Filament;

use Coolsam\Modules\Concerns\ModuleFilamentPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Modules\Invoices\Filament\Resources\InvoiceGroupResource;
use Modules\Invoices\Filament\Resources\InvoiceResource;

class InvoicesPlugin implements Plugin
{
    use ModuleFilamentPlugin;

    public function getModuleName(): string
    {
        return 'Invoices';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                InvoiceResource::class,
                InvoiceGroupResource::class,
            ]);
    }

    public function getId(): string
    {
        return 'invoices';
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }
}
