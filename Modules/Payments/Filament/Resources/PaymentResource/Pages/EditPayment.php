<?php

namespace Modules\Payments\Filament\Resources\PaymentResource\Pages;

use Filament\Resources\Pages\Page;
use Modules\Payments\Filament\Resources\PaymentResource;

class EditPayment extends Page
{
    protected static string $resource = PaymentResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
