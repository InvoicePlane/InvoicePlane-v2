<?php

namespace Modules\Core\Filament\Admin\Resources\ReportBlocks\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Modules\Core\Filament\Admin\Resources\ReportBlocks\ReportBlockResource;
use Modules\Core\Services\ReportBlockService;

class ListReportBlocks extends ListRecords implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string $resource = ReportBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->action(function (array $data) {
                    app(ReportBlockService::class)->createReportBlock($data);
                })
                ->modalWidth('full'),
        ];
    }
}
