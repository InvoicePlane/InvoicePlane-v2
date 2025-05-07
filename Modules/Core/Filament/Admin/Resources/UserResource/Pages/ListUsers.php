<?php

namespace Modules\Core\Filament\Admin\Resources\UserResource\Pages;

use Modules\Core\Filament\Admin\Resources\UserResource\Pages\ListUsers;

use Modules\Core\Filament\Admin\Resources\UserResource;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Filament\Admin\Resources\UserResource;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
