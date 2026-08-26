<?php

namespace Modules\Products\Filament\Company\Resources\ProductUnits\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ProductUnitForm
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
                                TextInput::make('unit_name')
                                    ->label(trans('ip.unit'))
                                    ->required()
                                    ->maxLength(255)
                                    ->autofocus(),
                                TextInput::make('unit_name_plrl')
                                    ->label(trans('ip.unit_name_plrl'))
                                    ->maxLength(255),
                            ]),

                        // Right column - can be left empty or used for additional fields
                        Schemas\Components\Group::make()
                            ->columnSpan(1)
                            ->schema([
                                // Add any additional fields if needed
                            ]),
                    ]),
            ]);
    }
}
