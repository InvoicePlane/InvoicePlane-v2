<?php

namespace Modules\Quotes\Filament\Company\Resources\Quotes\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
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
                    EditAction::make()
                        ->mutateDataUsing(function (array $data, Quote $record) {
                            $data['quoteItems'] = $record->quoteItems()->get()->map(function ($item) {
                                $product = $item->product;

                                return [
                                    'product_id'   => $item->product_id,
                                    'product_name' => $product?->product_name ?? '', // Populate product_name
                                    'item_name'    => $item->item_name,
                                    'quantity'     => $item->quantity,
                                    'price'        => $item->price,
                                    'discount'     => $item->discount,
                                    'subtotal'     => $item->subtotal,
                                ];
                            })->toArray();

                            return $data;
                        })
                        ->action(function (Quote $record, array $data) {
                            app(QuoteService::class)->updateQuote($record, $data);
                        })
                        ->modalWidth('full'),
                    Action::make('download pdf')
                        ->label(trans('ip.download_pdf'))
                        ->modalDescription(
                            'todo: make sure we can download the PDF of the Quote through an action,
                            so need for modal anymore'
                        )
                        ->action(function (Quote $record): void {}),
                    Action::make('send email')
                        ->label(trans('ip.send_email'))
                        ->modalDescription('todo: make sure we can email the Quote through an action,
                            so need for modal anymore')
                        ->action(function (Quote $record): void {}),
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
