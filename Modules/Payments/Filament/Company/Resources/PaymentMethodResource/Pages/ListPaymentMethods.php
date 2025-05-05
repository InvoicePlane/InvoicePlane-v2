<?php

namespace Modules\Payments\Filament\Company\Resources\PaymentMethodResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Payments\Filament\Company\Resources\PaymentMethodResource;

class ListPaymentMethods extends ListRecords
{
    protected static string $resource = PaymentMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
