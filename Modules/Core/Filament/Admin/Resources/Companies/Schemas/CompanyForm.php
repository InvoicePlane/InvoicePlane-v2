<?php

namespace Modules\Core\Filament\Admin\Resources\Companies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        /*
         * Logo, quote_template, invoice_template
         */
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make(trans('ip.basic'))
                            ->icon(Heroicon::OutlinedBuildingOffice2)
                            ->columnSpan(2)
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(trans('ip.name'))
                                            ->prefixIcon(Heroicon::OutlinedTag)
                                            ->required()
                                            // companies.name also has a unique DB
                                            // constraint (like search_code below) —
                                            // without this, a duplicate name passes
                                            // client validation and blows up as an
                                            // unhandled SQL 500.
                                            ->unique(ignoreRecord: true)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state, '_')))
                                            ->columnSpan(4),

                                        TextInput::make('slug')
                                            ->label(trans('ip.slug'))
                                            ->prefixIcon(Heroicon::OutlinedLink)
                                            ->required()
                                            ->readOnly()
                                            // companies.slug is also unique; two
                                            // different names can still slugify to
                                            // the same value (e.g. "Foo!" / "Foo?"),
                                            // so this needs its own uniqueness check
                                            // independent of the name check above.
                                            ->unique(ignoreRecord: true)
                                            ->dehydrated()
                                            ->columnSpan(2),

                                        TextInput::make('search_code')
                                            ->label(trans('ip.search_code'))
                                            ->prefixIcon(Heroicon::OutlinedHashtag)
                                            ->required()
                                            // companies.search_code is varchar(10) —
                                            // without this, a longer value passes
                                            // client-side validation and then blows
                                            // up as an unhandled 500 SQL truncation
                                            // error instead of a form validation
                                            // message.
                                            ->maxLength(10)
                                            // companies.search_code also has a
                                            // unique DB constraint — without this,
                                            // a duplicate hits the same "unhandled
                                            // 500 instead of a validation message"
                                            // failure mode as the length issue
                                            // above.
                                            ->unique(ignoreRecord: true)
                                            ->columnSpan(2),
                                    ]),
                            ]),

                        Section::make(trans('ip.details'))
                            ->icon(Heroicon::OutlinedIdentification)
                            ->columnSpan(1)
                            ->schema([
                                TextInput::make('vat_number')
                                    ->label(trans('ip.vat_id'))
                                    ->prefixIcon(Heroicon::OutlinedBuildingLibrary)
                                    ->nullable(),

                                TextInput::make('id_number')
                                    ->label(trans('ip.id_number'))
                                    ->prefixIcon(Heroicon::OutlinedIdentification)
                                    ->nullable(),

                                TextInput::make('coc_number')
                                    ->label(trans('ip.coc_number'))
                                    ->prefixIcon(Heroicon::OutlinedIdentification)
                                    ->nullable(),
                            ]),
                    ]),
            ]);
    }
}
