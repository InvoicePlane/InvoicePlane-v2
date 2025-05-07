<?php

namespace Modules\Payments\Filament\Company\Resources\PaymentResource\Pages;

use Modules\Core\Support\Results\Payments;

use Modules\Core\Models\Company;

use Modules\Payments\Filament\Company\Resources\PaymentResource\Pages\CreatePayment;

use Modules\Payments\Filament\Company\Resources\PaymentResource;

use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
