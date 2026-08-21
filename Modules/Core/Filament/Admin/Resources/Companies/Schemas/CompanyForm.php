<?php

namespace Modules\Core\Filament\Admin\Resources\Companies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
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
                Grid::make(2)
                    ->schema([
                        Section::make(trans('ip.basic'))
                            ->columnSpan(1)
                            ->columns(1)
                            ->schema([
                                TextInput::make('name')
                                    ->label(trans('ip.name'))
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state, '_'))),

                                TextInput::make('slug')
                                    ->label(trans('ip.slug'))
                                    ->required()
                                    ->readOnly()
                                    ->dehydrated(),
                            ]),

                        //
                        // ─── RIGHT COLUMN (3/4 width) ────────────────────────────────
                        //
                        Section::make(trans('ip.details'))
                            ->columnSpan(1)   // 3/4 of the total width
                            ->columns(2)      // two‐columns inside
                            ->schema([
                                TextInput::make('search_code')
                                    ->label(trans('ip.search_code'))
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
                                    ->unique(ignoreRecord: true),
                                TextInput::make('vat_number')
                                    ->label(trans('ip.vat_id'))
                                    ->nullable(),

                                TextInput::make('id_number')
                                    ->label(trans('ip.id_number'))
                                    ->nullable(),

                                TextInput::make('coc_number')
                                    ->label(trans('ip.coc_number'))
                                    ->nullable(),
                            ]),
                    ]),
            ]);
    }
}
