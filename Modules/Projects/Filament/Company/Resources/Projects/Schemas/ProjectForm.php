<?php

namespace Modules\Projects\Filament\Company\Resources\Projects\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Modules\Projects\Enums\ProjectStatus;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        //
                        // LEFT COLUMN: Client selector + info
                        //
                        Schemas\Components\Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Section::make(trans('ip.client'))
                                    ->schema([
                                        Select::make('customer_id')
                                            ->label(trans('ip.client'))
                                            ->relationship('customer', 'company_name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->createOptionForm([
                                                TextInput::make('company_name')
                                                    ->label(trans('ip.customer_name'))
                                                    ->required(),
                                            ])
                                            ->reactive(),

                                        Placeholder::make('customer_info')
                                            ->label(trans('ip.client_information'))
                                            ->content(fn (Get $get) => optional($get('customer'))->company_name ?? '-'),
                                    ]),
                            ]),

                        //
                        // RIGHT COLUMN: Project details
                        //
                        Schemas\Components\Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Section::make(trans('ip.details'))
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('project_name')
                                            ->label(trans('ip.project_name'))
                                            ->required()
                                            ->maxLength(255),

                                        Select::make('project_status')
                                            ->label(trans('ip.project_status'))
                                            ->options(
                                                collect(ProjectStatus::cases())
                                                    ->mapWithKeys(fn ($s) => [$s->value => trans($s->label())])
                                                    ->toArray()
                                            )
                                            ->getOptionLabelUsing(fn (string $value) => ProjectStatus::tryFrom($value)?->label())
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->required(),

                                        DatePicker::make('start_at')
                                            ->label(trans('ip.start_at'))
                                            ->required()
                                            ->native(false),

                                        DatePicker::make('end_at')
                                            ->label(trans('ip.end_at'))
                                            ->native(false),
                                    ]),
                            ]),
                    ]),

                //
                // DESCRIPTION (collapsed)
                //
                Section::make(trans('ip.description'))
                    ->collapsed()
                    ->schema([
                        TextInput::make('description')
                            ->label(trans('ip.description'))
                            ->maxLength(65535),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
