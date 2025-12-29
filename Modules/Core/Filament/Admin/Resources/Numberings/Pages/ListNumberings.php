<?php

namespace Modules\Core\Filament\Admin\Resources\Numberings\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Filament\Admin\Resources\Numberings\NumberingResource;
use Modules\Core\Services\NumberingService;

class ListNumberings extends ListRecords
{
    protected static string $resource = NumberingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data) {
                    return $data;
                })
                ->action(function (array $data) {
                    app(NumberingService::class)->createNumbering($data);
                })
                ->modalWidth('full'),
        ];
    }
}
