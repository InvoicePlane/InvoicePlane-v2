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
use Modules\Core\Models\Company;

class NumberingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Company selection (Admin can assign to any company)
                Section::make(trans('ip.numbering_company_assignment'))
                    ->schema([
                        Select::make('company_id')
                            ->label(trans('ip.numbering_company'))
                            ->options(Company::all()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText(trans('ip.numbering_select_company_help')),
                    ])
                    ->columnSpanFull(),

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
                                                array_map(fn (NumberingType $case): string => $case->value, NumberingType::cases()),
                                                array_map(fn (NumberingType $case): string => $case->label(), NumberingType::cases())
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
                                            ->default(1),

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
                                        ->placeholder('JOB'),
                                    TextInput::make('format')
                                        ->label(trans('ip.numbering_format'))
                                        ->placeholder(trans('ip.numbering_format_placeholder'))
                                        ->helperText(trans('ip.numbering_format_help')),
                                    TextInput::make('group_identifier_format')
                                        ->label(trans('ip.numbering_group_identifier_format'))
                                        ->placeholder('{PREFIX}-{YEAR}-{ID}'),
                                ]),
                                Schemas\Components\Group::make()->schema([
                                    Placeholder::make('format_helper')
                                        ->label('')
                                        ->content(trans('ip.numbering_format_helper_admin'))
                                        ->columnSpanFull(),
                                ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
