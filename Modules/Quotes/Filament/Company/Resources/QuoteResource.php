<?php

namespace Modules\Quotes\Filament\Company\Resources;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Modules\Core\Filament\Admin\Resources\AbstractTenantResource;
use Modules\Core\Helpers\EnumHelper;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Filament\Company\Resources\QuoteResource\Pages\ListQuotes;
use Modules\Quotes\Filament\Company\Resources\QuoteResource\RelationManagers\DocumentGroupRelationManager;
use Modules\Quotes\Filament\Company\Resources\QuoteResource\RelationManagers\InvoiceRelationManager;
use Modules\Quotes\Filament\Company\Resources\QuoteResource\RelationManagers\UserRelationManager;
use Modules\Quotes\Models\Quote;

class QuoteResource extends AbstractTenantResource
{
    protected static ?string $model = Quote::class;

    protected static ?string $navigationGroup = 'Quotes';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 10;

    protected static bool $shouldRegisterNavigation = true;

    protected static bool $isScopedToTenant = true;

    public static function getModelLabel(): string
    {
        return trans('crud.quotes.itemTitle');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('crud.quotes.collectionTitle');
    }

    public static function getNavigationLabel(): string
    {
        return trans('crud.quotes.collectionTitle');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        // Left side (Client selector + Info)
                        Forms\Components\Group::make()
                            ->schema([
                                Select::make('prospect_id')
                                    ->label(trans('ip.client_name'))
                                    ->relationship('prospect', 'company_name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('company_name')
                                            ->label(trans('ip.client_name'))
                                            ->required(),
                                    ])
                                    ->reactive(),

                                Fieldset::make(trans('ip.client_information'))
                                    ->extraAttributes([
                                        'class' => '!border-curious-200 dark:!border-curious-600 rounded-2xl !p-4',
                                    ])
                                    ->schema([
                                        Placeholder::make('customer_info')
                                            ->label(trans('ip.client'))
                                            ->content(fn (Get $get) => optional($get('client'))->client_name ?? '-'),
                                    ])
                                    ->columns(1)
                                    ->visible(fn (Get $get) => filled($get('prospect_id'))),
                            ])
                            ->columnSpan(1),

                        Forms\Components\Group::make()
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('quote_number')
                                            ->label(trans('ip.quote_number'))
                                            ->required(),

                                        Select::make('quote_status')
                                            ->label(trans('ip.quote_status'))
                                            ->required()
                                            ->options(
                                                collect(QuoteStatus::cases())
                                                    ->mapWithKeys(fn (QuoteStatus $status) => [
                                                        $status->value => trans($status->label()),
                                                    ])
                                                    ->toArray()
                                            )
                                            ->getOptionLabelUsing(fn (string $value) => QuoteStatus::from($value)->label())
                                            ->searchable()
                                            ->preload()
                                            ->native(false),

                                        DatePicker::make('quoted_at')
                                            ->label(trans('ip.quote_date'))
                                            ->native(false),

                                        DatePicker::make('quote_expires_at')
                                            ->label(trans('ip.quote_expires_at'))
                                            ->native(false),

