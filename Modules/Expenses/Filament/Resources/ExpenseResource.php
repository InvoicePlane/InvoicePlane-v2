<?php

namespace Modules\Expenses\Filament\Resources;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Expenses\Filament\Resources\ExpenseResource\Pages;
use Modules\Expenses\Filament\Resources\ExpenseResource\RelationManagers\CategoryRelationManager;
use Modules\Expenses\Filament\Resources\ExpenseResource\RelationManagers\ClientRelationManager;
use Modules\Expenses\Filament\Resources\ExpenseResource\RelationManagers\InvoiceRelationManager;
use Modules\Expenses\Filament\Resources\ExpenseResource\RelationManagers\VendorRelationManager;
use Modules\Expenses\Models\Expense;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 15;

    public static function getModelLabel(): string
    {
        return trans('crud.expenses.itemTitle');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('crud.expenses.collectionTitle');
    }

    public static function getNavigationLabel(): string
    {
        return trans('crud.expenses.collectionTitle');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                Grid::make(['default' => 2])->schema([
                    /*Select::make('client_id')
                        ->required()
                        ->relationship('client', 'client_name')
                        ->searchable()
                        ->preload()
                        ->native(false),*/

                    Select::make('vendor_id')
                        ->required()
                        ->relationship('vendor', 'name')
                        ->searchable()
                        ->preload()
                        ->native(false),

                    Select::make('invoice_id')
                        ->required()
                        ->relationship('invoice', 'invoice_number')
                        ->searchable()
                        ->preload()
                        ->native(false),

                    Select::make('category_id')
                        ->required()
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->native(false),

                    DatePicker::make('expense_date')
                        ->rules(['date'])
                        ->required()
                        ->native(false),

                    TextInput::make('amount')
                        ->required()
                        ->string(),

                    MarkdownEditor::make('description')
                        ->toolbarButtons([
                            'bold',
                            'italic',
                        ])
                        ->columnSpan(2),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                TextColumn::make('client.client_name'),

                TextColumn::make('vendor.name'),

                TextColumn::make('category_id'),

                TextColumn::make('expense_date')->since(),

                TextColumn::make('amount'),
            ])
            ->filters([])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('expense_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            CategoryRelationManager::class,
            ClientRelationManager::class,
            InvoiceRelationManager::class,
            VendorRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageExpenses::route('/'),
        ];
    }
}
