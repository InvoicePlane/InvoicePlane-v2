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
                                Section::make('Subscription Overview')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Subscription Title')
                                            ->required()
                                            ->placeholder('e.g. Enterprise Monthly Software License')
                                            ->columnSpanFull(),

                                        Select::make('customer_id')
                                            ->label('Client / Customer')
                                            ->relationship('customer', 'company_name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        TextInput::make('number')
                                            ->label('Subscription Code / Ref')
                                            ->default(fn () => 'SUB-' . mb_strtoupper(mb_substr(uniqid(), -6)))
                                            ->required(),

                                        Select::make('status')
                                            ->label('Subscription Status')
                                            ->options(
                                                collect(SubscriptionStatus::cases())
                                                    ->mapWithKeys(fn ($s) => [$s->value => $s->label()])
                                                    ->toArray()
                                            )
                                            ->default(SubscriptionStatus::ACTIVE->value)
                                            ->required(),
                                    ])
                                    ->columns(2),

                                Section::make('Billing Cycle Configuration')
                                    ->schema([
                                        Select::make('billing_interval')
                                            ->label('Billing Interval')
                                            ->options(
                                                collect(BillingInterval::cases())
                                                    ->mapWithKeys(fn ($i) => [$i->value => $i->label()])
                                                    ->toArray()
                                            )
                                            ->default(BillingInterval::MONTHLY->value)
                                            ->reactive()
                                            ->required(),

                                        Select::make('interval_unit')
                                            ->label('Custom Unit')
                                            ->options(
                                                collect(IntervalUnit::cases())
                                                    ->mapWithKeys(fn ($u) => [$u->value => $u->label()])
                                                    ->toArray()
                                            )
                                            ->default(IntervalUnit::MONTH->value)
                                            ->visible(fn (Get $get) => $get('billing_interval') === BillingInterval::CUSTOM->value)
                                            ->required(fn (Get $get) => $get('billing_interval') === BillingInterval::CUSTOM->value),

                                        TextInput::make('interval_count')
                                            ->label('Custom Count (Frequency)')
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->visible(fn (Get $get) => $get('billing_interval') === BillingInterval::CUSTOM->value)
                                            ->required(fn (Get $get) => $get('billing_interval') === BillingInterval::CUSTOM->value),

                                        TextInput::make('price')
                                            ->label('Recurring Price')
                                            ->numeric()
                                            ->prefix('$')
                                            ->required(),
                                    ])
                                    ->columns(2),
                            ]),

                        Schemas\Components\Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Section::make('Lifecycle & Trial Dates')
                                    ->schema([
                                        DateTimePicker::make('starts_at')
                                            ->label('Start Date')
                                            ->default(now())
                                            ->required(),

                                        DateTimePicker::make('ends_at')
                                            ->label('Expiration / End Date')
                                            ->nullable(),

                                        DateTimePicker::make('trial_starts_at')
                                            ->label('Trial Start Date')
                                            ->nullable(),

                                        DateTimePicker::make('trial_ends_at')
                                            ->label('Trial End Date')
                                            ->nullable(),

                                        TextInput::make('grace_period_days')
                                            ->label('Grace Period (Days)')
                                            ->numeric()
                                            ->default(0),

                                        DateTimePicker::make('grace_period_ends_at')
                                            ->label('Grace Period Expiration')
                                            ->nullable(),
                                    ])
                                    ->columns(1),
                            ]),
                    ]),

                Section::make('Subscription Line Items')
                    ->schema([
                        Repeater::make('subscriptionItems')
                            ->relationship('subscriptionItems')
                            ->schema([
                                Grid::make(5)
                                    ->schema([
                                        Select::make('product_id')
                                            ->label('Product / Service')
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
                                            ->label('Description')
                                            ->required(),

                                        TextInput::make('quantity')
                                            ->label('Qty')
                                            ->numeric()
                                            ->default(1)
                                            ->required(),

                                        TextInput::make('unit_price')
                                            ->label('Unit Price')
                                            ->numeric()
                                            ->required(),

                                        TextInput::make('total')
                                            ->label('Total')
                                            ->numeric()
                                            ->placeholder('Auto-calc'),
                                    ]),
                            ])
                            ->columns(1)
                            ->defaultItems(1)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('Internal Notes')
                    ->schema([
                        MarkdownEditor::make('notes')
                            ->label('Subscription Notes')
                            ->toolbarButtons(['bold', 'italic', 'bulletList'])
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->columnSpanFull(),
            ]);
    }
}
