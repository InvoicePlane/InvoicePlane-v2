<?php

namespace Modules\Clients\Filament\Company\Resources\Relations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\Clients\Enums\RelationStatus;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Clients\Services\ContactService;

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
                                                    ->required()
                                                    ->live(debounce: 500)
                                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                        if ( ! $get('trading_name')) {
                                                            $set('unique_name', \Illuminate\Support\Str::slug($state));
                                                        }
                                                    }),

                                                TextInput::make('email')
                                                    ->label(trans('ip.email'))
                                                    ->email(),

                                                TextInput::make('trading_name')
                                                    ->label(trans('ip.trading_name'))
                                                    ->live(debounce: 500)
                                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                        $set('unique_name', \Illuminate\Support\Str::slug($state));
                                                    }),

                                                TextInput::make('unique_name')
                                                    ->label(trans('ip.unique_name'))
                                                    ->unique(\Modules\Clients\Models\Relation::class, 'unique_name', ignoreRecord: true)
                                                    ->required()
                                                    ->readOnly()
                                                    ->dehydrated()
                                                    ->helperText(trans('ip.unique_name_helper'))
                                                    ->afterStateHydrated(function (Get $get, Set $set, ?string $state) {
                                                        if (empty($state)) {
                                                            $name = $get('trading_name') ?: $get('company_name');
                                                            if ($name) {
                                                                $set('unique_name', \Illuminate\Support\Str::slug($name));
                                                            }
                                                        }
                                                    }),

                                                TextInput::make('relation_number')
                                                    ->label(trans('ip.relation_number'))
                                                    ->required(),
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
                                                    ->label(trans('ip.registered_at'))
                                                    ->required(),
                                            ]),
                                    ]),
                            ])
                            ->columnSpan(1),
                    ]),
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        //
                        // LEFT COLUMN: just a placeholder summary of “Client (Type)”
                        //
                        Group::make()
                            ->schema([
                                Fieldset::make(trans('ip.client_information'))
                                    ->extraAttributes([
                                        'class' => '!border-curious-200 dark:!border-curious-600 rounded-2xl !p-4',
                                    ])
                                    ->schema([
                                        Placeholder::make('company_name_display')
                                            ->label(trans('ip.company_name'))
                                            ->content(fn (Get $get) => $get('company_name') ?: '-'),

                                        Placeholder::make('trading_name_display')
                                            ->label(trans('ip.trading_name'))
                                            ->content(fn (Get $get) => $get('trading_name') ?: '-'),

                                        Placeholder::make('relation_type_display')
                                            ->label(trans('ip.type'))
                                            ->content(function (Get $get) {
                                                $type = $get('relation_type');
                                                if ( ! $type) {
                                                    return '-';
                                                }

                                                if ($type instanceof RelationType) {
                                                    return $type->label();
                                                }

                                                $typeEnum = RelationType::tryFrom($type);

                                                return $typeEnum ? $typeEnum->label() : '-';
                                            }),
                                    ])->columnSpan(2),
                            ])
                            ->columnSpan(1),

                        //
                        // RIGHT COLUMN: all the real inputs, in a 2-column grid
                        //
                        Schemas\Components\Group::make()
                            ->schema([
                                Fieldset::make(trans('ip.contact_details'))
                                    ->schema([
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
                                            ])
                                            ->createOptionUsing(function (array $data, ?Relation $record) {
                                                // The mounted record is the Relation (client) this
                                                // form belongs to. On the create-client form there is
                                                // no persisted Relation yet, so there's no relation_id
                                                // to attach the new Contact to — guard against that
                                                // rather than violating the not-null relation_id column.
                                                if ( ! $record?->exists) {
                                                    Notification::make()
                                                        ->title(trans('ip.save_client_before_adding_contact'))
                                                        ->danger()
                                                        ->send();

                                                    return null;
                                                }

                                                return app(ContactService::class)->createContact([
                                                    'relation_id' => $record->getKey(),
                                                    'first_name'  => $data['first_name'],
                                                    'last_name'   => $data['last_name'],
                                                ])->getKey();
                                            }),

                                        TagsInput::make('email_cc')
                                            ->label(trans('ip.cc_email_addresses'))
                                            ->splitKeys([',', 'Tab', ' '])
                                            ->placeholder('cc@example.com')
                                            ->nestedRecursiveRules('email')
                                            ->helperText(trans('ip.cc_email_addresses_helper')),
                                    ]),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }
}
