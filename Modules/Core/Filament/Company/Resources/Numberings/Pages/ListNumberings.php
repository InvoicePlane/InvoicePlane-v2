<?php

namespace Modules\Core\Filament\Company\Resources\Numberings\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Core\Filament\Company\Resources\Numberings\NumberingResource;

class ListNumberings extends ListRecords
{
    protected static string $resource = NumberingResource::class;

    protected static ?string $title = 'Numbering Schemes';
}
