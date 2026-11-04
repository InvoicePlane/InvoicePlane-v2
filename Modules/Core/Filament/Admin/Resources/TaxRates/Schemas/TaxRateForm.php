<?php

namespace Modules\Core\Filament\Admin\Resources\TaxRates\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Modules\Core\Enums\TaxRateType;

class TaxRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make(trans('ip.basic_information'))
                            ->icon(Heroicon::OutlinedIdentification)
                            ->columnSpan(2)
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(trans('ip.name'))
                                            ->prefixIcon(Heroicon::OutlinedTag)
                                            ->required()
                                            ->autofocus()
                                            ->columnSpan(4),

                                        TextInput::make('code')
                                            ->label(trans('ip.tax_rate_code'))
                                            ->prefixIcon(Heroicon::OutlinedHashtag)
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
                                            ->unique(ignoreRecord: true)
                                            ->columnSpan(2),

                                        Toggle::make('is_active')
                                            ->label(trans('ip.is_active'))
                                            ->default(true)
                                            ->columnSpan(2),
                                    ]),
                            ]),

                        Section::make(trans('ip.details'))
                            ->icon(Heroicon::OutlinedReceiptPercent)
                            ->columnSpan(1)
                            ->schema([
                                Select::make('tax_rate_type')
                                    ->label(trans('ip.tax_rate_type'))
                                    ->prefixIcon(Heroicon::OutlinedListBullet)
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
                                    ->prefixIcon(Heroicon::OutlinedReceiptPercent)
                                    ->required()
                                    ->numeric()
                                    ->step(0.01),
                            ]),
                    ]),
            ]);
    }
}
