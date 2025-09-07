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
                Action::make('exportCsv')
                    ->label('Export as CSV')
                    ->icon('heroicon-o-document-text')
                    ->action(function () {
                        return app(PaymentExportService::class)->export('csv');
                    }),
                Action::make('exportExcel')
                    ->label('Export as Excel')
                    ->icon('heroicon-o-document')
                    ->action(function () {
                        return app(PaymentExportService::class)->export('xlsx');
                    }),
            ])
                ->label('Export')
                ->icon(Heroicon::OutlinedFolderArrowDown)
                ->button(),
        ];
    }
}
