<?php

namespace Modules\Products\Filament\Company\Resources\Products\Schemas;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Modules\Products\Enums\ProductType;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make(trans('ip.details'))
                            ->icon(Heroicon::OutlinedArchiveBox)
                            ->columnSpan(2)
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('code')
                                            ->label(trans('ip.product_sku'))
                                            ->prefixIcon(Heroicon::OutlinedHashtag)
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpan(2),

                                        TextInput::make('product_name')
                                            ->label(trans('ip.product_name'))
                                            ->prefixIcon(Heroicon::OutlinedTag)
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpan(2),

                                        Select::make('type')
                                            ->label(trans('ip.product_type'))
                                            ->prefixIcon(Heroicon::OutlinedCube)
                                            ->options(
                                                collect(ProductType::cases())
                                                    ->mapWithKeys(fn (ProductType $type) => [$type->value => $type->label()])
                                                    ->toArray()
                                            )
                                            ->native(false)
                                            ->required()
                                            ->columnSpan(2),

                                        TextInput::make('price')
                                            ->label(trans('ip.price'))
                                            ->prefixIcon(Heroicon::OutlinedCurrencyDollar)
                                            ->numeric()
                                            ->required()
                                            ->columnSpan(2),
                                    ]),
                            ]),

                        Section::make(trans('ip.classification'))
                            ->icon(Heroicon::OutlinedRectangleStack)
                            ->columnSpan(1)
                            ->schema([
                                Select::make('category_id')
                                    ->label(trans('ip.family'))
                                    ->prefixIcon(Heroicon::OutlinedRectangleStack)
                                    ->relationship('productCategory', 'category_name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('unit_id')
                                    ->label(trans('ip.product_unit'))
                                    ->prefixIcon(Heroicon::OutlinedScale)
                                    ->relationship('productUnit', 'unit_name')
                                    ->searchable()
                                    ->preload(),

                                Select::make('tax_rate_id')
                                    ->label(trans('ip.tax_rate'))
                                    ->prefixIcon(Heroicon::OutlinedReceiptPercent)
                                    ->relationship('taxRate', 'name')
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ]),

                Section::make(trans('ip.description'))
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->collapsed()
                    ->schema([
                        MarkdownEditor::make('description')
                            ->label(trans('ip.description'))
                            ->toolbarButtons(['bold', 'italic']),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
