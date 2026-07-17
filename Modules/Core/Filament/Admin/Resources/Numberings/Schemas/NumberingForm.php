<?php

namespace Modules\Core\Filament\Admin\Resources\Numberings\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
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
                // Company selection (Admin can assign to any company)
                Section::make(trans('ip.numbering_company_assignment'))
                    ->schema([
                        Select::make('company_id')
                            ->label(trans('ip.numbering_company'))
                            ->options(Company::all()->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText(trans('ip.numbering_select_company_help')),
                    ])
                    ->columnSpanFull(),

                //
                // Top: Type and Name
                //
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                // ── LEFT: Type and Name
                                Schemas\Components\Group::make()
                                    ->schema([
                                        Select::make('type')
                                            ->label(trans('ip.numbering_type'))
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
                                            }),
                                        TextInput::make('name')
                                            ->label(trans('ip.numbering_name'))
                                            ->required(),
                                    ])
                                    ->columnSpan(1),

                                // ── RIGHT: Next ID / Left Pad
                                Grid::make()
                                    ->schema([
                                        TextInput::make('next_id')
                                            ->label(trans('ip.numbering_next_id'))
                                            ->numeric()
                                            ->required()
                                            ->default(1),

                                        TextInput::make('left_pad')
                                            ->label(trans('ip.numbering_left_pad'))
                                            ->numeric()
                                            ->default(4),
                                    ])
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpanFull(),

                //
                // Below: Prefix and Format
                //
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->columns(2)
                            ->schema([
                                Schemas\Components\Group::make()->schema([
                                    Select::make('prefix')
                                        ->label(trans('ip.numbering_prefix'))
                                        ->placeholder('INV')
                                        ->options(fn (?string $state): array => self::prefixOptions($state))
                                        ->searchable()
                                        ->native(false),
                                    TextInput::make('format')
                                        ->label(trans('ip.numbering_format'))
                                        ->placeholder(trans('ip.numbering_format_placeholder'))
                                        ->helperText(trans('ip.numbering_format_help'))
                                        ->suffixActions(self::tokenInsertActions('format')),
                                    TextInput::make('group_identifier_format')
                                        ->label(trans('ip.numbering_group_identifier_format'))
                                        ->placeholder(trans('ip.numbering_group_identifier_format_placeholder'))
                                        ->helperText(trans('ip.numbering_group_identifier_format_help'))
                                        ->suffixActions(self::tokenInsertActions('group_identifier_format')),
                                ]),
                                Schemas\Components\Group::make()->schema([
                                    Placeholder::make('format_helper')
                                        ->label('')
                                        ->content(trans('ip.numbering_format_helper'))
                                        ->columnSpanFull(),
                                ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
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
}
