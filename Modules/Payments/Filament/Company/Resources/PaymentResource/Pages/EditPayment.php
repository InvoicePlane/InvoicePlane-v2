<?php

namespace Modules\Payments\Filament\Company\Resources\PaymentResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Payments\Filament\Company\Resources\PaymentResource;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
