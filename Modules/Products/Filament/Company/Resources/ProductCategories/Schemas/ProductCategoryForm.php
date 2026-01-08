<?php

namespace Modules\Products\Filament\Company\Resources\ProductCategories\Schemas;

use Filament\Forms\Components\Placeholder;
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
                    ->schema([
                        Schemas\Components\Group::make()
                            ->schema([
                                TextInput::make('category_name')
                                    ->label(trans('ip.family'))
                                    ->inlineLabel()
                                    ->autofocus()
                                    ->required(),
                            ]),
                        Schemas\Components\Group::make()->schema([
                            Placeholder::make('explanation Product Family')
                                ->label('just some text'),
                        ]),
                    ]),
            ]);
    }
}
