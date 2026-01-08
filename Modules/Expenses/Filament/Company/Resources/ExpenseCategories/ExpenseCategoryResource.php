<?php

namespace Modules\Expenses\Filament\Company\Resources\ExpenseCategories;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategories\Pages\ListExpenseCategories;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategories\Schemas\ExpenseCategoryForm;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategories\Tables\ExpenseCategoriesTable;
use Modules\Expenses\Models\ExpenseCategory;

class ExpenseCategoryResource extends Resource
{
    protected static ?string $model = ExpenseCategory::class;

    //heroicon-o-archive-box
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static ?int $navigationSort = 20;

    protected static bool $shouldRegisterNavigation = true;

    protected static bool $isScopedToTenant = true;

    public static function getModelLabel(): string
    {
        return trans('ip.expense_category');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.expense_categories');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.expense_categories');
    }

    public static function form(Schema $schema): Schema
    {
        return ExpenseCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExpenseCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExpenseCategories::route('/'),
        ];
    }
}
