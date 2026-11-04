<?php

namespace Modules\Core\Filament\Admin\Resources\Numberings\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Models\Company;
use Modules\Core\Models\Numbering;

class NumberingForm
{
    protected const FORMAT_TOKENS = ['{{prefix}}', '{{number}}', '{{year}}', '{{yy}}', '{{month}}', '{{day}}'];

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
                                        Select::make('company_id')
                                            ->label(trans('ip.numbering_company'))
                                            ->prefixIcon(Heroicon::OutlinedBuildingOffice2)
                                            ->options(Company::all()->pluck('name', 'id'))
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->helperText(trans('ip.numbering_select_company_help'))
                                            ->columnSpan(4),

                                        Select::make('type')
                                            ->label(trans('ip.numbering_type'))
                                            ->prefixIcon(Heroicon::OutlinedRectangleStack)
                                            ->options(array_combine(
                                                array_map(fn (NumberingType $case): string => $case->value, NumberingType::cases()),
                                                array_map(fn (NumberingType $case): string => $case->label(), NumberingType::cases())
                                            ))
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function (callable $set, callable $get, $state): void {
                                                if ($state) {
                                                    $type = NumberingType::tryFrom($state);
                                                    if ($type && ! $get('prefix')) {
                                                        $set('prefix', $type->prefix());
                                                    }
                                                }
                                            })
                                            ->columnSpan(2),

                                        TextInput::make('name')
                                            ->label(trans('ip.numbering_name'))
                                            ->prefixIcon(Heroicon::OutlinedTag)
                                            ->required()
                                            ->columnSpan(2),

                                        TextInput::make('next_id')
                                            ->label(trans('ip.numbering_next_id'))
                                            ->prefixIcon(Heroicon::OutlinedHashtag)
                                            ->numeric()
                                            ->required()
                                            ->default(1)
                                            ->columnSpan(2),

                                        TextInput::make('left_pad')
                                            ->label(trans('ip.numbering_left_pad'))
                                            ->numeric()
                                            ->default(4)
                                            ->columnSpan(2),
                                    ]),
                            ]),

                        Section::make(trans('ip.details'))
                            ->icon(Heroicon::OutlinedCalculator)
                            ->columnSpan(2)
                            ->schema([
                                Select::make('prefix')
                                    ->label(trans('ip.numbering_prefix'))
                                    ->prefixIcon(Heroicon::OutlinedListBullet)
                                    ->placeholder('INV')
                                    ->options(fn (?string $state): array => self::prefixOptions($state))
                                    ->searchable()
                                    ->native(false)
                                    ->columnSpanFull(),

                                TextInput::make('format')
                                    ->label(trans('ip.numbering_format'))
                                    ->placeholder(trans('ip.numbering_format_placeholder'))
                                    ->helperText(trans('ip.numbering_format_help'))
                                    ->suffixActions(self::tokenInsertActions('format'))
                                    ->columnSpanFull(),

                                TextInput::make('group_identifier_format')
                                    ->label(trans('ip.numbering_group_identifier_format'))
                                    ->placeholder(trans('ip.numbering_group_identifier_format_placeholder'))
                                    ->helperText(trans('ip.numbering_group_identifier_format_help'))
                                    ->suffixActions(self::tokenInsertActions('group_identifier_format'))
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    /**
     * Suffix actions on the field itself -- schemaComponent() only gets bound
     * automatically here (via HasAffixes::cacheSuffixActions -> prepareAction),
     * not for a standalone Actions::make() block, so $set/$get would silently
     * no-op if these were placed as a separate schema component instead.
     *
     * @return array<Action>
     */
    protected static function tokenInsertActions(string $field): array
    {
        return array_map(
            fn (string $token): Action => Action::make("insert_{$field}_" . str_replace(['{', '}'], '', $token))
                ->label($token)
                ->view(Action::BUTTON_VIEW) // suffixActions() defaults to icon-only; we want the token text visible
                ->size('sm')
                ->color('gray')
                ->action(function (callable $set, callable $get) use ($field, $token): void {
                    $set($field, ($get($field) ?? '') . $token);
                }),
            self::FORMAT_TOKENS
        );
    }

    /**
     * Build the option list for the Prefix dropdown: every NumberingType's
     * default prefix (CUS, EXP, INV, PAY, PRJ, QUO, TSK), plus every distinct
     * prefix already in use on any company's Numbering rows (admin manages
     * numbering across all companies), plus the field's current value if set
     * so editing an existing, non-standard prefix never looks blank/invalid.
     *
     * @return array<string, string>
     */
    private static function prefixOptions(?string $currentPrefix = null): array
    {
        $prefixes = Collection::make(NumberingType::cases())
            ->map(fn (NumberingType $type): string => $type->prefix())
            ->merge(
                Numbering::query()
                    ->withoutGlobalScopes()
                    ->whereNotNull('prefix')
                    ->where('prefix', '!=', '')
                    ->distinct()
                    ->pluck('prefix')
            );

        if (filled($currentPrefix)) {
            $prefixes->push($currentPrefix);
        }

        return $prefixes
            ->unique()
            ->sort()
            ->values()
            ->mapWithKeys(fn (string $prefix): array => [$prefix => $prefix])
            ->all();
    }
}
