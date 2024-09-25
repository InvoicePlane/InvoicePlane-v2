<?php

namespace Modules\Products\Filament\Resources;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Products\Filament\Resources\ProductResource\Pages;
use Modules\Products\Filament\Resources\ProductResource\RelationManagers;
use Modules\Products\Models\Product;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 50;

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
        return $form->schema([
            Section::make()->schema([
                Grid::make(['default' => 2])->schema([
                    TextInput::make('family_id')
                        ->required()
                        ->numeric()
                        ->step(1)
                        ->autofocus(),

                    TextInput::make('product_sku')
                        ->nullable()
                        ->string(),

                    TextInput::make('product_name')
                        ->nullable()
                        ->string(),

                    MarkdownEditor::make('product_description')
                        ->toolbarButtons([
                            'bold',
                            'italic',
                        ])
                        ->columnSpan(2),

                    TextInput::make('product_price')
                        ->nullable()
                        ->numeric()
                        ->step(1),

                    TextInput::make('purchase_price')
                        ->nullable()
                        ->numeric()
                        ->step(1),

                    TextInput::make('provider_name')
                        ->nullable()
                        ->string(),

                    TextInput::make('tax_rate_id')
                        ->required()
                        ->numeric()
                        ->step(1),

                    TextInput::make('unit_id')
                        ->required()
                        ->numeric()
                        ->step(1),

                    TextInput::make('product_tariff')
                        ->nullable()
                        ->numeric()
                        ->step(1),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                TextColumn::make('productFamily.family_name'),

                TextColumn::make('product_sku'),

                TextColumn::make('product_name'),

                TextColumn::make('product_price'),

                TextColumn::make('productUnit.unit_name'),

                TextColumn::make('taxRate.tax_rate_name'),
            ])
            ->filters([])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('product_name', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\FamilyRelationManager::class,
            RelationManagers\InvoiceItemsRelationManager::class,
            RelationManagers\QuoteItemsRelationManager::class,
            RelationManagers\UnitRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProducts::route('/'),
        ];
    }
}
