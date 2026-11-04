<?php

namespace Modules\Core\Filament\Company\Resources\TaxRates\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Modules\Core\Enums\TaxRateType;

class TaxRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make(trans('ip.basic_information'))
                            ->icon(Heroicon::OutlinedIdentification)
                            ->columnSpan(2)
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(trans('ip.name'))
                                            ->prefixIcon(Heroicon::OutlinedTag)
                                            ->required()
                                            ->autofocus()
                                            ->columnSpan(4),

                                        TextInput::make('code')
                                            ->label(trans('ip.tax_rate_code'))
                                            ->prefixIcon(Heroicon::OutlinedHashtag)
                                            // tax_rates.code is NOT NULL with
                                            // no DB default — leaving this
                                            // blank passes client validation
                                            // and blows up as an unhandled
                                            // SQLSTATE 500 on submission.
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->columnSpan(2),

                                        Toggle::make('is_active')
                                            ->label(trans('ip.is_active'))
                                            ->default(true)
                                            ->columnSpan(2),
                                    ]),
                            ]),

                        Section::make(trans('ip.details'))
                            ->icon(Heroicon::OutlinedReceiptPercent)
                            ->columnSpan(1)
                            ->schema([
                                Select::make('tax_rate_type')
                                    ->label(trans('ip.tax_rate_type'))
                                    ->prefixIcon(Heroicon::OutlinedListBullet)
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
                                    ->prefixIcon(Heroicon::OutlinedReceiptPercent)
                                    ->required()
                                    ->numeric()
                                    ->step(0.01),
                            ]),
                    ]),
            ]);
    }
}
