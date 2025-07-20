<?php

namespace Modules\Expenses\Filament\Company\Resources\Expenses\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Helpers\EnumHelper;
use Modules\Expenses\Enums\ExpenseStatus;
use Modules\Expenses\Enums\ExpenseType;

class ExpensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('expense_status')
                    ->formatStateUsing(function ($state) {
                        $status = EnumHelper::safeEnum(ExpenseStatus::class, $state);

                        return $status?->label() ?? '-';
                    })
                    ->color(function ($state) {
                        $status = EnumHelper::safeEnum(ExpenseStatus::class, $state);

                        return $status?->color() ?? 'secondary';
                    })
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('expenseCategory.category_name')
                    ->limit(10)
                    ->placeholder('-')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->hiddenFrom('sm'),
                TextColumn::make('expense_type')
                    ->formatStateUsing(function ($state) {
                        $status = EnumHelper::safeEnum(ExpenseType::class, $state);

                        return $status?->label() ?? '-';
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->hiddenFrom('sm'),
                TextColumn::make('expense_number')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->hiddenFrom('sm'),
                TextColumn::make('vendor.company_name')->limit(10)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('expensed_at')
                    ->date()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('expense_amount')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->mutateDataUsing(function (array $data, Expense $record) {
                            $data['expenseItems'] = $record->expenseItems()->get()->map(function ($item) {
                                $product = $item->product;

                                return [
                                    'id'            => $item->id,
                                    'item_id'       => $item->item_id,
                                    'item_name'     => $item->item_name,
                                    'quantity'      => $item->quantity,
                                    'price'         => $item->price,
                                    'discount'      => $item->discount,
                                    'subtotal'      => $item->subtotal,
                                    'tax_1'         => $item->tax_1,
                                    'tax_2'         => $item->tax_2,
                                    'tax_rate_id'   => $item->tax_rate_id,
                                    'tax_rate_2_id' => $item->tax_rate_2_id,
                                    'description'   => $item->description,
                                ];
                            })->toArray();

                            return $data;
                        })
                        ->action(function (Expense $record, array $data) {
                            app(ExpenseService::class)->updateExpense($record, $data);
                        })
                        ->modalWidth('full'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
