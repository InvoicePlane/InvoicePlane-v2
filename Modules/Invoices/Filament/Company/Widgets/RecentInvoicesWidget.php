<?php

namespace Modules\Invoices\Filament\Company\Widgets;

use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Modules\Core\Support\DateHelpers;
use Modules\Invoices\Filament\Company\Resources\Invoices\InvoiceResource;
use Modules\Invoices\Models\Invoice;

class RecentInvoicesWidget extends TableWidget
{
    protected static ?int $sort = 1;

    public static function getHeading(): ?string
    {
        return trans('ip.recent_invoices');
    }

    public function getTableHeaderActions(): array
    {
        return [
            Action::make('view_all')
                ->label(trans('ip.view_all'))
                ->url(InvoiceResource::getUrl('index'))
                ->icon('heroicon-o-arrow-right')
                ->color('primary'),
        ];
    }

    public function table(Table $table): Table
    {
        // InvoiceResource only registers an 'index' page — editing happens
        // via a modal action on that page's table, not a dedicated edit/view
        // page — so this is the most specific URL a row can link to.
        return parent::table($table)
            ->recordUrl(fn (Invoice $record): string => InvoiceResource::getUrl('index'));
    }

    protected function getTableQuery(): Builder|Relation|null
    {
        /** @var Builder<Invoice> $query */
        $query = Invoice::query()->recent();

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('invoice_status')
                ->label(trans('ip.invoice_status'))
                ->badge()
                ->formatStateUsing(fn ($state) => $state?->label() ?? '-')
                ->color(fn ($state) => $state?->color() ?? 'secondary'),
            TextColumn::make('invoice_number')->label(trans('ip.invoice_number')),
            TextColumn::make('customer.company_name')->limit(10)->label(trans('ip.customer_name')),
            TextColumn::make('invoice_due_at')
                ->label(trans('ip.invoice_due_at'))
                ->color(fn ($state, $record) => $record?->due_intensity ?? 'secondary')
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
