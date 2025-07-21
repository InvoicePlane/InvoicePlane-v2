<?php

namespace Modules\Products\Filament\Company\Resources\Products\Schemas;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Products\Enums\ProductType;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        //
                        // LEFT COLUMN: basic details
                        //
                        Schemas\Components\Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Section::make(trans('ip.details'))
                                    ->schema([
                                        TextInput::make('code')
                                            ->label(trans('ip.product_sku'))
                                            ->required()
                                            ->maxLength(255),

                                        TextInput::make('product_name')
                                            ->label(trans('ip.product_name'))
                                            ->required()
                                            ->maxLength(255),

                                        Select::make('type')
                                            ->label(trans('ip.product_type'))
                                            ->options(
                                                collect(ProductType::cases())
                                                    ->mapWithKeys(fn (ProductType $type) => [$type->value => $type->label()])
                                                    ->toArray()
                                            )
                                            ->native(false)
                                            ->required(),
                                    ]),
                            ]),

                        //
                        // RIGHT COLUMN: classification
                        //
                        Schemas\Components\Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Section::make(trans('ip.classification'))
                                    ->schema([
                                        Grid::make(2)->schema([
                                            Select::make('category_id')
                                                ->label(trans('ip.family'))
                                                ->relationship('productCategory', 'category_name')
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
}
