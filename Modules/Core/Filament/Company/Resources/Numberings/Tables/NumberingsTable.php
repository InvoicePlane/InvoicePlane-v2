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
                    ->label(trans('ip.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label(trans('ip.type'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('format')
                    ->label(trans('ip.format'))
                    ->searchable(),

                TextColumn::make('next_id')
                    ->label(trans('ip.next_id'))
                    ->sortable(),

                TextColumn::make('left_pad')
                    ->label(trans('ip.padding')),
            ])
            ->defaultSort('type', 'asc');
    }
}
