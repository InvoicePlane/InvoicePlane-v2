<?php

namespace Modules\Core\Filament\Admin\Resources\Numberings\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Helpers\EnumHelper;
use Modules\Core\Models\Numbering;
use Modules\Core\Services\NumberingService;

class NumberingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->limit(10)
                    ->formatStateUsing(function ($state) {
                        if ($state instanceof NumberingType) {
                            return $state->label();
                        }

                        $type = EnumHelper::safeEnum(NumberingType::class, $state);

                        return $type?->label() ?? '-';
                    })
                    ->color(function ($state) {
                        if ($state instanceof NumberingType) {
                            return $state->color();
                        }

                        $type = EnumHelper::safeEnum(NumberingType::class, $state);

                        return $type?->color() ?? 'secondary';
                    })
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('name')
                    ->limit(10)->searchable()->sortable()->toggleable(),
                TextColumn::make('prefix')
                    ->searchable()->sortable()->toggleable(),
                TextColumn::make('left_pad')
                    ->numeric()
                    ->searchable()->sortable()->toggleable(),
                TextColumn::make('format')
                    ->limit(10)->searchable()->sortable()->toggleable(),
                TextColumn::make('next_id')
                    ->numeric()
                    ->searchable()->sortable()->toggleable(),
            ])
            ->filters([])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make('delete')
                        ->action(function (Numbering $record) {
                            app(NumberingService::class)->deleteNumbering($record);
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
