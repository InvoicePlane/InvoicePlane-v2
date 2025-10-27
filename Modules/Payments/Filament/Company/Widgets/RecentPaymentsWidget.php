<?php

namespace Modules\Payments\Filament\Company\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class RecentPaymentsWidget extends TableWidget
{
    protected static ?string $heading = 'Recent Payments';

    protected static ?int $sort = 6;

    protected function getTableQuery(): Builder|Relation|null
    {
        return \Modules\Payments\Models\Payment::query()->latest()->limit(10);
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
