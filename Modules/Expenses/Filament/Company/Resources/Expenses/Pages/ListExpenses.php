<?php

namespace Modules\Expenses\Filament\Company\Resources\Expenses\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Modules\Expenses\Filament\Company\Resources\Expenses\ExpenseResource;
use Modules\Expenses\Services\ExpenseExportService;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data) {
                    return $data;
                })
                ->action(function (array $data) {
                    app(\Modules\Expenses\Services\ExpenseService::class)->createExpense($data);
                })
                ->modalWidth('full'),

            ActionGroup::make([
                Action::make('exportCsv')
                    ->label('Export as CSV')
                    ->icon('heroicon-o-document-text')
                    ->action(function () {
                        return app(ExpenseExportService::class)->export('csv');
                    }),
                Action::make('exportExcel')
                    ->label('Export as Excel')
                    ->icon('heroicon-o-document')
                    ->action(function () {
                        return app(ExpenseExportService::class)->export('xlsx');
                    }),
            ])
                ->label('Export')
                ->icon(Heroicon::OutlinedFolderArrowDown)
                ->button(),
        ];
    }
}
