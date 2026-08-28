<?php

namespace Modules\Core\Filament\Admin\Resources\MerchantClients\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MerchantClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Company')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('driver')
                    ->label('Driver')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('merchant_key')
                    ->label('Key')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('label')
                    ->label('Label')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()->modalWidth('full'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
