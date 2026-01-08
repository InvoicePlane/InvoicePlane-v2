<?php

namespace Modules\Invoices\Filament\Company\Resources\Invoices\Pages;

use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Modules\Invoices\Filament\Company\Resources\Invoices\InvoiceResource;
use Modules\Invoices\Filament\Exporters\InvoiceExporter;
use Modules\Invoices\Filament\Exporters\InvoiceLegacyExporter;
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
                ExportAction::make('exportCsvV2')
                    ->label('Export as CSV (v2)')
                    ->icon('heroicon-o-document-text')
                    ->exporter(InvoiceExporter::class)
                    ->formats([ExportFormat::Csv]),
                ExportAction::make('exportCsvV1')
                    ->label('Export as CSV (v1, Legacy)')
                    ->icon('heroicon-o-document-text')
                    ->exporter(InvoiceLegacyExporter::class)
                    ->formats([ExportFormat::Csv]),
                ExportAction::make('exportExcelV2')
                    ->label('Export as Excel (v2)')
                    ->icon('heroicon-o-document')
                    ->exporter(InvoiceExporter::class)
                    ->formats([ExportFormat::Xlsx]),
                ExportAction::make('exportExcelV1')
                    ->label('Export as Excel (v1, Legacy)')
                    ->icon('heroicon-o-document')
                    ->exporter(InvoiceLegacyExporter::class)
                    ->formats([ExportFormat::Xlsx]),
            ])
                ->label('Export')
                ->icon(Heroicon::OutlinedFolderArrowDown)
                ->button(),
        ];
    }
}
