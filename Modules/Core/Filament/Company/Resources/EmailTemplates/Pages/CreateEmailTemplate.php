<?php

namespace Modules\Core\Filament\Company\Resources\EmailTemplates\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Core\Filament\Company\Resources\EmailTemplates\EmailTemplateResource;

class CreateEmailTemplate extends CreateRecord
{
    protected static string $resource = EmailTemplateResource::class;
}
