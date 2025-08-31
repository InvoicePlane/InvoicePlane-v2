<?php

namespace Modules\Core\Filament\Admin\Resources\Companies\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Models\Company;
use Modules\Core\Services\CompanyService;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('search_code')->searchable()->sortable()->toggleable(),
                TextColumn::make('slug')->searchable()->sortable()->toggleable(),
                TextColumn::make('name')->limit(10)->searchable()->sortable()->toggleable(),
                TextColumn::make('vat_number')->limit(10)->searchable()->sortable()->toggleable(),
                TextColumn::make('id_number')->limit(10)->searchable()->sortable()->toggleable(),
                TextColumn::make('coc_number')->searchable()->sortable()->toggleable(),
            ])
            ->filters([
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make('edit')
                        ->action(function (Company $record, array $data) {
                            app(CompanyService::class)->updateCompany($record, $data);
                        })
                        ->modalWidth('full'),
                    DeleteAction::make('delete')
                        ->action(function (Company $record, array $data) {
                            app(CompanyService::class)->deleteCompany($record, $data);
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
