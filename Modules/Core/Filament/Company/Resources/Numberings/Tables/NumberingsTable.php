<?php

namespace Modules\Core\Filament\Company\Resources\Numberings\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NumberingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('format')
                    ->label('Format')
                    ->searchable(),

                TextColumn::make('next_id')
                    ->label('Next ID')
                    ->sortable(),

                TextColumn::make('left_pad')
                    ->label('Padding'),
            ])
            ->defaultSort('type', 'asc');
    }
}
