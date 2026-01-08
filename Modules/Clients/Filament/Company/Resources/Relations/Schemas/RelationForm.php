<?php

namespace Modules\Clients\Filament\Company\Resources\Relations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Modules\Clients\Enums\RelationStatus;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Contact;

class RelationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        //
                        // LEFT COLUMN: just a placeholder summary of “Client (Type)”
                        //
                        Group::make()
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Grid::make(2)
                                            ->columns(2)
                                            ->schema([
                                                Select::make('relation_status')
                                                    ->label(trans('ip.status'))
                                                    ->options(
                                                        collect(RelationStatus::cases())
                                                            ->mapWithKeys(fn ($s) => [$s->value => $s->label()])
                                                            ->toArray()
                                                    )
                                                    ->searchable()
                                                    ->required(),

                                                Select::make('relation_type')
                                                    ->label(trans('ip.type'))
                                                    ->options(
                                                        collect(RelationType::cases())
                                                            ->mapWithKeys(fn ($r) => [$r->value => $r->label()])
                                                            ->toArray()
                                                    )
                                                    ->searchable()
                                                    ->required(),

                                                TextInput::make('company_name')
                                                    ->label(trans('ip.company_name'))
                                                    ->required(),

                                                TextInput::make('trading_name')
                                                    ->label(trans('ip.trading_name')),

                                                TextInput::make('relation_number')
                                                    ->label(trans('ip.relation_number'))
                                                    ->required(),

                                                Fieldset::make(trans('ip.client_information'))
                                                    ->extraAttributes([
                                                        'class' => '!border-curious-200 dark:!border-curious-600 rounded-2xl !p-4',
                                                    ])
                                                    ->schema([
                                                        Placeholder::make('customer_info')
                                                            ->label(trans('ip.client'))
                                                            ->content(fn (Get $get) => optional($get('customer'))->company_name ?? '-'),
                                                    ]),
                                            ]),
                                    ]),
                            ])

                            ->columnSpan(1),

                        //
                        // RIGHT COLUMN: all the real inputs, in a 2-column grid
                        //
                        Schemas\Components\Group::make()
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Grid::make(2)
                                            ->columns(2)
                                            ->schema([
                                                TextInput::make('id_number')
                                                    ->label(trans('ip.id_number')),

                                                TextInput::make('coc_number')
                                                    ->label(trans('ip.coc_number')),

                                                TextInput::make('vat_number')
                                                    ->label(trans('ip.vat_id')),

                                                DatePicker::make('registered_at')
                                                    ->label(trans('ip.date'))
                                                    ->required(),

                                                Select::make('primary_contact_id')
                                                    ->label(trans('ip.primary_contact'))
                                                    ->options(
                                                        fn (): array => Contact::query()
                                                            ->orderBy('first_name')
                                                            ->orderBy('last_name')
                                                            ->get()
                                                            ->pluck('full_name', 'id')
                                                            ->toArray()
                                                    )
                                                    ->searchable()
                                                    ->preload()
                                                    ->createOptionForm([
                                                        TextInput::make('first_name')
                                                            ->label(trans('ip.first'))
                                                            ->required(),
                                                        TextInput::make('last_name')
                                                            ->label(trans('ip.last'))
                                                            ->required(),
                                                    ]),
                                            ]),
                                    ]),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }
}
