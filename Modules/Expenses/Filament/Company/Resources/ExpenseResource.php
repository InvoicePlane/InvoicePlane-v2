<?php

namespace Modules\Expenses\Filament\Company\Resources;

use Modules\Expenses\Enums\ExpenseType;

use Modules\Core\Helpers\EnumHelper;

use Modules\Expenses\Models\Expense;

use Modules\Expenses\Filament\Company\Resources\ExpenseResource;

use Modules\Core\Support\Results\Expenses;

use Modules\Expenses\Enums\ExpenseStatus;

use Modules\Core\Models\Company;

use Modules\Expenses\Filament\Company\Resources\ExpenseResource\Pages\ListExpenses;

use Modules\Core\Filament\Admin\Resources\AbstractTenantResource;

use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Modules\Expenses\Filament\Company\Resources\ExpenseResource\Pages;

class ExpenseResource extends AbstractTenantResource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationGroup = 'Expenses';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 10;

    protected static bool $shouldRegisterNavigation = true;

    protected static bool $isScopedToTenant = true;

    public static function getModelLabel(): string
    {
        return trans('ip.expense');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.expenses');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.expenses');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        Group::make()
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Select::make('customer_id')
                                            ->relationship('customer', 'company_name')
                                            ->label(trans('ip.customer'))
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->native(false),

                                        Fieldset::make(trans('ip.customer_information'))
                                            ->extraAttributes([
                                                'class' => '!border-curious-200 dark:!border-curious-600 rounded-2xl !p-4',
                                            ])
                                            ->schema([
                                                Placeholder::make('customer_info')
                                                    ->label(trans('ip.customer'))
                                                    ->content(fn (Get $get) => optional($get('customer'))->company_name ?? '-'),
                                            ])
                                            ->columns(1)
                                            ->visible(fn (Get $get) => filled($get('customer_id'))),
                                    ])
                                    ->collapsed(false),

                                Section::make()
                                    ->schema([
                                        Select::make('vendor_id')
                                            ->relationship('vendor', 'company_name')
                                            ->label(trans('ip.vendor'))
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->native(false),

                                        Fieldset::make(trans('ip.vendor_information'))
                                            ->extraAttributes([
                                                'class' => '!border-curious-200 dark:!border-curious-600 rounded-2xl !p-4',
                                            ])
                                            ->schema([
                                                Placeholder::make('vendor_info')
                                                    ->label(trans('ip.vendor'))
                                                    ->content(fn (Get $get) => optional($get('vendor'))->company_name ?? '-'),
                                            ])
                                            ->columns(1)
                                            ->visible(fn (Get $get) => filled($get('vendor_id'))),
                                    ])
                                    ->collapsed(false),
                            ]),

                        Group::make()
                            ->schema([
                                Section::make(trans('ip.details'))
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('expense_number')
                                                    ->label(trans('ip.expense_number')),

                                                Select::make('expense_status')
                                                    ->label(trans('ip.expense_status'))
                                                    ->options(
                                                        collect(ExpenseStatus::cases())
                                                            ->mapWithKeys(fn (ExpenseStatus $status) => [
                                                                $status->value => trans($status->label()),
                                                            ])
                                                            ->toArray()
                                                    )
                                                    ->getOptionLabelUsing(fn (string $value) => ExpenseStatus::from($value)->label())
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->native(false),

                                                Select::make('category_id')
                                                    ->relationship('expenseCategory', 'category_name')
                                                    ->label(trans('ip.category'))
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->native(false),

                                                Select::make('expense_type')
                                                    ->label(trans('ip.expense_type'))
                                                    ->options(
                                                        collect(ExpenseType::cases())
                                                            ->mapWithKeys(fn (ExpenseType $type) => [
                                                                $type->value => trans($type->label()),
                                                            ])
                                                            ->toArray()
                                                    )
                                                    ->getOptionLabelUsing(fn (string $value) => ExpenseType::from($value)->label())
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->native(false),

                                                TextInput::make('expense_amount')
                                                    ->label(trans('ip.amount'))
                                                    ->numeric()
                                                    ->required(),
                                            ])
                                            ->columns(2),
                                    ])
                                    ->collapsed(false),
                            ]),
                    ]),

                Section::make(trans('ip.expense_items'))
                    ->schema([
                        Repeater::make('expenseItems')
                            ->relationship('expenseItems')
                            ->reorderable()
                            ->addActionLabel(trans('ip.add_row'))
                            ->schema([
                                Grid::make(5)
                                    ->schema([
                                        TextInput::make('item_name')
                                            ->label(trans('ip.item'))
                                            ->required(),

                                        TextInput::make('quantity')
                                            ->label(trans('ip.quantity'))
                                            ->numeric()
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(fn ($state, callable $set, callable $get) => static::updateItemTotals($set, $get)),

                                        TextInput::make('price')
                                            ->label(trans('ip.price'))
                                            ->numeric()
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(fn ($state, callable $set, callable $get) => static::updateItemTotals($set, $get)),

                                        TextInput::make('discount')
                                            ->label(trans('ip.discount'))
                                            ->numeric()
                                            ->reactive()
                                            ->afterStateUpdated(fn ($state, callable $set, callable $get) => static::updateItemTotals($set, $get)),

                                        TextInput::make('subtotal')
                                            ->label(trans('ip.subtotal'))
                                            ->disabled(),
                                    ])
                                    ->columns(5),
                            ])
                            ->columns(1)
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set, callable $get) => static::updateGrandTotal($set, $get, 'expenseItems', 'subtotal', 'expense_item_subtotal')),
                    ])
                    ->collapsed(true)
                    ->columnSpanFull(),

                Section::make(trans('ip.expense_totals'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Group::make()
                                    ->schema([]), // Optional button space

                                Group::make()
                                    ->schema([
                                        TextInput::make('expense_item_subtotal')
                                            ->label(trans('ip.subtotal'))
                                            ->disabled()
                                            ->dehydrated()
                                            ->reactive()
                                            ->afterStateUpdated(fn (callable $set, callable $get) => static::updateGrandTotal($set, $get, 'expenseItems', 'subtotal', 'expense_item_subtotal')),

                                        TextInput::make('expense_discount_amount')
                                            ->label(trans('ip.discount_amount'))
                                            ->nullable(),

                                        TextInput::make('expense_discount_percent')
                                            ->label(trans('ip.discount_percent'))
                                            ->nullable(),

                                        TextInput::make('expense_tax_total')
                                            ->label(trans('ip.tax_total'))
                                            ->disabled(),

                                        TextInput::make('expense_total')
                                            ->label(trans('ip.total'))
                                            ->disabled(),
                                    ]),
                            ]),
                    ])
                    ->columns(2)
                    ->collapsed(true),

                Section::make(trans('ip.expense_notes'))
                    ->schema([
                        MarkdownEditor::make('description')
                            ->label(trans('ip.notes'))
                            ->toolbarButtons(['bold', 'italic']),
                    ])
                    ->collapsed(true)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('expense_status')
                    ->formatStateUsing(function ($state) {
                        $status = EnumHelper::safeEnum(ExpenseStatus::class, $state);

                        return $status?->label() ?? '-';
                    })
                    ->color(function ($state) {
                        $status = EnumHelper::safeEnum(ExpenseStatus::class, $state);

                        return $status?->color() ?? 'secondary';
                    })
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('expenseCategory.category_name')
                    ->limit(10)
                    ->placeholder('-')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('expense_type')
                    ->formatStateUsing(function ($state) {
                        $status = EnumHelper::safeEnum(ExpenseType::class, $state);

                        return $status?->label() ?? '-';
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->hiddenFrom('md'),
                Tables\Columns\TextColumn::make('expense_number')->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('vendor.company_name')->limit(10)->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('expense_amount')->searchable()->sortable()->toggleable(),
            ])
            ->filters([
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make()->modalWidth('7xl'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * - expenseCategory (BelongsTo)
     * - vendor (BelongsTo).
     */
    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
        ];
    }
}
