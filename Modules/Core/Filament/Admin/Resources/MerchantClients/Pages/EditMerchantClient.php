<?php

namespace Modules\Core\Filament\Admin\Resources\MerchantClients\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Core\Filament\Admin\Resources\MerchantClients\MerchantClientResource;

class EditMerchantClient extends EditRecord
{
    protected static string $resource = MerchantClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
