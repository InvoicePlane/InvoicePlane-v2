<?php

namespace Modules\Clients\Filament\Company\Resources\Contacts\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Modules\Clients\Enums\CommunicationType;
use Modules\Clients\Enums\Gender;
use Modules\Clients\Models\Contact;

class ContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        //
                        // LEFT COLUMN: choose client + summary
                        //
                        Section::make(trans('ip.client'))
                            ->columnSpan(1)
                            ->schema([
                                Select::make('relation_id')
                                    ->relationship('relation', 'company_name')
                                    ->label(trans('ip.client'))
                                    ->searchable()
                                    ->preload()
                                    ->required(trans('ip.relation_required'))
                                    ->native(false)
                                    ->createOptionForm([
                                        TextInput::make('company_name')
                                            ->label(trans('ip.customer_name'))
                                            ->required(),
                                    ])
                                    ->reactive(),

                                Fieldset::make(trans('ip.client_information'))
                                    ->extraAttributes([
                                        'class' => '!border-curious-200 dark:!border-curious-600 rounded-2xl !p-4',
                                    ])
                                    ->columns(1)
                                    ->schema([
                                        Placeholder::make('relation_info')
                                            ->label(trans('ip.client'))
                                            ->content(fn (Get $get) => optional($get('relation'))->company_name ?? '-'),
                                    ])
                                    ->visible(fn (Get $get) => filled($get('relation_id'))),
                            ]),

                        //
                        // RIGHT COLUMN: personal info + primary contacts
                        //
                        Section::make(trans('ip.personal_information'))
                            ->columnSpan(1)
                            ->columns(2)
                            ->schema([
                                TextInput::make('first_name')
                                    ->label(trans('ip.first_name'))
                                    ->required()
                                    // contacts.first_name is varchar(50) —
                                    // without this, a longer value passes
                                    // client validation and blows up as an
                                    // unhandled SQL 500.
                                    ->maxLength(50),

                                TextInput::make('last_name')
                                    ->label(trans('ip.last_name'))
                                    ->required()
                                    ->maxLength(50),

                                Placeholder::make('primary_email')
                                    ->label(trans('ip.email'))
                                    ->content(
                                        fn (?Contact $record = null) => $record
                                            ? optional($record->communications)
                                                ->where('communication_type', CommunicationType::EMAIL->value)
                                                ->where('is_primary', true)
                                                ->first()?->contactable_value ?? '-'
                                            : '-'
                                    ),

                                Placeholder::make('primary_phone')
                                    ->label(trans('ip.phone'))
                                    ->content(
                                        fn (?Contact $record = null) => $record
                                            ? optional($record->communications)
                                                ->where('communication_type', CommunicationType::PHONE->value)
                                                ->where('is_primary', true)
                                                ->first()?->contactable_value ?? '-'
                                            : '-'
                                    ),

                                Select::make('gender')
                                    ->label(trans('ip.gender'))
                                    ->options(
                                        collect(Gender::cases())
                                            ->mapWithKeys(fn (Gender $g) => [$g->value => trans($g->label())])
                                            ->toArray()
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->required(),
                            ]),
                    ]),
            ]);
    }
}
