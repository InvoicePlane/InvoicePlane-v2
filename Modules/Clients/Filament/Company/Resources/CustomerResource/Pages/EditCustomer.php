<?php

namespace Modules\Clients\Filament\Company\Resources\CustomerResource\Pages;

use Modules\Clients\Filament\Company\Resources\CustomerResource\Pages\EditCustomer;

use Modules\Core\Support\Results\Clients;

use Modules\Core\Models\Company;

use Modules\Clients\Filament\Company\Resources\CustomerResource;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Clients\Filament\Company\Resources\CustomerResource;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
