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
                                            ->label('Type')
                                            ->options(array_combine(
                                                array_map(fn ($case) => $case->value, NumberingType::cases()),
                                                array_map(fn ($case) => $case->label(), NumberingType::cases())
                                            ))
                                            ->required()
                                            ->disabled() // Company users cannot change type
                                            ->dehydrated(),
                                        
                                        TextInput::make('name')
                                            ->label('Name')
                                            ->required(),
                                    ])
                                    ->columnSpan(1),

                                // ── RIGHT: Next ID / Left Pad
                                Grid::make()
                                    ->schema([
                                        TextInput::make('next_id')
                                            ->label('Next ID')
                                            ->numeric()
                                            ->required()
                                            ->helperText('Can be adjusted to troubleshoot numbering issues'),

                                        TextInput::make('left_pad')
                                            ->label('Left Pad')
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
                                        ->label('Prefix')
                                        ->placeholder('INV')
                                        ->disabled() // Company users cannot change prefix
                                        ->dehydrated(),
                                    
                                    TextInput::make('format')
                                        ->label('Format')
                                        ->placeholder('{{prefix}}-{{number}}')
                                        ->helperText('Use {{prefix}}, {{number}}, {{year}}, {{month}}, {{day}} as placeholders. Only dash (-) or underscore (_) separators allowed.'),
                                ]),
                                Schemas\Components\Group::make()->schema([
                                    Placeholder::make('format_helper')
                                        ->label('Format Help')
                                        ->content('You can customize the format using placeholders: {{prefix}} for prefix, {{number}} for sequential number, {{year}} for 4-digit year, {{yy}} for 2-digit year, {{month}} for month, {{day}} for day. The number will be left-padded according to the Left Pad setting.')
                                        ->columnSpanFull(),
                                ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
