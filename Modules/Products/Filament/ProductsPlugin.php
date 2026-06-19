<?php

namespace Modules\Products\Filament;

use Coolsam\Modules\Concerns\ModuleFilamentPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Modules\Products\Filament\Resources\ProductFamilyResource;
use Modules\Products\Filament\Resources\ProductResource;
use Modules\Products\Filament\Resources\ProductUnitResource;

class ProductsPlugin implements Plugin
{
    use ModuleFilamentPlugin;

    public function getModuleName(): string
    {
        return 'Products';
    }

    public function getId(): string
    {
        return 'products';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                ProductResource::class,
                ProductFamilyResource::class,
                ProductUnitResource::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }
}
