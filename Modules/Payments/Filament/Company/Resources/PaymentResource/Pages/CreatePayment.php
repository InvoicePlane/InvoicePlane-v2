<?php

namespace Modules\Payments\Filament\Company\Resources\PaymentResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Payments\Filament\Company\Resources\PaymentResource;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
