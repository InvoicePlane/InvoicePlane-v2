<?php

namespace Modules\Payments\Filament\Company\Resources\PaymentMethodResource\Pages;

use Modules\Core\Support\Results\Payments;

use Modules\Payments\Filament\Company\Resources\PaymentMethodResource;

use Modules\Core\Models\Company;

use Modules\Payments\Filament\Company\Resources\PaymentMethodResource\Pages\CreatePaymentMethod;

use Filament\Resources\Pages\CreateRecord;
use Modules\Payments\Filament\Company\Resources\PaymentMethodResource;

class CreatePaymentMethod extends CreateRecord
{
    protected static string $resource = PaymentMethodResource::class;
}
