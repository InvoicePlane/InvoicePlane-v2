<?php

namespace Modules\Payments\Filament\Company\Resources\Payments\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Modules\Payments\Filament\Company\Resources\Payments\PaymentResource;
use Modules\Payments\Services\PaymentExportService;
use Modules\Payments\Services\PaymentService;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data) {
                    return $data;
                })
                ->action(function (array $data) {
                    app(PaymentService::class)->createPayment($data);
                })
                ->modalWidth('full'),

            ActionGroup::make([
                Action::make('exportCsvV2')
                    ->label('Export as CSV (v2)')
                    ->icon('heroicon-o-document-text')
                    ->action(fn () => app(PaymentExportService::class)->export('csv')),
                Action::make('exportCsvV1')
                    ->label('Export as CSV (v1, Legacy)')
                    ->icon('heroicon-o-document-text')
                    ->action(fn () => app(PaymentExportService::class)->exportWithVersion('csv', 1)),
                Action::make('exportExcelV2')
                    ->label('Export as Excel (v2)')
                    ->icon('heroicon-o-document')
                    ->action(fn () => app(PaymentExportService::class)->export('xlsx')),
                Action::make('exportExcelV1')
                    ->label('Export as Excel (v1, Legacy)')
                    ->icon('heroicon-o-document')
                    ->action(fn () => app(PaymentExportService::class)->exportWithVersion('xlsx', 1)),
            ])
                ->label('Export')
                ->icon(Heroicon::OutlinedFolderArrowDown)
                ->button(),
        ];
    }
}
