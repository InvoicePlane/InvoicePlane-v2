<?php

namespace Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Enums\Permission;
use Modules\Core\Helpers\EnumHelper;
use Modules\Invoices\Enums\RecurringFrequency;

class RecurringInvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice.invoice_number')->searchable()->sortable()->toggleable(),
                TextColumn::make('frequency')
                    ->formatStateUsing(function ($state) {
                        $status = EnumHelper::safeEnum(RecurringFrequency::class, $state);

                        return $status?->label() ?? '-';
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('customer.company_name')->limit(10)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('start_at')
                    ->date()
                    ->since()->searchable()->sortable()->toggleable(),
                TextColumn::make('end_at')
                    ->date()
                    ->since()->searchable()->sortable()->toggleable(),
            ])
            ->filters([
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->visible(fn () => auth()->user()?->can(Permission::EDIT_INVOICES->value))
                        ->modalWidth('full'),
                    DeleteAction::make()
                        ->visible(fn () => auth()->user()?->can(Permission::DELETE_INVOICES->value)),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can(Permission::DELETE_INVOICES->value)),
                ]),
            ]);
    }
}
