<?php

namespace Modules\Payments\Filament\Company\Resources\PaymentResource\Pages;

use Modules\Core\Support\Results\Payments;

use Modules\Payments\Filament\Company\Resources\PaymentResource\Pages\ListPayments;

use Modules\Core\Models\Company;

use Modules\Payments\Filament\Company\Resources\PaymentResource;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Payments\Filament\Company\Resources\PaymentResource;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