                                        Select::make('document_group_id')
                                            ->label(trans('ip.document_group'))
                                            ->relationship('documentGroup', 'document_group_name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->native(false),
                                    ])
                                    ->columns(2),
                            ])
                            ->columnSpan(1),
                    ]),

                Section::make(trans('ip.quote_items'))
                    ->schema([
                        Repeater::make('quoteItems')
                            ->relationship('quoteItems')
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
                                            ->afterStateUpdated(fn (callable $set, callable $get) => static::updateItemTotals($set, $get)),

                                        TextInput::make('price')
                                            ->label(trans('ip.price'))
                                            ->numeric()
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(fn (callable $set, callable $get) => static::updateItemTotals($set, $get)),

                                        TextInput::make('discount')
                                            ->label(trans('ip.discount'))
                                            ->numeric()
                                            ->default(0)
                                            ->reactive()
                                            ->afterStateUpdated(fn (callable $set, callable $get) => static::updateItemTotals($set, $get)),

                                        TextInput::make('subtotal')
                                            ->label(trans('ip.subtotal'))
                                            ->disabled(),
                                    ])
                                    ->columns(5),
                            ])
                            ->columns(1)
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set, callable $get) => static::updateGrandTotal($set, $get, 'quoteItems', 'subtotal', 'quote_item_subtotal')),
                    ])
                    ->collapsed()
                    ->columnSpanFull(),

                Section::make(trans('ip.quote_totals'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Group::make()
                                    ->schema([]), // Optional left column

                                Forms\Components\Group::make()
                                    ->schema([
                                        TextInput::make('quote_item_subtotal')
                                            ->label(trans('ip.subtotal'))
                                            ->disabled()
                                            ->dehydrated()
                                            ->reactive()
                                            ->afterStateUpdated(fn (callable $set, callable $get) => static::updateGrandTotal($set, $get, 'quoteItems', 'subtotal', 'quote_item_subtotal')),

                                        TextInput::make('quote_discount_amount')
                                            ->label(trans('ip.discount_amount'))
                                            ->nullable(),

                                        TextInput::make('quote_discount_percent')
                                            ->label(trans('ip.discount_percent'))
                                            ->nullable(),

                                        TextInput::make('quote_tax_total')
                                            ->label(trans('ip.tax_total'))
                                            ->disabled(),

                                        TextInput::make('quote_total')
                                            ->label(trans('ip.total'))
                                            ->disabled(),
                                    ]),
                            ]),
                    ])
                    ->collapsed()
                    ->columns(2),

                Section::make(trans('ip.quote_notes'))
                    ->schema([
                        MarkdownEditor::make('notes')
                            ->label(trans('ip.notes'))
                            ->toolbarButtons(['bold', 'italic']),
                    ])
                    ->collapsed()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quote_status')
                    ->label(trans('ip.quote_status'))
                    ->badge()
                    ->formatStateUsing(function (Quote $record) {
                        $status = EnumHelper::safeEnum(QuoteStatus::class, $record->quote_status);

                        return $status ? trans($status->label()) : '-';
                    })
                    ->color(function (Quote $record) {
                        $status = EnumHelper::safeEnum(QuoteStatus::class, $record->quote_status);

                        return $status?->color() ?? 'secondary';
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('quote_number')->searchable()->sortable()->toggleable(),
                TextColumn::make('prospect.company_name')->limit(10)->label(trans('ip.client_name'))->searchable()->sortable()->toggleable(),
                TextColumn::make('quote_expires_at')->date()->label(trans('ip.expires'))->color(fn (Quote $record) => Carbon::parse($record->quote_expires_at)->isPast() ? 'text-red-500' : null)->since()->searchable()->sortable()->toggleable(),
                TextColumn::make('quote_total')->searchable()->sortable()->toggleable(),
            ])
            ->filters([])
            ->actions([
                ActionGroup::make([
                    EditAction::make()->modalWidth('7xl'),
                    Action::make('download pdf')
                        ->label(trans('ip.download_pdf'))
                        ->modalDescription(
                            'todo: make sure we can download the PDF of the Quote through an action,
                            so need for modal anymore'
                        )
                        ->action(function (Quote $record): void {}),
                    Action::make('send email')
                        ->label(trans('ip.send_email'))
                        ->modalDescription('todo: make sure we can email the Quote through an action,
                            so need for modal anymore')
                        ->action(function (Quote $record): void {}),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('quote_expires_at', 'asc');
    }

    /**
     * - prospect (BelongsTo)
     * - user (BelongsTo).
     */
    public static function getRelations(): array
    {
        return [
            DocumentGroupRelationManager::class,
            InvoiceRelationManager::class,
            UserRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuotes::route('/'),
        ];
    }
}
