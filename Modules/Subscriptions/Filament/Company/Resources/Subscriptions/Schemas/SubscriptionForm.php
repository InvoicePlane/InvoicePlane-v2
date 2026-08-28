<?php

namespace Modules\Subscriptions\Filament\Company\Resources\Subscriptions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Modules\Products\Models\Product;
use Modules\Subscriptions\Enums\BillingInterval;
use Modules\Subscriptions\Enums\IntervalUnit;
use Modules\Subscriptions\Enums\SubscriptionStatus;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(5)
                    ->columnSpanFull()
                    ->schema([
                        Schemas\Components\Group::make()
                            ->columnSpan(3)
                            ->schema([
                                Section::make(trans('ip.subscription_overview'))
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(trans('ip.subscription_title'))
                                            ->required()
                                            ->placeholder(trans('ip.subscription_title_placeholder'))
                                            ->columnSpanFull(),

                                        Select::make('customer_id')
                                            ->label(trans('ip.subscription_client'))
                                            ->relationship('customer', 'company_name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        TextInput::make('number')
                                            ->label(trans('ip.subscription_code'))
                                            ->placeholder(trans('ip.subscription_code_auto'))
                                            ->helperText(trans('ip.subscription_code_helper')),
                                    ])
                                    ->columns(2),

                                Section::make(trans('ip.billing_cycle_configuration'))
                                    ->schema([
                                        Select::make('billing_interval')
                                            ->label(trans('ip.billing_interval'))
                                            ->options(
                                                collect(BillingInterval::cases())
                                                    ->mapWithKeys(fn ($i) => [$i->value => $i->label()])
                                                    ->toArray()
                                            )
                                            ->default(BillingInterval::MONTHLY->value)
                                            ->reactive()
                                            ->required(),

                                        Select::make('interval_unit')
                                            ->label(trans('ip.custom_unit'))
                                            ->options(
                                                collect(IntervalUnit::cases())
                                                    ->mapWithKeys(fn ($u) => [$u->value => $u->label()])
                                                    ->toArray()
                                            )
                                            ->default(IntervalUnit::MONTH->value)
                                            ->visible(fn (Get $get) => $get('billing_interval') === BillingInterval::CUSTOM->value)
                                            ->required(fn (Get $get) => $get('billing_interval') === BillingInterval::CUSTOM->value),

                                        TextInput::make('interval_count')
                                            ->label(trans('ip.custom_count'))
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->visible(fn (Get $get) => $get('billing_interval') === BillingInterval::CUSTOM->value)
                                            ->required(fn (Get $get) => $get('billing_interval') === BillingInterval::CUSTOM->value),

                                        TextInput::make('price')
                                            ->label(trans('ip.recurring_price'))
                                            ->numeric()
                                            ->prefix('$')
                                            ->required(),

                                        TextInput::make('currency_code')
                                            ->label(trans('ip.currency_code'))
                                            ->default('USD')
                                            ->maxLength(3),
                                    ])
                                    ->columns(2),
                            ]),

                        Schemas\Components\Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Section::make(trans('ip.lifecycle_and_trial_dates'))
                                    ->schema([
                                        DateTimePicker::make('starts_at')
                                            ->label(trans('ip.start_date'))
                                            ->default(now())
                                            ->required(),

                                        DateTimePicker::make('ends_at')
                                            ->label(trans('ip.expiration_date'))
                                            ->nullable(),

                                        DateTimePicker::make('trial_starts_at')
                                            ->label(trans('ip.trial_start_date'))
                                            ->nullable(),

                                        DateTimePicker::make('trial_ends_at')
                                            ->label(trans('ip.trial_end_date'))
                                            ->nullable(),

                                        TextInput::make('grace_period_days')
                                            ->label(trans('ip.grace_period_days'))
                                            ->numeric()
                                            ->default(0),

                                        DateTimePicker::make('grace_period_ends_at')
                                            ->label(trans('ip.grace_period_expiration'))
                                            ->nullable(),
                                    ])
                                    ->columns(1),
                            ]),
                    ]),

                Section::make(trans('ip.subscription_line_items'))
                    ->schema([
                        Repeater::make('subscriptionItems')
                            ->relationship('subscriptionItems')
                            ->schema([
                                Grid::make(5)
                                    ->schema([
                                        Select::make('product_id')
                                            ->label(trans('ip.product_service'))
                                            ->options(Product::query()->pluck('product_name', 'id')->toArray())
                                            ->searchable()
                                            ->preload()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                if ($product = Product::find($state)) {
                                                    $set('name', $product->product_name);
                                                    $set('unit_price', $product->product_price);
                                                }
                                            }),

                                        TextInput::make('name')
                                            ->label(trans('ip.description'))
                                            ->required(),

                                        TextInput::make('quantity')
                                            ->label(trans('ip.quantity'))
                                            ->numeric()
                                            ->default(1)
                                            ->required(),

                                        TextInput::make('unit_price')
                                            ->label(trans('ip.unit_price'))
                                            ->numeric()
                                            ->required(),

                                        TextInput::make('total')
                                            ->label(trans('ip.total'))
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->placeholder(trans('ip.total_auto_calc')),
                                    ]),
                            ])
                            ->columns(1)
                            ->defaultItems(1)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make(trans('ip.internal_notes'))
                    ->schema([
                        MarkdownEditor::make('notes')
                            ->label(trans('ip.subscription_notes'))
                            ->toolbarButtons(['bold', 'italic', 'bulletList'])
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->columnSpanFull(),
            ]);
    }
}
