<?php

namespace Modules\Products\Filament\Company\Resources\ProductUnits\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Schemas;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ProductUnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Schemas\Components\Group::make()->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('unit_name')
                                ->inlineLabel()
                                ->label(trans('ip.unit'))
                                ->required()
                                ->autofocus(),
                            TextInput::make('unit_name_plrl')
                                ->inlineLabel()
                                ->label(trans('ip.unit_name_plrl')),
                        ]),
                ])->columns(1),
                Schemas\Components\Group::make()->schema([
                    Placeholder::make('explanation Product Unit')
                        ->label('just some text'),
                ])->columns(1),
            ]);
    }
}
