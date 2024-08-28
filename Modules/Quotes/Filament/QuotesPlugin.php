<?php

namespace Modules\Quotes\Filament;

use Coolsam\Modules\Concerns\ModuleFilamentPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Modules\Quotes\Filament\Resources\QuoteResource;

class QuotesPlugin implements Plugin
{
    use ModuleFilamentPlugin;

    public function getModuleName(): string
    {
        return 'Quotes';
    }

    public function getId(): string
    {
        return 'quotes';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                QuoteResource::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }
}
