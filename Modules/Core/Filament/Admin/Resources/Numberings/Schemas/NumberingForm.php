<?php

namespace Modules\Core\Filament\Admin\Resources\Numberings\Schemas;

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
                                            ->reactive()
                                            ->afterStateUpdated(function (callable $set, callable $get, $state): void {
                                                if ($state) {
                                                    $type = NumberingType::tryFrom($state);
                                                    if ($type && ! $get('prefix')) {
                                                        $set('prefix', $type->prefix());
                                                    }
                                                }
                                            }),
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
                                            ->default(1),

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
                                        ->placeholder('JOB'),
                                    TextInput::make('format')
                                        ->label('Format')
                                        ->placeholder('{{prefix}}-{{number}}')
                                        ->helperText('Use {{prefix}} and {{number}} as placeholders'),
                                ]),
                                Schemas\Components\Group::make()->schema([
                                    Placeholder::make('format_helper')
                                        ->label('')
                                        ->content('The format string can use {{prefix}} for the prefix and {{number}} for the sequential number. The number will be left-padded according to the Left Pad setting.')
                                        ->columnSpanFull(),
                                ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
