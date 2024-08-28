<?php

namespace Modules\Core\Filament;

use Coolsam\Modules\Concerns\ModuleFilamentPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Modules\Core\Filament\Resources\EmailTemplateResource;
use Modules\Core\Filament\Resources\TaxRateResource;
use Modules\Core\Filament\Resources\UserResource;

class CorePlugin implements Plugin
{
    use ModuleFilamentPlugin;

    public function getModuleName(): string
    {
        return 'Core';
    }

    public function getId(): string
    {
        return 'core';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                EmailTemplateResource::class,
                UserResource::class,
                TaxRateResource::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }
}
