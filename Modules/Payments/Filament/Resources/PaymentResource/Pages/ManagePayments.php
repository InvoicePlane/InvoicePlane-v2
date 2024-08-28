<?php

namespace Modules\Payments\Filament\Resources\PaymentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\Payments\Filament\Resources\PaymentResource;

class ManagePayments extends ManageRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
