<?php

namespace Modules\Clients\Filament;

use Coolsam\Modules\Concerns\ModuleFilamentPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Modules\Clients\Filament\Resources\ClientResource;

class ClientsPlugin implements Plugin
{
    use ModuleFilamentPlugin;

    public function getModuleName(): string
    {
        return 'Clients';
    }

    public function getId(): string
    {
        return 'clients';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                ClientResource::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }
}
