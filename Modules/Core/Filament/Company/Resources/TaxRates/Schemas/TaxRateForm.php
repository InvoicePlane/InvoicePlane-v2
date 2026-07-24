<?php

namespace Modules\Core\Filament\Company\Resources\TaxRates\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Core\Enums\TaxRateType;

class TaxRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        Section::make(trans('ip.basic_information'))
                            ->schema([
                                Grid::make(2)
                                    ->columns(2)
                                    ->extraAttributes([
                                        'class' => '!items-center',
                                    ])
                                    ->schema([
                                        TextInput::make('code')
                                            ->label(trans('ip.tax_rate_code'))
                                            ->nullable(),

                                        Toggle::make('is_active')
                                            ->label(trans('ip.is_active'))
                                            ->default(true)
                                            ->columnSpan(1)
                                            ->extraAttributes([
                                                'class' => '!flex items-center',
                                            ]),
                                    ]),

                                TextInput::make('name')
                                    ->label(trans('ip.name'))
                                    ->required()
                                    ->autofocus(),
                            ])
                            ->columnSpan(1),

                        Section::make(trans('ip.details'))
                            ->schema([
                                Grid::make(2)
                                    ->columns(2)
                                    ->schema([
                                        Select::make('tax_rate_type')
                                            ->label(trans('ip.tax_rate_type'))
                                            ->options(
                                                collect(TaxRateType::cases())
                                                    ->mapWithKeys(fn (TaxRateType $type) => [
                                                        $type->value => trans($type->label()),
                                                    ])
                                                    ->toArray()
                                            )
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->native(false),

                                        TextInput::make('rate')
                                            ->label(trans('ip.percentage'))
                                            ->required()
                                            ->numeric()
                                            ->step(0.01),
                                    ]),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }
}
