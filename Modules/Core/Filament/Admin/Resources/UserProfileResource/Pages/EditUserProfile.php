<?php

namespace Modules\Core\Filament\Admin\Resources\UserProfileResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Core\Filament\Admin\Resources\UserProfileResource;

class EditUserProfile extends EditRecord
{
    protected static string $resource = UserProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
