<?php

namespace Modules\Core\Filament\Admin\Resources\UserProfileResource\Pages;

use Modules\Core\Filament\Admin\Resources\UserProfileResource;

use Modules\Core\Filament\Admin\Resources\UserProfileResource\Pages\ListUserProfiles;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserProfiles extends ListRecords
{
    protected static string $resource = UserProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
