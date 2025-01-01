<?php

namespace Modules\Payments\Filament\Resources\PaymentResource\Pages;

use Filament\Resources\Pages\Page;
use Modules\Payments\Filament\Resources\PaymentResource;

class CreatePayment extends Page
{
    protected static string $resource = PaymentResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill();

        parent::create($another);
    }
}
