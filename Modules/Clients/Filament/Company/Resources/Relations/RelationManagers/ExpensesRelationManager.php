<?php

namespace Modules\Clients\Filament\Company\Resources\Relations\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExpensesRelationManager extends RelationManager
{
    protected static string $relationship = 'expenses';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return trans('ip.expenses');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('expense_number')->label(fn (): string => trans('ip.expense_number'))->sortable()->searchable(),
                TextColumn::make('expensed_at')->label(fn (): string => trans('ip.expensed_at'))->date()->sortable(),
                TextColumn::make('description')->label(fn (): string => trans('ip.expense_description'))->limit(40)->searchable(),
                TextColumn::make('expense_amount')->label(fn (): string => trans('ip.amount'))->money()->sortable(),
            ]);
    }
}
