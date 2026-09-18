<?php

namespace Modules\Clients\Filament\Company\Resources\Relations\RelationManagers;

use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Filament\Company\Resources\Invoices\InvoiceResource;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')->sortable()->searchable(),
                TextColumn::make('invoice_status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ($state instanceof InvoiceStatus ? $state : InvoiceStatus::tryFrom($state))?->label())
                    ->color(fn ($state) => ($state instanceof InvoiceStatus ? $state : InvoiceStatus::tryFrom($state))?->color() ?? 'secondary')
                    ->sortable(),
                TextColumn::make('invoiced_at')->date()->sortable(),
                TextColumn::make('invoice_due_at')->date()->sortable(),
                TextColumn::make('invoice_total')->money()->sortable(),
            ])
            ->headerActions([
                Action::make('create_invoice')
                    ->label(trans('ip.create_invoice'))
                    ->url(fn (): string => InvoiceResource::getUrl('create', [
                        'customer_id' => $this->getOwnerRecord()->id,
                    ])),
            ]);
    }
}
