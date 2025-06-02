<?php

namespace Modules\Expenses\Filament\Company\Resources\Expenses;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Expenses\Filament\Company\Resources\Expenses\Pages\ListExpenses;
use Modules\Expenses\Filament\Company\Resources\Expenses\Schemas\ExpenseForm;
use Modules\Expenses\Filament\Company\Resources\Expenses\Tables\ExpensesTable;
use Modules\Expenses\Models\Expense;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 10;

    protected static bool $shouldRegisterNavigation = true;

    protected static bool $isScopedToTenant = true;

    public static function getModelLabel(): string
    {
        return trans('ip.expense');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.expenses');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.expenses');
    }

    public static function form(Schema $schema): Schema
    {
        return ExpenseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExpensesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExpenses::route('/'),
        ];
    }
}
