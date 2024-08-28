<?php

namespace Modules\Payments\Filament\Resources\PaymentMethodResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\Payments\Filament\Resources\PaymentMethodResource;

class ManagePaymentMethods extends ManageRecords
{
    protected static string $resource = PaymentMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
