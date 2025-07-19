<?php

namespace Modules\Quotes\Filament\Company\Widgets;

use Modules\Quotes\Filament\Company\Widgets\TableWidget as BaseWidget;

class LatestQuotes extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    /*
    public function table(Table $table): Table
    {
        return $table
            ->query(OrderResource::getEloquentQuery())
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('currency')
                    ->getStateUsing(fn ($record): ?string => Currency::query()->find($record->currency)?->name ?? null)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shipping_price')
                    ->label('Shipping cost')
                    ->searchable()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->url(fn (Order $record): string => OrderResource::getUrl('edit', ['record' => $record])),
            ]);
    }
    */
}
