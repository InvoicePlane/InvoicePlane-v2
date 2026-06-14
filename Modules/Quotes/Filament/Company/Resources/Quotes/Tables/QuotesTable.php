<?php

namespace Modules\Quotes\Filament\Company\Resources\Quotes\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Helpers\EnumHelper;
use Modules\Core\Support\DateHelpers;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Services\QuoteService;

class QuotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quote_status')
                    ->label(trans('ip.quote_status'))
                    ->badge()
                    ->formatStateUsing(function (Quote $record) {
                        $status = EnumHelper::safeEnum(QuoteStatus::class, $record->quote_status);

                        return $status ? trans($status->label()) : '-';
                    })
                    ->color(function (Quote $record) {
                        $status = EnumHelper::safeEnum(QuoteStatus::class, $record->quote_status);

                        return $status?->color() ?? 'secondary';
                    }),
                TextColumn::make('quote_number')->searchable()->sortable()->toggleable(),
                TextColumn::make('prospect.company_name')
                    ->limit(10)
                    ->label(trans('ip.customer_name'))
                    ->searchable()->sortable()
                    ->toggleable(),
                TextColumn::make('quote_expires_at')
                    ->label(trans('ip.expires_at'))
                    ->color(fn ($state, $record) => $record?->expires_intensity ?? 'secondary')
                    ->formatStateUsing(function ($state) {
                        if ( ! $state) {
                            return '-';
                        }
                        $days = now()->diffInDays($state, false);
                        if ($days < 0) {
                            return DateHelpers::formatSince($state, 3600);
                        }

                        return DateHelpers::formatDate($state);
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('quote_total')->searchable()->sortable()->toggleable(),
            ])
            ->filters([])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make('delete')
                        ->action(function (Quote $quote) {
                            app(QuoteService::class)->deleteQuote($quote);
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('quote_expires_at', 'asc');
    }
}
