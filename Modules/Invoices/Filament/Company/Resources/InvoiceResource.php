<?php

namespace Modules\Invoices\Filament\Company\Resources;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Modules\Invoices\Filament\Company\Resources\InvoiceResource\RelationManagers\CustomerRelationManager;
use Modules\Invoices\Filament\Company\Resources\InvoiceResource\RelationManagers\ExpenseRelationManager;
use Modules\Invoices\Filament\Company\Resources\InvoiceResource\RelationManagers\DocumentGroupRelationManager;
use Modules\Invoices\Filament\Company\Resources\InvoiceResource\RelationManagers\InvoiceItemsRelationManager;
use Modules\Invoices\Filament\Company\Resources\InvoiceResource\RelationManagers\QuoteRelationManager;
use Modules\Invoices\Filament\Company\Resources\InvoiceResource\RelationManagers\UserRelationManager;
use Modules\Invoices\Filament\Company\Resources\InvoiceResource\Pages\ListInvoices;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Modules\Core\Filament\Admin\Resources\AbstractTenantResource;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Filament\Company\Resources\InvoiceResource\Pages;
use Modules\Invoices\Filament\Company\Resources\InvoiceResource\RelationManagers;
use Modules\Invoices\Models\Invoice;

class InvoiceResource extends AbstractTenantResource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationGroup = 'Invoices';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 10;

    protected static bool $shouldRegisterNavigation = true;

    protected static bool $isScopedToTenant = true;

    public static function getModelLabel(): string
    {
        return trans('ip.invoices');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.invoices');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.invoices');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                // Top two-column: Client on left, Details on right
                //
                Grid::make(2)
                    ->schema([
                        Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Section::make(trans('ip.client'))
                                    ->schema([
                                        Select::make('customer_id')
                                            ->label(trans('ip.client'))
                                            ->relationship('customer', 'company_name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->createOptionForm([
                                                TextInput::make('company_name')
                                                    ->label(trans('ip.client_name'))
                                                    ->required(),
                                            ])
                                            ->reactive(),

                                        Placeholder::make('customer_info')
                                            ->label(trans('ip.client_information'))
                                            ->content(fn ($get) => optional($get('customer'))->company_name ?? '-'),
                                    ]),
                            ]),

                        Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Section::make(trans('ip.details'))
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('invoice_number')
                                            ->label(trans('ip.invoice_number'))
                                            ->required(),

                                        Select::make('invoice_status')
                                            ->label(trans('ip.invoice_status'))
                                            ->options(
                                                collect(InvoiceStatus::cases())
                                                    ->mapWithKeys(fn ($s) => [$s->value => trans($s->label())])
                                                    ->toArray()
                                            )
                                            ->getOptionLabelUsing(fn (string $value) => InvoiceStatus::from($value)->label())
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->required(),

                                        DatePicker::make('invoiced_at')
                                            ->label(trans('ip.invoice_date'))
                                            ->required(),

                                        DatePicker::make('invoice_due_at')
                                            ->label(trans('ip.invoice_due_at'))
                                            ->required(),

                                        TextInput::make('invoice_password')
                                            ->label(trans('ip.invoice_password')),
                                    ]),
                            ]),
                    ]),

                //
                // Items repeater (always visible, not collapsed)
                //
                // Invoice Items
                Section::make(trans('ip.invoice_items'))
                    ->collapsed()
                    ->schema([
                        Repeater::make('invoiceItems')
                            ->relationship('invoiceItems')
                            ->reorderable()
                            ->addActionLabel(trans('ip.add_new_row'))
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
                                            ->afterStateUpdated(fn (callable $set, callable $get) => static::updateItemTotals($set, $get)),

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
                                    ]),
                            ])
                            ->columns(1)
                            ->reactive()
                            ->afterStateUpdated(
                                fn ($set, $get) => static::updateGrandTotal($set, $get, 'invoiceItems', 'subtotal', 'invoice_item_subtotal')
                            ),
                    ])
                    ->columnSpanFull(),

                // Totals
                Section::make(trans('ip.invoice_amounts'))
                    ->columns(2)
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                // Left side reserved (e.g. “Add Item” button later)
                                Group::make()->schema([]),

                                // Right side: the actual totals
                                Group::make()
                                    ->schema([
                                        TextInput::make('invoice_item_subtotal')
                                            ->label(trans('ip.subtotal'))
                                            ->inlineLabel()
                                            ->disabled()
                                            ->dehydrated()
                                            ->reactive()
                                            ->afterStateUpdated(function (callable $set, callable $get): void {
                                                static::updateGrandTotal($set, $get);
                                            }),

                                        TextInput::make('invoice_discount_amount')
                                            ->label(trans('ip.discount_amount'))
                                            ->inlineLabel()
                                            ->nullable(),

                                        TextInput::make('invoice_discount_percent')
                                            ->label(trans('ip.discount_percent'))
                                            ->inlineLabel()
                                            ->nullable(),

                                        TextInput::make('invoice_tax_total')
                                            ->label(trans('ip.tax_total'))
                                            ->inlineLabel()
                                            ->disabled(),

                                        TextInput::make('invoice_total')
                                            ->label(trans('ip.invoice_total'))
                                            ->inlineLabel()
                                            ->disabled(),
                                    ]),
                            ]),
                    ])
                    ->collapsed()
                    ->columnSpanFull(),

                // Notes & Attachments
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        Section::make(trans('ip.notes'))
                            ->collapsed()
                            ->schema([
                                MarkdownEditor::make('notes')
                                    ->label(trans('ip.notes'))
                                    ->toolbarButtons(['bold', 'italic']),
                            ])
                            ->columnSpan(1),

                        Section::make(trans('ip.attachments'))
                            ->collapsed()
                            ->schema([
                                FileUpload::make('attachments')
                                    ->label(trans('ip.attachments'))
                                    ->multiple(),
                            ])
                            ->columnSpan(1),
                    ]),

                // Invoice Terms
                Section::make(trans('ip.invoice_terms'))
                    ->collapsed()
                    ->schema([
                        MarkdownEditor::make('invoice_terms')
                            ->toolbarButtons(['bold', 'italic'])
                            ->label(trans('ip.invoice_terms')),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_status')
                    ->formatStateUsing(function ($state) {
                        $status = $state instanceof InvoiceStatus ? $state : InvoiceStatus::tryFrom($state);

                        return $status?->label();
                    })
                    ->color(function ($state) {
                        $status = $state instanceof InvoiceStatus ? $state : InvoiceStatus::tryFrom($state);

                        return $status?->color() ?? 'secondary';
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('customer.company_name')->limit(10)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('invoice_due_at')
                    ->date()
                    ->since()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('invoice_total')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([])
            ->actions([
                ActionGroup::make([
                    EditAction::make()->modalWidth('7xl'),
                    Action::make('download pdf')
                        ->label(trans('ip.download_pdf'))
                        ->modalDescription(
                            'todo: make sure we can download the PDF of the Invoice through an action,
                            so need for modal anymore'
                        )
                        ->action(function (Invoice $record): void {}),
                    Action::make('send email')
                        ->label(trans('ip.send_email'))
                        ->modalDescription('todo: make sure we can email the Invoice through an action,
                            so need for modal anymore')
                        ->action(function (Invoice $record): void {}),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('invoice_due_at', 'desc');
    }

    /**
     * - customer (BelongsTo)
     * - invoice (BelongsTo)
     * - user (BelongsTo).
     */
    public static function getRelations(): array
    {
        return [
            CustomerRelationManager::class,
            ExpenseRelationManager::class,
            DocumentGroupRelationManager::class,
            InvoiceItemsRelationManager::class,
            QuoteRelationManager::class,
            UserRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInvoices::route('/'),
        ];
    }
}
