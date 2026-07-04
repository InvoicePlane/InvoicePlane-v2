<?php

namespace Modules\Expenses\Filament\Company\Resources\ExpenseCategories;

use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Enums\Permission;
use Modules\Core\Filament\Company\Resources\BaseResource;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategories\Pages\ListExpenseCategories;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategories\Schemas\ExpenseCategoryForm;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategories\Tables\ExpenseCategoriesTable;
use Modules\Expenses\Models\ExpenseCategory;

class ExpenseCategoryResource extends BaseResource
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExpenseCategories::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can(Permission::VIEW_EXPENSES->value) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can(Permission::CREATE_EXPENSES->value) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can(Permission::EDIT_EXPENSES->value) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can(Permission::DELETE_EXPENSES->value) ?? false;
    }
}
