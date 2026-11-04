<?php

namespace Modules\Core\Filament\Admin\Resources\Numberings\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Filament\Admin\Resources\Numberings\NumberingResource;

class ListNumberings extends ListRecords
{
    protected static string $resource = NumberingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->url(fn (): string => NumberingResource::getUrl('create')),
        ];
    }
}
