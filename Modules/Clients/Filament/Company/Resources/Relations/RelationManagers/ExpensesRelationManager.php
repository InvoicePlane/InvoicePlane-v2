<?php

namespace Modules\Clients\Filament\Company\Resources\Relations\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExpensesRelationManager extends RelationManager
{
    protected static string $relationship = 'expenses';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('expense_number')->sortable()->searchable(),
                TextColumn::make('expensed_at')->date()->sortable(),
                TextColumn::make('description')->limit(40)->searchable(),
                TextColumn::make('expense_amount')->money()->sortable(),
            ]);
    }
}
