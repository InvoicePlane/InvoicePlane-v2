<?php

namespace Modules\Payments\Filament\Company\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Modules\Payments\Filament\Company\Resources\Payments\PaymentResource;
use Modules\Payments\Models\Payment;

class RecentPaymentsWidget extends TableWidget
{
    protected static ?string $heading = 'Recent Payments';

    protected static ?int $sort = 6;

    public function table(Table $table): Table
    {
        // PaymentResource only registers an 'index' page — editing happens
        // via a modal action on that page's table, not a dedicated edit/view
        // page — so this is the most specific URL a row can link to.
        return parent::table($table)
            ->recordUrl(fn (Payment $record): string => PaymentResource::getUrl('index'));
    }

    protected function getTableQuery(): Builder|Relation|null
    {
        /** @var Builder<Payment> $query */
        $query = Payment::query()->latest()->limit(10);

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('paid_at')->label(trans('ip.paid_at'))->date(),
            TextColumn::make('invoice.invoice_number')->label(trans('ip.payment_reference')),
            TextColumn::make('amount')->label(trans('ip.amount')),
        ];
    }
}
