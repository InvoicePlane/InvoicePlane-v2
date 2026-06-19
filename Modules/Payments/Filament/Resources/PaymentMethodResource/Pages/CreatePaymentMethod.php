<?php

namespace Modules\Payments\Filament\Resources\PaymentMethodResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Payments\Filament\Resources\PaymentMethodResource;

class CreatePaymentMethod extends CreateRecord
{
    protected static string $resource = PaymentMethodResource::class;
}
