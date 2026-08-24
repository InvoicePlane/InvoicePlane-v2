<?php

namespace Modules\Expenses\Filament\Company\Resources\Expenses\Pages;

use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Modules\Expenses\Filament\Company\Resources\Expenses\ExpenseResource;
use Modules\Expenses\Filament\Exporters\ExpenseExporter;
use Modules\Expenses\Filament\Exporters\ExpenseLegacyExporter;
use Modules\Expenses\Services\ExpenseService;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => auth()->user()?->can(Permission::CREATE_EXPENSES->value))
                ->mutateDataUsing(function (array $data) {
                    return $data;
                })
                ->action(function (array $data) {
                    app(ExpenseService::class)->createExpense($data);
                })
                ->modalWidth('full'),

            ActionGroup::make([
                ExportAction::make('exportCsvV2')
                    ->label('Export as CSV (v2)')
                    ->icon('heroicon-o-document-text')
                    ->exporter(ExpenseExporter::class)
                    ->formats([ExportFormat::Csv]),
                ExportAction::make('exportCsvV1')
                    ->label('Export as CSV (v1, Legacy)')
                    ->icon('heroicon-o-document-text')
                    ->exporter(ExpenseLegacyExporter::class)
                    ->formats([ExportFormat::Csv]),
                ExportAction::make('exportExcelV2')
                    ->label('Export as Excel (v2)')
                    ->icon('heroicon-o-document')
                    ->exporter(ExpenseExporter::class)
                    ->formats([ExportFormat::Xlsx]),
                ExportAction::make('exportExcelV1')
                    ->label('Export as Excel (v1, Legacy)')
                    ->icon('heroicon-o-document')
                    ->exporter(ExpenseLegacyExporter::class)
                    ->formats([ExportFormat::Xlsx]),
            ])
                ->label('Export')
                ->icon(Heroicon::OutlinedFolderArrowDown)
                ->button(),
        ];
    }
}
