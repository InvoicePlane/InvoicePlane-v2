<?php

namespace Modules\Core\Filament\Admin\Resources\MerchantClients\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Core\Filament\Admin\Resources\MerchantClients\MerchantClientResource;

class CreateMerchantClient extends CreateRecord
{
    protected static string $resource = MerchantClientResource::class;
}
