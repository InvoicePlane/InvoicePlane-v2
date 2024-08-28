<?php

namespace Modules\Payments\Filament\Resources\PaymentResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Payments\Filament\Resources\PaymentResource;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;
}
