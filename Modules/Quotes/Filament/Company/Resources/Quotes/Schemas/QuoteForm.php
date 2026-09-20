<?php

namespace Modules\Quotes\Filament\Company\Resources\Quotes\Schemas;

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
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Services\RelationService;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Filament\Company\Actions\InsertNoteTemplateAction;
use Modules\Core\Models\Setting;
use Modules\Products\Models\Product;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Support\QuoteCalculator;
use Modules\Quotes\Support\QuoteNumberGenerator;

class QuoteForm
{
    public static function configure(Schema $schema): Schema
    {
        if ( ! $schema->getRecord() && ! $schema->getState()) {
            $schema->state([]);
        }

        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make(trans('ip.quote_details'))
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->columnSpan(2)
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('prospect_id')
                                            ->label(trans('ip.customer_name'))
                                            ->prefixIcon(Heroicon::OutlinedUserGroup)
                                            ->relationship('prospect', 'company_name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->createOptionForm([
                                                TextInput::make('company_name')
                                                    ->label(trans('ip.customer_name'))
                                                    ->required()
                                                    ->maxLength(150),
                                            ])
                                            ->createOptionUsing(function (array $data): int {
                                                return app(RelationService::class)->createRelation([
                                                    'relation_type' => RelationType::PROSPECT->value,
                                                    'company_name'  => $data['company_name'],
                                                ])->getKey();
                                            })
                                            ->reactive(),

                                        Select::make('numbering_id')
                                            ->label(trans('ip.numbering'))
                                            ->prefixIcon(Heroicon::OutlinedQueueList)
                                            ->relationship('numbering', 'name', fn ($query) => $query->where('type', NumberingType::QUOTE->value))
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->native(false),

                                        TextInput::make('quote_number')
                                            ->label(trans('ip.quote_number'))
                                            ->prefixIcon(Heroicon::OutlinedHashtag)
                                            ->required()
                                            ->default(function (Get $get, string $operation) {
                                                if ($operation !== 'create') {
                                                    return;
                                                }

                                                return self::generateQuoteNumber($get);
                                            })
                                            ->dehydrated(),

