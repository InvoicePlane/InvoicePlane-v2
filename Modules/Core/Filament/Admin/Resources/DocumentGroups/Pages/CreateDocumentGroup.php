<?php

namespace Modules\Core\Filament\Admin\Resources\DocumentGroups\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Core\Filament\Admin\Resources\DocumentGroups\DocumentGroupResource;

class CreateDocumentGroup extends CreateRecord
{
    protected static string $resource = DocumentGroupResource::class;
}
