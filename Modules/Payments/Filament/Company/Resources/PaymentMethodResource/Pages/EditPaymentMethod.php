<?php

namespace Modules\Payments\Filament\Company\Resources\PaymentMethodResource\Pages;

use Modules\Core\Support\Results\Payments;

use Modules\Payments\Filament\Company\Resources\PaymentMethodResource\Pages\EditPaymentMethod;

use Modules\Payments\Filament\Company\Resources\PaymentMethodResource;

use Modules\Core\Models\Company;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Payments\Filament\Company\Resources\PaymentMethodResource;

class EditPaymentMethod extends EditRecord
{
    protected static string $resource = PaymentMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
