<?php

namespace Modules\Payments\Filament\Company\Resources\PaymentMethodResource\Pages;

use Modules\Core\Support\Results\Payments;

use Modules\Payments\Filament\Company\Resources\PaymentMethodResource\Pages\ListPaymentMethods;

use Modules\Payments\Filament\Company\Resources\PaymentMethodResource;

use Modules\Core\Models\Company;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
