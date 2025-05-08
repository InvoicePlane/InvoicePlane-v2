<?php

namespace Modules\Products\Filament\Company\Resources;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Products\Enums\ProductType;
use Modules\Products\Filament\Company\Resources\ProductResource\Pages\ListProducts;
use Modules\Products\Filament\Company\Resources\ProductResource\RelationManagers\InvoiceItemsRelationManager;
use Modules\Products\Filament\Company\Resources\ProductResource\RelationManagers\ProductCategoryRelationManager;
use Modules\Products\Filament\Company\Resources\ProductResource\RelationManagers\ProductUnitRelationManager;
use Modules\Products\Filament\Company\Resources\ProductResource\RelationManagers\QuoteItemsRelationManager;
use Modules\Products\Models\Product;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Resources';

    protected static ?int $navigationSort = 30;

    public static function getModelLabel(): string
    {
        return trans('ip.product');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.products');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.products');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        //
                        // LEFT COLUMN: basic details
                        //
                        DocumentGroup::make()
                            ->columnSpan(1)
                            ->schema([
                                Section::make(trans('ip.details'))
                                    ->schema([
                                        TextInput::make('code')
                                            ->label(trans('ip.code'))
                                            ->nullable()
                                            ->maxLength(255),

                                        TextInput::make('item_name')
                                            ->label(trans('ip.item_name'))
                                            ->required()
                                            ->maxLength(255),
                                    ]),
                            ]),

                        //
                        // RIGHT COLUMN: classification
                        //
                        DocumentGroup::make()
                            ->columnSpan(1)
                            ->schema([
                                Section::make(trans('ip.classification'))
                                    ->schema([
                                        Grid::make(2)->schema([
                                            Select::make('category_id')
                                                ->label(trans('ip.family'))
                                                ->relationship('category', 'category_name')
                                                ->searchable()
                                                ->preload()
                                                ->required(),

                                            Select::make('unit_id')
                                                ->label(trans('ip.product_unit'))
                                                ->relationship('productUnit', 'unit_name')
                                                ->searchable()
                                                ->preload(),

                                            TextInput::make('price')
                                                ->label(trans('ip.price'))
                                                ->numeric()
                                                ->required(),

                                            Select::make('tax_rate_id')
                                                ->label(trans('ip.tax_rate'))
                                                ->relationship('taxRate', 'name')
                                                ->searchable()
                                                ->preload(),
                                        ]),
                                    ]),
                            ]),
                    ]),

                //
                // DESCRIPTION / NOTES (collapsed)
                //
                Section::make(trans('ip.description'))
                    ->collapsed()
                    ->schema([
                        MarkdownEditor::make('description')
                            ->label(trans('ip.description'))
                            ->toolbarButtons(['bold', 'italic']),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category.category_name')->limit(10)->searchable()->sortable()->toggleable(),
                TextColumn::make('code')->searchable()->sortable()->toggleable(),
                TextColumn::make('product_name')->limit(10)->searchable()->sortable()->toggleable(),
                TextColumn::make('type')
                    ->formatStateUsing(fn ($state) => ($state instanceof ProductType ? $state : ProductType::tryFrom($state))?->label())
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('price')->searchable()->sortable()->toggleable(),
                TextColumn::make('productUnit.unit_name')->limit(5)->searchable()->sortable()->toggleable(),
                TextColumn::make('taxRate.name')->limit(5)->searchable()->sortable()->toggleable(),
            ])
            ->filters([])
            ->actions([
                ActionGroup::make([
                    EditAction::make()->modalWidth('7xl'),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('product_name', 'asc');
    }

    /**
     * - category (BelongsTo).
     */
    public static function getRelations(): array
    {
        return [
            ProductCategoryRelationManager::class,
            InvoiceItemsRelationManager::class,
            QuoteItemsRelationManager::class,
            ProductUnitRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
        ];
    }
}
