<?php

namespace Modules\Clients\Filament\Company\Resources\Relations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
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
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        // LEFT: company identity + registration/tax details.
                        Group::make()
                            ->schema([
                                Section::make(trans('ip.client_information'))
                                    ->icon(Heroicon::OutlinedBuildingOffice2)
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Select::make('relation_type')
                                                    ->label(trans('ip.type'))
                                                    ->prefixIcon(Heroicon::OutlinedTag)
                                                    ->options(
                                                        collect(RelationType::cases())
                                                            ->mapWithKeys(fn ($r) => [$r->value => $r->label()])
                                                            ->toArray()
                                                    )
                                                    ->searchable()
                                                    ->required(),

                                                Select::make('relation_status')
                                                    ->label(trans('ip.status'))
                                                    ->prefixIcon(Heroicon::OutlinedCheckBadge)
                                                    ->options(
                                                        collect(RelationStatus::cases())
                                                            ->mapWithKeys(fn ($s) => [$s->value => $s->label()])
                                                            ->toArray()
                                                    )
                                                    ->searchable()
                                                    ->required(),

                                                TextInput::make('relation_number')
                                                    ->label(trans('ip.relation_number'))
                                                    ->prefixIcon(Heroicon::OutlinedHashtag)
                                                    ->required()
                                                    ->maxLength(30),

                                                TextInput::make('company_name')
                                                    ->label(trans('ip.company_name'))
                                                    ->prefixIcon(Heroicon::OutlinedBuildingOffice)
                                                    ->required()
                                                    ->maxLength(150)
                                                    ->columnSpan(2)
                                                    ->live(debounce: 500)
                                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                        if ( ! $get('trading_name')) {
                                                            $set('unique_name', \Illuminate\Support\Str::slug($state));
                                                        }
                                                    }),

                                                TextInput::make('trading_name')
                                                    ->label(trans('ip.trading_name'))
                                                    ->prefixIcon(Heroicon::OutlinedBuildingStorefront)
                                                    ->maxLength(70)
                                                    ->live(debounce: 500)
                                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                        $set('unique_name', \Illuminate\Support\Str::slug($state));
                                                    }),

                                                TextInput::make('unique_name')
                                                    ->label(trans('ip.unique_name'))
                                                    ->prefixIcon(Heroicon::OutlinedLink)
                                                    ->unique(Relation::class, 'unique_name', ignoreRecord: true)
                                                    ->required()
                                                    ->readOnly()
                                                    ->dehydrated()
                                                    ->columnSpanFull()
                                                    ->helperText(trans('ip.unique_name_helper'))
                                                    ->afterStateHydrated(function (Get $get, Set $set, ?string $state) {
                                                        if (empty($state)) {
                                                            $name = $get('trading_name') ?: $get('company_name');
                                                            if ($name) {
                                                                $set('unique_name', \Illuminate\Support\Str::slug($name));
                                                            }
                                                        }
                                                    }),
                                            ]),
                                    ]),

                                Section::make(trans('ip.registration_tax_details'))
                                    ->icon(Heroicon::OutlinedDocumentText)
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('id_number')
                                                    ->label(trans('ip.id_number'))
                                                    ->prefixIcon(Heroicon::OutlinedIdentification)
                                                    ->maxLength(70),

                                                TextInput::make('coc_number')
                                                    ->label(trans('ip.coc_number'))
                                                    ->prefixIcon(Heroicon::OutlinedDocumentText)
                                                    ->maxLength(70),

                                                TextInput::make('vat_number')
                                                    ->label(trans('ip.vat_id'))
                                                    ->prefixIcon(Heroicon::OutlinedReceiptPercent)
                                                    ->maxLength(70),

                                                DatePicker::make('registered_at')
                                                    ->label(trans('ip.registered_at'))
                                                    ->prefixIcon(Heroicon::OutlinedCalendarDays)
                                                    ->required(),
                                            ]),
                                    ]),
                            ])
                            ->columnSpan(2),

                        // RIGHT: contact details sidebar.
                        Group::make()
                            ->schema([
                                Section::make(trans('ip.contact_details'))
                                    ->icon(Heroicon::OutlinedUserCircle)
                                    ->schema([
                                        TextInput::make('email')
                                            ->label(trans('ip.email'))
                                            ->prefixIcon(Heroicon::OutlinedEnvelope)
                                            ->email(),

                                        Select::make('primary_contact_id')
                                            ->label(trans('ip.primary_contact'))
                                            ->prefixIcon(Heroicon::OutlinedUser)
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
                                                    ->required()
                                                    // contacts.first_name is varchar(50) —
                                                    // same overlong-input 500 risk as the
                                                    // main ContactForm's first_name field.
                                                    ->maxLength(50),
                                                TextInput::make('last_name')
                                                    ->label(trans('ip.last'))
                                                    ->required()
                                                    ->maxLength(50),
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
