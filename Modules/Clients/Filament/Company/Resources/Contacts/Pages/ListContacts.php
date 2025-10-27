<?php

namespace Modules\Clients\Filament\Company\Resources\Contacts\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Modules\Clients\Filament\Company\Resources\Contacts\ContactResource;
use Modules\Clients\Services\ContactExportService;
use Modules\Clients\Services\ContactService;

class ListContacts extends ListRecords
{
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->action(function (array $data) {
                    app(ContactService::class)->createContact($data);
                })
                ->modalWidth('full'),
            ActionGroup::make([
                Action::make('exportCsvV2')
                    ->label('Export as CSV (v2)')
                    ->icon('heroicon-o-document-text')
                    ->action(fn () => app(ContactExportService::class)->export('csv')),
                Action::make('exportCsvV1')
                    ->label('Export as CSV (v1, Legacy)')
                    ->icon('heroicon-o-document-text')
                    ->action(fn () => app(ContactExportService::class)->exportWithVersion('csv', 1)),
                Action::make('exportExcelV2')
                    ->label('Export as Excel (v2)')
                    ->icon('heroicon-o-document')
                    ->action(fn () => app(ContactExportService::class)->export('xlsx')),
                Action::make('exportExcelV1')
                    ->label('Export as Excel (v1, Legacy)')
                    ->icon('heroicon-o-document')
                    ->action(fn () => app(ContactExportService::class)->exportWithVersion('xlsx', 1)),
            ])
                ->label('Export')
                ->icon('heroicon-o-folder-arrow-down')
                ->button(),
        ];
    }
}
