<?php

namespace Modules\Expenses\Filament\Company\Resources\ExpenseCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ExpenseCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->schema([
                        Schemas\Components\Group::make()
                            ->schema([
                                TextInput::make('category_name')
                                    ->label(trans('ip.expense_category'))
                                    ->inlineLabel()
                                    ->autofocus()
                                    ->required()
                                    // expense_categories.category_name is
                                    // varchar(50) — without this, a longer
                                    // value blows up as an unhandled SQL 500.
                                    ->maxLength(50),
                            ]),
                    ]),
            ]);
    }
}
