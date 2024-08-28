<?php

namespace Modules\Payments\Filament\Resources\PaymentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Payments\Filament\Resources\PaymentResource;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
