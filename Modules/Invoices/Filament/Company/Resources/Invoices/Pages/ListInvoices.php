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
                Action::make('exportCsv')
                    ->label('Export as CSV')
                    ->icon('heroicon-o-document-text')
                    ->action(function () {
                        return app(InvoiceExportService::class)->export('csv');
                    }),
                Action::make('exportExcel')
                    ->label('Export as Excel')
                    ->icon('heroicon-o-document')
                    ->action(function () {
                        return app(InvoiceExportService::class)->export('xlsx');
                    }),
            ])
                ->label('Export')
                ->icon(Heroicon::OutlinedFolderArrowDown)
                ->button(),
        ];
    }
}
