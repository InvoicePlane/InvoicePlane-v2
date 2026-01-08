<?php

namespace Modules\Core\Filament\Admin\Resources\TaxRates\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Enums\TaxRateType;
use Modules\Core\Helpers\EnumHelper;

class TaxRatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tax_rate_type')
                    ->formatStateUsing(function ($state) {
                        $status = EnumHelper::safeEnum(TaxRateType::class, $state);

                        return $status?->label() ?? '-';
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('name')
                    ->label(trans('ip.name'))
                    ->limit(10)
                    ->searchable()->sortable()->toggleable(),
                TextColumn::make('code')
                    ->label(trans('ip.code'))
                    ->searchable()->sortable()->toggleable(),
                IconColumn::make('is_compound')
                    ->label(trans('ip.is_compound'))
                    ->boolean()->searchable()->sortable()->toggleable(),
                IconColumn::make('calculate_vat')
                    ->label(trans('ip.calculate_vat'))
                    ->boolean()->searchable()->sortable()->toggleable(),
                TextColumn::make('rate')
                    ->label(trans('ip.percentage'))
                    ->numeric()
                    ->searchable()->sortable()->toggleable(),
            ])
            ->filters([
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()->modalWidth('full'),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }
}
