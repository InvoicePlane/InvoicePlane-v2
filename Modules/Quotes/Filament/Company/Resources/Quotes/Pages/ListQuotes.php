<?php

namespace Modules\Quotes\Filament\Company\Resources\Quotes\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Modules\Quotes\Filament\Company\Resources\Quotes\QuoteResource;
use Modules\Quotes\Services\QuoteExportService;
use Modules\Quotes\Services\QuoteService;

class ListQuotes extends ListRecords
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                /*->mutateDataUsing(function (array $data) {
                    return $data;
                })*/
                ->action(function (array $data) {
                    app(QuoteService::class)->createQuote($data);
                })
                ->modalWidth('full'),

            ActionGroup::make([
                Action::make('exportCsvV2')
                    ->label('Export as CSV (v2)')
                    ->icon('heroicon-o-document-text')
                    ->action(fn () => app(QuoteExportService::class)->export('csv')),
                Action::make('exportCsvV1')
                    ->label('Export as CSV (v1, Legacy)')
                    ->icon('heroicon-o-document-text')
                    ->action(fn () => app(QuoteExportService::class)->exportWithVersion('csv', 1)),
                Action::make('exportExcelV2')
                    ->label('Export as Excel (v2)')
                    ->icon('heroicon-o-document')
                    ->action(fn () => app(QuoteExportService::class)->export('xlsx')),
                Action::make('exportExcelV1')
                    ->label('Export as Excel (v1, Legacy)')
                    ->icon('heroicon-o-document')
                    ->action(fn () => app(QuoteExportService::class)->exportWithVersion('xlsx', 1)),
            ])
                ->label('Export')
                ->icon(Heroicon::OutlinedFolderArrowDown)
                ->button(),
        ];
    }
}
