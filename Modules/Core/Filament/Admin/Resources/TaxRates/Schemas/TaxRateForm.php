<?php

namespace Modules\Core\Filament\Admin\Resources\TaxRates\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Core\Enums\TaxRateType;

class TaxRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        //
                        // LEFT COLUMN: Code, Name, Active
                        //
                        Section::make(trans('ip.basic_information'))
                            ->schema([
                                // 2-column grid for Code + Active
                                Grid::make(2)
                                    ->columns(2)
                                    // <-- Center *every* grid cell vertically
                                    ->extraAttributes([
                                        'class' => '!items-center',
                                    ])
                                    ->schema([
                                        TextInput::make('code')
                                            ->label(trans('ip.tax_rate_code'))
                                            // tax_rates.code is NOT NULL with
                                            // no DB default — leaving this
                                            // blank (it looked optional,
                                            // no asterisk) passed client
                                            // validation and then blew up as
                                            // an unhandled SQLSTATE 500 on
                                            // every submission, silently:
                                            // the modal's outer wrapper
                                            // always reports hidden/zero
                                            // height regardless of state, so
                                            // the failure was invisible too.
                                            ->required()
                                            // tax_rates also has a unique DB
                                            // constraint on (company_id, code)
                                            // — without this, a duplicate
                                            // code hits the same "unhandled
                                            // 500 instead of a validation
                                            // message" failure mode as the
                                            // missing ->required() above did.
                                            // Scoped to 'code' alone (not
                                            // true composite uniqueness)
                                            // since every admin-created tax
                                            // rate lands on the acting
                                            // admin's own active company
                                            // anyway (BelongsToCompany, see
                                            // the Numbering company-scoping
                                            // note elsewhere in this
                                            // codebase) — this is at worst
                                            // stricter than the real DB
                                            // constraint, never looser.
                                            ->unique(ignoreRecord: true),

                                        Toggle::make('is_active')
                                            ->label(trans('ip.is_active'))
                                            ->default(true)
                                            ->columnSpan(1)
                                            // no need for h-full, the grid will handle centering
                                            ->extraAttributes([
                                                'class' => '!flex items-center',
                                            ]),
                                    ]),

                                // Name below, full width of the section
                                TextInput::make('name')
                                    ->label(trans('ip.name'))
                                    ->required()
                                    ->autofocus(),
                            ])
                            ->columnSpan(1),

                        //
                        // RIGHT COLUMN: Type & Percentage
                        //
                        Section::make(trans('ip.details'))
                            ->schema([
                                Grid::make(2)
                                    ->columns(2)
                                    ->schema([
                                        Select::make('tax_rate_type')
                                            ->label(trans('ip.tax_rate_type'))
                                            ->options(
                                                collect(TaxRateType::cases())
                                                    ->mapWithKeys(fn (TaxRateType $type) => [
                                                        $type->value => trans($type->label()),
                                                    ])
                                                    ->toArray()
                                            )
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->native(false),

                                        TextInput::make('rate')
                                            ->label(trans('ip.percentage'))
                                            ->required()
                                            ->numeric()
                                            ->step(0.01),
                                    ]),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }
}
