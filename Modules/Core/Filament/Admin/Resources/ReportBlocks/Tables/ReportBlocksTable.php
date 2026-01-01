<?php

namespace Modules\Core\Filament\Admin\Resources\ReportBlocks\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReportBlocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('block_type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('width')
                    ->sortable(),
                TextColumn::make('data_source')
                    ->sortable(),
                TextColumn::make('default_band')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_system')
                    ->boolean()
                    ->sortable(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
