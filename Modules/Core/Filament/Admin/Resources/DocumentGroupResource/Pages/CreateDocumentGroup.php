<?php

namespace Modules\Core\Filament\Admin\Resources\DocumentGroupResource\Pages;

use Modules\Core\Filament\Admin\Resources\DocumentGroupResource\Pages\CreateDocumentGroup;

use Modules\Core\Filament\Admin\Resources\DocumentGroupResource;

use Filament\Resources\Pages\CreateRecord;

class CreateDocumentGroup extends CreateRecord
{
    protected static string $resource = DocumentGroupResource::class;
}
