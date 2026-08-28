<?php

namespace Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Invoices\Enums\PeppolConnectionStatus;

class PeppolIntegrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Company')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('provider_name')
                    ->label('Provider')
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state)))
                    ->sortable()
                    ->searchable(),

                IconColumn::make('enabled')
                    ->label('Enabled')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('test_connection_status')
                    ->label('Connection Status')
                    ->badge()
                    ->formatStateUsing(fn (?PeppolConnectionStatus $state): ?string => $state?->label())
                    ->color(fn (?PeppolConnectionStatus $state): ?string => match ($state) {
                        PeppolConnectionStatus::SUCCESS => 'success',
                        PeppolConnectionStatus::FAILED => 'danger',
                        null => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('test_connection_at')
                    ->label('Last Tested')
                    ->dateTime()
                    ->sortable(),
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
