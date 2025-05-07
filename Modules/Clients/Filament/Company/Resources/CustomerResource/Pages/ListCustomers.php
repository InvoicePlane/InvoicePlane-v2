<?php

namespace Modules\Clients\Filament\Company\Resources\CustomerResource\Pages;

use Modules\Clients\Filament\Company\Resources\CustomerResource\Pages\ListCustomers;

use Modules\Core\Support\Results\Clients;

use Modules\Core\Models\Company;

use Modules\Clients\Filament\Company\Resources\CustomerResource;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
