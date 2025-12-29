<?php

namespace Modules\Core\Filament\Company\Resources\Numberings\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Core\Enums\NumberingType;

class NumberingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Hidden company_id field (locked to current company)
                Hidden::make('company_id')
                    ->default(fn () => session('current_company_id'))
                    ->disabled()
                    ->dehydrated(false), // Don't allow changing company_id

                //
                // Top: Type and Name
                //
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                // ── LEFT: Type and Name
                                Schemas\Components\Group::make()
                                    ->schema([
                                        Select::make('type')
                                            ->label(trans('ip.numbering_type'))
                                            ->options(array_combine(
                                                array_map(fn ($case) => $case->value, NumberingType::cases()),
                                                array_map(fn ($case) => $case->label(), NumberingType::cases())
                                            ))
                                            ->required()
                                            ->disabled() // Company users cannot change type
                                            ->dehydrated(),
                                        
                                        TextInput::make('name')
                                            ->label(trans('ip.numbering_name'))
                                            ->required(),
                                    ])
                                    ->columnSpan(1),

                                // ── RIGHT: Next ID / Left Pad
                                Grid::make()
                                    ->schema([
                                        TextInput::make('next_id')
                                            ->label(trans('ip.numbering_next_id'))
                                            ->numeric()
                                            ->required()
                                            ->helperText(trans('ip.numbering_next_id_help')),

                                        TextInput::make('left_pad')
                                            ->label(trans('ip.numbering_left_pad'))
                                            ->numeric()
                                            ->default(4),
                                    ])
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpanFull(),

                //
                // Below: Prefix and Format
                //
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->columns(2)
                            ->schema([
                                Schemas\Components\Group::make()->schema([
                                    TextInput::make('prefix')
                                        ->label(trans('ip.numbering_prefix'))
                                        ->placeholder('INV')
                                        ->disabled() // Company users cannot change prefix
                                        ->dehydrated(),
                                    
                                    TextInput::make('format')
                                        ->label(trans('ip.numbering_format'))
                                        ->placeholder(trans('ip.numbering_format_placeholder'))
                                        ->helperText(trans('ip.numbering_format_help')),
                                ]),
                                Schemas\Components\Group::make()->schema([
                                    Placeholder::make('format_helper')
                                        ->label(trans('ip.numbering_format_help_label'))
                                        ->content(trans('ip.numbering_format_helper'))
                                        ->columnSpanFull(),
                                ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
