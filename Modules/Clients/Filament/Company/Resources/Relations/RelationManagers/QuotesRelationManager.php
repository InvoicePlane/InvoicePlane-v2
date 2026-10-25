<?php

namespace Modules\Clients\Filament\Company\Resources\Relations\RelationManagers;

use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Filament\Company\Resources\Quotes\QuoteResource;

class QuotesRelationManager extends RelationManager
{
    protected static string $relationship = 'quotes';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return trans('ip.quotes');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quote_number')->label(fn (): string => trans('ip.quote_number'))->sortable()->searchable(),
                TextColumn::make('quote_status')
                    ->label(fn (): string => trans('ip.quote_status'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => ($state instanceof QuoteStatus ? $state : QuoteStatus::tryFrom($state))?->label())
                    ->color(fn ($state) => ($state instanceof QuoteStatus ? $state : QuoteStatus::tryFrom($state))?->color() ?? 'secondary')
                    ->sortable(),
                TextColumn::make('quoted_at')->label(fn (): string => trans('ip.quoted_at'))->date()->sortable(),
                TextColumn::make('quote_expires_at')->label(fn (): string => trans('ip.quote_expires_at'))->date()->sortable(),
                TextColumn::make('quote_total')->label(fn (): string => trans('fields.quote_total'))->money()->sortable(),
            ])
            ->headerActions([
                Action::make('create_quote')
                    ->label(trans('ip.create_quote'))
                    ->url(fn (): string => QuoteResource::getUrl('create', [
                        'customer_id' => $this->getOwnerRecord()->id,
                    ])),
            ]);
    }
}
