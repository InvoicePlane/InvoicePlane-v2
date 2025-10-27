<?php

namespace Modules\Invoices\Filament\Company\Resources\Invoices\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Modules\Invoices\Filament\Company\Resources\Invoices\InvoiceResource;
use Modules\Invoices\Services\InvoiceExportService;
use Modules\Invoices\Services\InvoiceService;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('full')
                ->mutateDataUsing(function (array $data) {
                    return $data;
                })
                ->action(function (array $data) {
                    app(InvoiceService::class)->createInvoice($data);
                }),
            ActionGroup::make([
                Action::make('exportCsvV2')
                    ->label('Export as CSV (v2)')
                    ->icon('heroicon-o-document-text')
                    ->action(fn () => app(InvoiceExportService::class)->export('csv')),
                Action::make('exportCsvV1')
                    ->label('Export as CSV (v1, Legacy)')
                    ->icon('heroicon-o-document-text')
                    ->action(fn () => app(InvoiceExportService::class)->exportWithVersion('csv', 1)),
                Action::make('exportExcelV2')
                    ->label('Export as Excel (v2)')
                    ->icon('heroicon-o-document')
                    ->action(fn () => app(InvoiceExportService::class)->export('xlsx')),
                Action::make('exportExcelV1')
                    ->label('Export as Excel (v1, Legacy)')
                    ->icon('heroicon-o-document')
                    ->action(fn () => app(InvoiceExportService::class)->exportWithVersion('xlsx', 1)),
            ])
                ->label('Export')
                ->icon('heroicon-o-folder-arrow-down')
                ->button(),
        ];
    }
}
