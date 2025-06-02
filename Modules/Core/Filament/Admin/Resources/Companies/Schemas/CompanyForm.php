<?php

namespace Modules\Core\Filament\Admin\Resources\Companies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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
                                    ->reactive() // so we can watch its changes
                                    ->afterStateUpdated(function (callable $set, $state): void {
                                        // whenever 'name' changes, regenerate 'slug'
                                        $set('slug', Str::slug($state));
                                    }),

                                TextInput::make('slug')
                                    ->label(trans('ip.slug'))
                                    ->disabled() // can't manually edit
                                    ->required()
                                    ->reactive(), // stays in sync
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
                                    ->required(),
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
