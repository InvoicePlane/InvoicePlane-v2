<?php

namespace Modules\Payments\Filament;

use Coolsam\Modules\Concerns\ModuleFilamentPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Modules\Payments\Filament\Resources\PaymentMethodResource;
use Modules\Payments\Filament\Resources\PaymentResource;

class PaymentsPlugin implements Plugin
{
    use ModuleFilamentPlugin;

    public function getModuleName(): string
    {
        return 'Payments';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                PaymentResource::class,
                PaymentMethodResource::class,
            ]);
    }

    public function getId(): string
    {
        return 'payments';
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }
}
