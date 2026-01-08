<?php

namespace Modules\Core\Filament\Admin\Resources\MerchantClients\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Filament\Admin\Resources\MerchantClients\MerchantClientResource;

class ListMerchantClients extends ListRecords
{
    protected static string $resource = MerchantClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
