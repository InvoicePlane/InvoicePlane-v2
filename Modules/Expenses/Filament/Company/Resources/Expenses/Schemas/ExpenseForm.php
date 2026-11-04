<?php

namespace Modules\Expenses\Filament\Company\Resources\Expenses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Log;
use Modules\Clients\Enums\RelationType;
use Modules\Expenses\Enums\ExpenseStatus;
use Modules\Expenses\Enums\ExpenseType;
use Modules\Expenses\Support\ExpenseCalculator;
use Modules\Expenses\Support\ExpenseNumberGenerator;
use Modules\Products\Models\Product;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make(trans('ip.details'))
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->columnSpan(2)
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('expense_number')
                                            ->label(trans('ip.expense_number'))
                                            ->prefixIcon(Heroicon::OutlinedHashtag)
                                            ->required()
                                            ->columnSpan(2)
                                            ->default(function (Get $get, string $operation) {
                                                if ($operation !== 'create') {
                                                    return;
                                                }

                                                $user      = auth()->user();
                                                $companyId = $user?->getCurrentCompanyId();

                                                if (config('app.extreme_logging')) {
                                                    Log::debug('ExpenseForm: Initializing ExpenseNumberGenerator', [
                                                        'company_id'         => $companyId,
                                                        'expense_status'     => $get('expense_status'),
                                                        'user_id'            => $user?->id,
                                                        'session_company_id' => session('current_company_id'),
                                                        'trace'              => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
                                                    ]);
                                                }

                                                $generator = new ExpenseNumberGenerator($companyId);

                                                return $generator->generate();
                                            })
                                            ->dehydrated(),

                                        Select::make('expense_status')
                                            ->label(trans('ip.expense_status'))
                                            ->prefixIcon(Heroicon::OutlinedCheckBadge)
                                            ->options(ExpenseStatus::options())
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->required()
                                            ->columnSpan(2),

                                        Select::make('category_id')
                                            ->label(trans('ip.category'))
                                            ->prefixIcon(Heroicon::OutlinedTag)
                                            ->relationship('expenseCategory', 'category_name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->columnSpan(2),

                                        Select::make('expense_type')
                                            ->label(trans('ip.expense_type'))
                                            ->prefixIcon(Heroicon::OutlinedBriefcase)
                                            ->options(ExpenseType::options())
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->required()
                                            ->columnSpan(2),

                                        TextInput::make('expense_amount')
                                            ->label(trans('ip.expense_amount'))
                                            ->prefixIcon(Heroicon::OutlinedCurrencyDollar)
                                            ->numeric()
                                            ->required()
                                            ->columnSpan(2),

                                        DatePicker::make('expensed_at')
                                            ->label(trans('ip.expensed_at'))
                                            ->prefixIcon(Heroicon::OutlinedCalendar)
                                            ->required()
                                            ->columnSpan(2),
                                    ]),
                            ]),

                        Section::make(trans('ip.contact'))
                            ->icon(Heroicon::OutlinedUserGroup)
                            ->columnSpan(1)
                            ->schema([
                                Select::make('customer_id')
                                    ->label(trans('ip.client'))
                                    ->prefixIcon(Heroicon::OutlinedUserGroup)
                                    ->relationship(
                                        name: 'customer',
                                        titleAttribute: 'company_name',
                                        modifyQueryUsing: fn ($query) => $query->where('relation_type', RelationType::CUSTOMER->value)
                                    )
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->native(false),

                                Select::make('vendor_id')
                                    ->label(trans('ip.vendor'))
                                    ->prefixIcon(Heroicon::OutlinedBuildingOffice2)
                                    ->relationship(
                                        name: 'vendor',
                                        titleAttribute: 'company_name',
                                        modifyQueryUsing: fn ($query) => $query->where('relation_type', RelationType::VENDOR->value)
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->native(false),
                            ]),
                    ]),

                Section::make(trans('ip.expense_items'))
                    ->icon(Heroicon::OutlinedListBullet)
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('expenseItems')
                            ->defaultItems(0)
                            ->relationship('expenseItems')
                            ->label(trans('ip.expense_items'))
                            ->reorderable()
                            ->addActionLabel(trans('ip.add_new_row'))
                            ->schema([
                                Grid::make(12)
                                    ->schema([
                                        Select::make('item_id')
                                            ->label(trans('ip.item'))
                                            ->prefixIcon(Heroicon::OutlinedCube)
                                            ->options(Product::query()->pluck('product_name', 'id')->toArray())
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->dehydrated()
                                            ->columnSpan(4),

                                        TextInput::make('quantity')
                                            ->label(trans('ip.quantity'))
                                            ->prefixIcon(Heroicon::OutlinedHashtag)
                                            ->numeric()
                                            ->required()
                                            ->dehydrated()
                                            ->columnSpan(2),

                                        TextInput::make('price')
                                            ->label(trans('ip.price'))
                                            ->prefixIcon(Heroicon::OutlinedCurrencyDollar)
                                            ->numeric()
                                            ->required()
                                            ->dehydrated()
                                            ->columnSpan(2),

                                        TextInput::make('discount')
                                            ->label(trans('ip.discount'))
                                            ->prefixIcon(Heroicon::OutlinedMinusCircle)
                                            ->numeric()
                                            ->default(0)
                                            ->dehydrated()
                                            ->columnSpan(2),

                                        TextInput::make('subtotal')
                                            ->label(trans('ip.subtotal'))
                                            ->prefixIcon(Heroicon::OutlinedBanknotes)
                                            ->numeric()
                                            ->default(0)
                                            ->dehydrated()
                                            ->disabled()
                                            ->columnSpan(2),
                                    ]),
                            ])
                            ->columns(1)
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set, callable $get) => (new ExpenseCalculator())->updateGrandTotal($set, $get, 'expenseItems', 'subtotal', 'expense_item_subtotal')),
                    ]),

                Section::make(trans('ip.expense_notes'))
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->schema([
                        MarkdownEditor::make('description')
                            ->label(trans('ip.expense_notes'))
                            ->toolbarButtons(['bold', 'italic']),
                    ])
                    ->collapsed()
                    ->columnSpanFull(),
            ]);
    }
}
