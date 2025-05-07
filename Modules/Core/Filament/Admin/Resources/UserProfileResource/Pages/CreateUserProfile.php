<?php

namespace Modules\Core\Filament\Admin\Resources\UserProfileResource\Pages;

use Modules\Core\Filament\Admin\Resources\UserProfileResource\Pages\CreateUserProfile;

use Modules\Core\Filament\Admin\Resources\UserProfileResource;

use Filament\Resources\Pages\CreateRecord;

class CreateUserProfile extends CreateRecord
{
    protected static string $resource = UserProfileResource::class;
}