                                        Select::make('quote_status')
                                            ->label(trans('ip.quote_status'))
                                            ->prefixIcon(Heroicon::OutlinedCheckBadge)
                                            ->required()
                                            ->options(
                                                collect(QuoteStatus::cases())
                                                    ->mapWithKeys(fn (QuoteStatus $status) => [
                                                        $status->value => trans($status->label()),
                                                    ])
                                                    ->toArray()
                                            )
                                            ->getOptionLabelUsing(
                                                fn ($value) => $value instanceof QuoteStatus
                                                    ? $value->label()
                                                    : QuoteStatus::tryFrom($value)?->label() ?? $value
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->reactive()
                                            ->afterStateUpdated(function (callable $set, Get $get, string $operation): void {
                                                if ($operation !== 'create' || filled($get('quote_number'))) {
                                                    return;
                                                }

                                                $set('quote_number', self::generateQuoteNumber($get));
                                            }),

                                        DatePicker::make('quoted_at')
                                            ->label(trans('ip.quote_date'))
                                            ->prefixIcon(Heroicon::OutlinedCalendar)
                                            ->default(now())
                                            ->native(false),

                                        DatePicker::make('quote_expires_at')
                                            ->label(trans('ip.quote_expires_at'))
                                            ->prefixIcon(Heroicon::OutlinedCalendarDays)
                                            ->native(false),

                                        TextInput::make('client_reference')
                                            ->label(trans('ip.client_reference'))
                                            ->prefixIcon(Heroicon::OutlinedBookmark)
                                            ->maxLength(255),

                                        TextInput::make('work_order')
                                            ->label(trans('ip.work_order'))
                                            ->prefixIcon(Heroicon::OutlinedClipboardDocumentList)
                                            ->maxLength(255),
                                    ]),
                            ]),

                        Section::make(trans('ip.quote_totals'))
                            ->icon(Heroicon::OutlinedCalculator)
                            ->columnSpan(1)
                            ->columns(2)
                            ->schema([
                                TextInput::make('quote_subtotal')
                                    ->label(trans('ip.subtotal'))
                                    ->prefixIcon(Heroicon::OutlinedBanknotes)
                                    ->disabled()
                                    ->dehydrated()
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, callable $get) {
                                        (new QuoteCalculator())->updateGrandTotal($set, $get, 'quoteItems', 'subtotal', 'quote_item_subtotal');
                                    }),

                                TextInput::make('quote_discount_amount')
                                    ->label(trans('ip.discount_amount'))
                                    ->prefixIcon(Heroicon::OutlinedMinusCircle)
                                    ->nullable(),

                                TextInput::make('quote_discount_percent')
                                    ->label(trans('ip.discount_percent'))
                                    ->prefixIcon(Heroicon::OutlinedReceiptPercent)
                                    ->nullable()
                                    ->dehydrated(false),

                                TextInput::make('quote_tax_total')
                                    ->label(trans('ip.tax_total'))
                                    ->prefixIcon(Heroicon::OutlinedBuildingLibrary)
                                    ->disabled(),

                                TextInput::make('quote_total')
                                    ->label(trans('ip.total'))
                                    ->prefixIcon(Heroicon::OutlinedCurrencyDollar)
                                    ->disabled()
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make(trans('ip.quote_items'))
                    ->icon(Heroicon::OutlinedListBullet)
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('quoteItems')
                            ->relationship('quoteItems')
                            ->label(trans('ip.quote_items'))
                            ->reorderable()
                            ->addActionLabel(trans('ip.add_new_row'))
                            ->schema([
                                Grid::make(12)
                                    ->schema([
                                        Select::make('product_id')
                                            ->label(trans('ip.product'))
                                            ->prefixIcon(Heroicon::OutlinedCube)
                                            ->options(Product::query()->pluck('product_name', 'id')->toArray())
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->placeholder(trans('ip.select_product'))
                                            ->columnSpan(4),

                                        TextInput::make('quantity')
                                            ->label(trans('ip.quantity'))
                                            ->prefixIcon(Heroicon::OutlinedHashtag)
                                            ->numeric()
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(fn (callable $set, callable $get) => (new QuoteCalculator())->updateItemTotals($set, $get))
                                            ->columnSpan(2),

                                        TextInput::make('price')
                                            ->label(trans('ip.price'))
                                            ->prefixIcon(Heroicon::OutlinedCurrencyDollar)
                                            ->numeric()
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(fn (callable $set, callable $get) => (new QuoteCalculator())->updateItemTotals($set, $get))
                                            ->columnSpan(2),

                                        TextInput::make('discount')
                                            ->label(trans('ip.discount'))
                                            ->prefixIcon(Heroicon::OutlinedMinusCircle)
                                            ->numeric()
                                            ->default(0)
                                            ->reactive()
                                            ->afterStateUpdated(fn (callable $set, callable $get) => (new QuoteCalculator())->updateItemTotals($set, $get))
                                            ->columnSpan(2),

                                        TextInput::make('subtotal')
                                            ->label(trans('ip.subtotal'))
                                            ->prefixIcon(Heroicon::OutlinedBanknotes)
                                            ->dehydrated()
                                            ->disabled()
                                            ->columnSpan(2),
                                    ]),
                            ])
                            ->columns(1)
                            ->reactive()
                            ->dehydrated()
                            ->defaultItems(0)
                            ->afterStateUpdated(function (callable $set, $get, $state) {}),
                    ]),

                Section::make(trans('ip.quote_notes'))
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->schema([
                        MarkdownEditor::make('notes')
                            ->label(trans('ip.notes'))
                            ->hintAction(InsertNoteTemplateAction::make('notes')),
                    ])
                    ->collapsed()
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Generate a quote number for the create form, respecting the
     * generate_quote_number_for_draft setting (default true) for draft
     * status. Returns null when generation is skipped or no numbering
     * scheme is available.
     */
    private static function generateQuoteNumber(Get $get): ?string
    {
        $status = $get('quote_status') ?? QuoteStatus::DRAFT->value;

        if (
            $status === QuoteStatus::DRAFT->value
            && ! Setting::getBool('generate_quote_number_for_draft')
        ) {
            return null;
        }

        $companyId = auth()->user()?->getCurrentCompanyId();
        $generator = new QuoteNumberGenerator($companyId);

        // Prefer the explicitly selected numbering scheme; otherwise fall
        // back to any Quote-type scheme for the company instead of the
        // generator's conventional "Default Quote Numbering" group name,
        // which seeded/company-created schemes won't necessarily carry.
        if ($numberingId = $get('numbering_id')) {
            $generator->forNumberingId((int) $numberingId);
        } else {
            $generator->forNumbering('');
        }

        return $generator->generate();
    }
}
