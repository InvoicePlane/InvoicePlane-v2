<?php

namespace Modules\Products\Filament\Company\Resources\ProductCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ProductCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        // Left column
                        Schemas\Components\Group::make()
                            ->columnSpan(1)
                            ->schema([
                                TextInput::make('category_name')
                                    ->label(trans('ip.family'))
                                    ->required()
                                    ->maxLength(255)
                                    ->autofocus(),
                            ]),

                        // Right column
                        Schemas\Components\Group::make()
                            ->columnSpan(1)
                            ->schema([
                                // Add any additional fields if needed
                            ]),
                    ]),
            ]);
    }
}
