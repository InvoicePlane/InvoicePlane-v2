<?php

namespace Modules\Payments\Filament\Company\Resources\PaymentMethodResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Payments\Filament\Company\Resources\PaymentMethodResource;
use Modules\Payments\Services\PaymentMethodService;

class EditPaymentMethod extends EditRecord
{
    protected static string $resource = PaymentMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate($record, array $data): Model
    {
        return app(PaymentMethodService::class)->updatePaymentMethod($record, $data);
    }
}
