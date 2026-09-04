<?php

namespace Modules\Quotes\Filament\Company\Resources\Quotes\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Enums\ReportTemplateType;
use Modules\Core\Filament\Company\Actions\InsertNoteTemplateAction;
use Modules\Core\Models\Setting;
use Modules\Core\Services\ReportTemplateStorage;
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
                Grid::make(5)
                    ->columnSpanFull()
                    ->schema([
                        Group::make()
                            ->schema([
                                Select::make('prospect_id')
                                    ->label(trans('ip.customer_name'))
                                    ->relationship('prospect', 'company_name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('company_name')
                                            ->label(trans('ip.customer_name'))
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
                                            ->content(fn (Get $get) => optional($get('prospect'))->company_name ?? '-'),
                                    ])
                                    ->columns(1)
                                    ->visible(fn (Get $get) => filled($get('prospect_id'))),
                            ])
                            ->columnSpan(3),

                        Group::make()
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('quote_number')
                                            ->label(trans('ip.quote_number'))
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
                                                // Only (re)generate on create, and only when the field is still
                                                // empty -- never clobber a number the user already typed or one
                                                // that was already generated for this record.
                                                if ($operation !== 'create' || filled($get('quote_number'))) {
                                                    return;
                                                }

                                                $set('quote_number', self::generateQuoteNumber($get));
                                            }),

                                        DatePicker::make('quoted_at')
                                            ->label(trans('ip.quote_date'))
                                            ->default(now())
                                            ->native(false),

                                        DatePicker::make('quote_expires_at')
                                            ->label(trans('ip.quote_expires_at'))
                                            ->native(false),

                                        Select::make('numbering_id')
                                            ->label(trans('ip.numbering'))
                                            ->relationship('numbering', 'name', fn ($query) => $query->where('type', NumberingType::QUOTE->value))
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->native(false),

                                        TextInput::make('client_reference')
                                            ->label(trans('ip.client_reference'))
                                            ->maxLength(255),

                                        TextInput::make('work_order')
                                            ->label(trans('ip.work_order'))
                                            ->maxLength(255),

                                        Select::make('template')
                                            ->label(trans('ip.pdf_template'))
                                            ->options(fn (): array => app(ReportTemplateStorage::class)->optionsForType(ReportTemplateType::QUOTE))
                                            ->placeholder(trans('ip.company_default_template'))
                                            ->native(false)
                                            ->nullable(),
                                    ])
                                    ->columns(2),
                            ])
                            ->columnSpan(2),
                    ]),

                Section::make(trans('ip.quote_items'))
                    ->schema([
                        Repeater::make('quoteItems')
                            ->relationship('quoteItems')
                            ->label(trans('ip.quote_items'))
                            ->reorderable()
                            ->addActionLabel(trans('ip.add_new_row'))
                            ->schema([
                                Grid::make(6)
                                    ->schema([
                                        Select::make('product_id')
                                            ->label(trans('ip.product'))
                                            ->options(Product::query()->pluck('product_name', 'id')->toArray())
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->placeholder(trans('ip.select_product'))
                                            ->reactive()
                                            ->afterStateUpdated(function (callable $set, $state) {
                                                $product = Product::query()->find($state);
                                                $set('product_name', $product?->product_name ?? '');
                                            }),

                                        TextEntry::make('product_name')
                                            ->disabled(),

                                        TextInput::make('quantity')
                                            ->label(trans('ip.quantity'))
                                            ->numeric()
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(fn (callable $set, callable $get) => (new QuoteCalculator())->updateItemTotals($set, $get)),

                                        TextInput::make('price')
                                            ->label(trans('ip.price'))
                                            ->numeric()
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(fn (callable $set, callable $get) => (new QuoteCalculator())->updateItemTotals($set, $get)),

                                        TextInput::make('discount')
                                            ->label(trans('ip.discount'))
                                            ->numeric()
                                            ->default(0)
                                            ->reactive()
                                            ->afterStateUpdated(fn (callable $set, callable $get) => (new QuoteCalculator())->updateItemTotals($set, $get)),

                                        TextInput::make('subtotal')
                                            ->label(trans('ip.subtotal'))
                                            ->dehydrated()
                                            ->disabled(),
                                    ])
                                    ->columns(5),
                            ])
                            ->columns(1)
                            ->reactive()
                            ->dehydrated()
                            ->defaultItems(0)
                            ->afterStateUpdated(function (callable $set, $get, $state) {}),
                    ])
                    ->collapsed()
                    ->columnSpanFull(),

                Section::make(trans('ip.quote_totals'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Group::make()
                                    ->schema([]),

                                Group::make()
                                    ->schema([
                                        TextInput::make('quote_subtotal')
                                            ->label(trans('ip.subtotal'))
                                            ->disabled()
                                            ->dehydrated()
                                            ->reactive()
                                            ->afterStateUpdated(function (callable $set, callable $get) {
                                                (new QuoteCalculator())->updateGrandTotal($set, $get, 'quoteItems', 'subtotal', 'quote_item_subtotal');
                                            }),

                                        TextInput::make('quote_discount_amount')
                                            ->label(trans('ip.discount_amount'))
                                            ->nullable(),

                                        TextInput::make('quote_discount_percent')
                                            ->label(trans('ip.discount_percent'))
                                            ->nullable()
                                            ->dehydrated(false),

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
