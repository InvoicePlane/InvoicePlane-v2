<?php

namespace Modules\Invoices\Filament\Company\Resources\Invoices\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
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
use Modules\Core\Support\DateHelpers;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Services\InvoiceService;
use Modules\Invoices\Support\InvoiceCalculator;
use Modules\Invoices\Support\InvoiceNumberGenerator;
use Modules\Products\Models\Product;

class InvoiceForm
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
                                        Select::make('customer_id')
                                            ->label(trans('ip.client'))
                                            ->prefixIcon(Heroicon::OutlinedUserGroup)
                                            ->relationship('customer', 'company_name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->columnSpan(2)
                                            ->createOptionForm([
                                                TextInput::make('company_name')
                                                    ->label(trans('ip.customer_name'))
                                                    ->required()
                                                    ->maxLength(150),
                                            ])
                                            ->createOptionUsing(function (array $data): int {
                                                return app(RelationService::class)->createRelation([
                                                    'relation_type' => RelationType::CUSTOMER->value,
                                                    'company_name'  => $data['company_name'],
                                                ])->getKey();
                                            })
                                            ->reactive(),

                                        Select::make('numbering_id')
                                            ->label(trans('ip.numbering'))
                                            ->prefixIcon(Heroicon::OutlinedQueueList)
                                            ->relationship('numbering', 'name', fn ($query) => $query->where('type', NumberingType::INVOICE->value))
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->columnSpan(2)
                                            ->exists(
                                                table: 'numbering',
                                                column: 'id',
                                                modifyRuleUsing: fn ($rule) => $rule
                                                    ->where('type', NumberingType::INVOICE->value)
                                                    ->where('company_id', Filament::getTenant()?->id),
                                            ),

                                        TextInput::make('invoice_number')
                                            ->label(trans('ip.invoice_number'))
                                            ->prefixIcon(Heroicon::OutlinedHashtag)
                                            ->required()
                                            ->default(function (Get $get, string $operation) {
                                                if ($operation !== 'create') {
                                                    return;
                                                }

                                                return self::generateInvoiceNumber($get);
                                            })
                                            ->columnSpan(2)
                                            ->dehydrated(),

                                        Select::make('invoice_status')
                                            ->label(trans('ip.invoice_status'))
                                            ->prefixIcon(Heroicon::OutlinedCheckBadge)
                                            ->options(
                                                collect(InvoiceStatus::cases())
                                                    ->mapWithKeys(fn ($s) => [$s->value => trans($s->label())])
                                                    ->toArray()
                                            )
                                            ->getOptionLabelUsing(
                                                fn ($value) => $value instanceof InvoiceStatus
                                                ? $value->label()
                                                : InvoiceStatus::tryFrom($value)?->label() ?? $value
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->required()
                                            ->reactive()
                                            ->columnSpan(2)
                                            ->afterStateUpdated(function (callable $set, Get $get, string $operation): void {
                                                // Only (re)generate on create, and only when the field is still
                                                // empty -- never clobber a number the user already typed or one
                                                // that was already generated for this record.
                                                if ($operation !== 'create' || filled($get('invoice_number'))) {
                                                    return;
                                                }

                                                $set('invoice_number', self::generateInvoiceNumber($get));
                                            }),

                                        DatePicker::make('invoiced_at')
                                            ->label(trans('ip.invoice_date'))
                                            ->prefixIcon(Heroicon::OutlinedCalendar)
                                            ->default(now())
                                            ->columnSpan(2)
                                            ->required(),

                                        DatePicker::make('invoice_due_at')
                                            ->label(trans('ip.invoice_due_at'))
                                            ->columnSpan(2)
                                            ->prefixIcon(Heroicon::OutlinedCalendarDays)
                                            ->required(),

                                        TextInput::make('client_reference')
                                            ->label(trans('ip.client_reference'))
                                            ->prefixIcon(Heroicon::OutlinedBookmark)
                                            ->maxLength(255),

                                        TextInput::make('work_order')
                                            ->label(trans('ip.work_order'))
                                            ->prefixIcon(Heroicon::OutlinedClipboardDocumentList)
                                            ->maxLength(255),

                                        TextInput::make('invoice_password')
                                            ->label(trans('ip.invoice_password'))
                                            ->prefixIcon(Heroicon::OutlinedLockClosed)
                                            ->columnSpan(2),

                                        TextEntry::make('last_reminder_sent')
                                            ->label(trans('ip.last_reminder_sent'))
                                            ->visible(fn (string $operation): bool => $operation === 'edit')
                                            ->columnSpan(2)
                                            ->state(function (?Invoice $record) {
                                                $lastSentAt = $record ? app(InvoiceService::class)->lastReminderSentAt($record) : null;

                                                return $lastSentAt
                                                    ? DateHelpers::formatDate($lastSentAt)
                                                    : trans('ip.reminder_never_sent');
                                            }),
                                    ]),
                            ]),

                        Section::make(trans('ip.invoice_amounts'))
                            ->icon(Heroicon::OutlinedCalculator)
                            ->columnSpan(1)
                            ->columns(2)
                            ->schema([
                                TextInput::make('invoice_item_subtotal')
                                    ->label(trans('ip.subtotal'))
                                    ->prefixIcon(Heroicon::OutlinedBanknotes)
                                    ->disabled()
                                    ->dehydrated()
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, callable $get): void {
                                        (new InvoiceCalculator())->updateGrandTotal($set, $get);
                                    }),

                                TextInput::make('invoice_discount_amount')
                                    ->label(trans('ip.discount_amount'))
                                    ->prefixIcon(Heroicon::OutlinedMinusCircle)
                                    ->nullable(),

                                TextInput::make('invoice_discount_percent')
                                    ->label(trans('ip.discount_percent'))
                                    ->prefixIcon(Heroicon::OutlinedReceiptPercent)
                                    ->nullable(),

                                TextInput::make('invoice_tax_total')
                                    ->label(trans('ip.tax_total'))
                                    ->prefixIcon(Heroicon::OutlinedBuildingLibrary)
                                    ->disabled(),

                                TextInput::make('invoice_total')
                                    ->label(trans('ip.invoice_total'))
                                    ->prefixIcon(Heroicon::OutlinedCurrencyDollar)
                                    ->disabled(),
                            ]),
                    ]),

                Section::make(trans('ip.invoice_items'))
                    ->icon(Heroicon::OutlinedListBullet)
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('invoiceItems')
                            ->defaultItems(0)
                            ->relationship('invoiceItems')
                            ->label(trans('ip.invoice_items'))
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
                            ->afterStateUpdated(fn (callable $set, callable $get) => (new InvoiceCalculator())->updateGrandTotal($set, $get, 'invoiceItems', 'subtotal', 'invoice_item_subtotal')),
                    ]),

                Section::make(trans('ip.notes'))
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->schema([
                        MarkdownEditor::make('notes')
                            ->label(trans('ip.notes'))
                            ->toolbarButtons(['bold', 'italic'])
                            ->hintAction(InsertNoteTemplateAction::make('notes')),
                    ])
                    ->collapsed()
                    ->columnSpanFull(),

                Section::make(trans('ip.attachments'))
                    ->icon(Heroicon::OutlinedPaperClip)
                    ->schema([
                        FileUpload::make('attachments')
                            ->label(trans('ip.attachments'))
                            ->multiple(),
                    ])
                    ->collapsed()
                    ->columnSpanFull(),

                Section::make(trans('ip.invoice_terms'))
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->schema([
                        MarkdownEditor::make('invoice_terms')
                            ->toolbarButtons(['bold', 'italic'])
                            ->label(trans('ip.invoice_terms'))
                            ->default(function (string $operation) {
                                if ($operation !== 'create') {
                                    return;
                                }

                                $companyId = Filament::getTenant()?->id;

                                return $companyId
                                    ? Setting::getForCompany($companyId, Setting::KEY_INVOICE_DEFAULT_TERMS)
                                    : null;
                            })
                            ->hintAction(InsertNoteTemplateAction::make('invoice_terms')),
                    ])
                    ->collapsed()
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Generate an invoice number for the create form, respecting the
     * generate_invoice_number_for_draft setting (default true) for draft
     * status. Returns null when generation is skipped or no numbering
     * scheme is available.
     */
    private static function generateInvoiceNumber(Get $get): ?string
    {
        $status = $get('invoice_status') ?? InvoiceStatus::DRAFT->value;

        if (
            $status === InvoiceStatus::DRAFT->value
            && ! Setting::getBool('generate_invoice_number_for_draft')
        ) {
            return null;
        }

        $companyId = auth()->user()?->getCurrentCompanyId();
        $generator = new InvoiceNumberGenerator($companyId);

        // Prefer the explicitly selected numbering scheme; otherwise fall
        // back to any Invoice-type scheme for the company instead of the
        // generator's conventional "Default Invoice Numbering" group name,
        // which seeded/company-created schemes won't necessarily carry.
        if ($numberingId = $get('numbering_id')) {
            $generator->forNumberingId((int) $numberingId);
        } else {
            $generator->forNumbering('');
        }

        return $generator->generate();
    }
}
