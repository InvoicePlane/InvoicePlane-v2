<?php

namespace Modules\Quotes\Filament\Company\Widgets;

use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Modules\Core\Support\DateHelpers;
use Modules\Quotes\Filament\Company\Resources\Quotes\QuoteResource;
use Modules\Quotes\Models\Quote;

class RecentQuotesWidget extends TableWidget
{
    protected static ?int $sort = 1;

    public static function getHeading(): ?string
    {
        return trans('ip.recent_quotes');
    }

    public function getTableHeaderActions(): array
    {
        return [
            Action::make('view_all')
                ->label(trans('ip.view_all'))
                ->url(QuoteResource::getUrl('index'))
                ->icon('heroicon-o-arrow-right')
                ->color('primary'),
        ];
    }

    public function table(Table $table): Table
    {
        // QuoteResource only registers an 'index' page — editing happens via
        // a modal action on that page's table, not a dedicated edit/view
        // page — so this is the most specific URL a row can link to.
        return parent::table($table)
            ->recordUrl(fn (Quote $record): string => QuoteResource::getUrl('index'));
    }

    protected function getTableQuery(): Builder|Relation|null
    {
        /** @var Builder<Quote> $query */
        $query = Quote::query()->recent();

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('quote_status')
                ->label(trans('ip.quote_status'))
                ->badge()
                ->formatStateUsing(fn ($state) => $state?->label() ?? '-')
                ->color(fn ($state) => $state?->color() ?? 'secondary'),
            TextColumn::make('quote_number')->label(trans('ip.quote_number')),
            TextColumn::make('prospect.company_name')->limit(10)->label(trans('ip.prospect_name')),
            TextColumn::make('quote_expires_at')
                ->label(trans('ip.quote_expires_at'))
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
                }),
        ];
    }
}
