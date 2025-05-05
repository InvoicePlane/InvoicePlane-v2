<?php

namespace Modules\Expenses\Filament\Company\Resources;

use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource\Pages;
use Modules\Expenses\Models\ExpenseCategory;

class ExpenseCategoryResource extends Resource
{
    protected static ?string $model = ExpenseCategory::class;

    protected static ?string $navigationGroup = 'Expenses';

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(1)
                    ->schema([
                        Group::make()
                            ->schema([
                                Forms\Components\TextInput::make('category_name')
                                    ->label(trans('ip.expense_category'))
                                    ->inlineLabel()
                                    ->autofocus()
                                    ->required(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category_name')->searchable()->sortable()->toggleable(),
            ])
            ->filters([
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                ]),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * No belongsTo relationships auto-detected.
     */
    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenseCategories::route('/'),
        ];
    }
}
