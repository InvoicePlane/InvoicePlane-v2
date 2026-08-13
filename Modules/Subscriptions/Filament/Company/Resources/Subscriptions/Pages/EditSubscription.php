<?php

namespace Modules\Subscriptions\Filament\Company\Resources\Subscriptions\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Subscriptions\Filament\Company\Resources\Subscriptions\SubscriptionResource;

class EditSubscription extends EditRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
