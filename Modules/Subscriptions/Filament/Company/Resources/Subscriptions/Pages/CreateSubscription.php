<?php

namespace Modules\Subscriptions\Filament\Company\Resources\Subscriptions\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Subscriptions\Filament\Company\Resources\Subscriptions\SubscriptionResource;
use Modules\Subscriptions\Services\SubscriptionService;

class CreateSubscription extends CreateRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return app(SubscriptionService::class)->createSubscription($data);
    }
}
