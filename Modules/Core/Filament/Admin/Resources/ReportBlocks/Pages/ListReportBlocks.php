<?php

namespace Modules\Core\Filament\Admin\Resources\ReportBlocks\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Filament\Admin\Resources\ReportBlocks\ReportBlockResource;

class ListReportBlocks extends ListRecords
{
    protected static string $resource = ReportBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
