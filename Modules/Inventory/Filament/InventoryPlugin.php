<?php

namespace Modules\Inventory\Filament;

use Coolsam\Modules\Concerns\ModuleFilamentPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Modules\Inventory\Filament\Resources\ProductInventoryResource;

class InventoryPlugin implements Plugin
{
    use ModuleFilamentPlugin;

    public function getModuleName(): string
    {
        return 'Inventory';
    }

    public function getId(): string
    {
        return 'Inventory';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                ProductInventoryResource::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }
}
