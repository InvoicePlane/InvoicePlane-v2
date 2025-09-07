<?php

namespace Modules\Products\Filament\Company\Resources\Products\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Modules\Products\Filament\Company\Resources\Products\ProductResource;
use Modules\Products\Services\ProductExportService;
use Modules\Products\Services\ProductService;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data) {
                    return $data;
                })
                ->action(function (array $data) {
                    app(ProductService::class)->createProduct($data);
                })->modalWidth('full'),

            ActionGroup::make([
                Action::make('exportCsv')
                    ->label('Export as CSV')
                    ->icon('heroicon-o-document-text')
                    ->action(function () {
                        return app(ProductExportService::class)->export('csv');
                    }),
                Action::make('exportExcel')
                    ->label('Export as Excel')
                    ->icon('heroicon-o-document')
                    ->action(function () {
                        return app(ProductExportService::class)->export('xlsx');
                    }),
            ])
                ->label('Export')
                ->icon(Heroicon::OutlinedFolderArrowDown)
                ->button(),
        ];
    }
}
