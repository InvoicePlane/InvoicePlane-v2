<?php

namespace Modules\Core\Services\Import;

use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteItem;

class QuotesImportService extends AbstractImportService
{
    private int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function getTables(): array
    {
        return ['ip_quotes', 'ip_quote_items'];
    }

    public function import(int $companyId, array &$idMappings): array
    {
        $this->companyId = $companyId;
        $this->idMappings = &$idMappings;
        $this->initStats(['quotes', 'quote_items']);

        $this->importQuotes();

        return $this->stats;
    }

    private function importQuotes(): void
    {
        $quotes = $this->getImportData('ip_quotes');

        foreach ($quotes as $v1Quote) {
            $prospectId = $this->idMappings['clients'][$v1Quote->client_id] ?? null;
            $numberingId = $this->idMappings['invoice_groups'][$v1Quote->quote_group_id] ?? null;

            if (! $prospectId) {
                continue;
            }

            $quote = Quote::create([
                'company_id'             => $this->companyId,
                'prospect_id'            => $prospectId,
                'numbering_id'           => $numberingId,
                'user_id'                => $this->userId,
                'quote_number'           => $v1Quote->quote_number,
                'quote_status'           => $this->mapQuoteStatus($v1Quote->quote_status_id ?? 1)->value,
                'quoted_at'              => $v1Quote->quote_date_created ?? now(),
                'quote_expires_at'       => $v1Quote->quote_date_expires ?? now()->addDays(30),
                'quote_discount_percent' => $v1Quote->quote_discount_percent ?? 0,
                'quote_discount_amount'  => $v1Quote->quote_discount_amount ?? 0,
                'item_tax_total'         => $v1Quote->quote_item_tax_total ?? 0,
                'quote_item_subtotal'    => $v1Quote->quote_item_subtotal ?? 0,
                'quote_tax_total'        => $v1Quote->quote_tax_total ?? 0,
                'quote_total'            => $v1Quote->quote_total ?? 0,
                'url_key'                => $v1Quote->quote_url_key ?? null,
                'terms'                  => $v1Quote->quote_terms ?? null,
            ]);

            $this->idMappings['quotes'][$v1Quote->quote_id] = $quote->id;
            $this->stats['quotes']++;

            $this->importQuoteItems($v1Quote->quote_id, $quote->id);
        }
    }

    private function importQuoteItems(int $v1QuoteId, int $v2QuoteId): void
    {
        $items = $this->getImportData('ip_quote_items');

        foreach ($items as $v1Item) {
            if ($v1Item->quote_id != $v1QuoteId) {
                continue;
            }

            $productId = $this->idMappings['products'][$v1Item->item_product_id] ?? null;
            $taxRateId = $this->idMappings['tax_rates'][$v1Item->item_tax_rate_id] ?? null;

            QuoteItem::create([
                'company_id'    => $this->companyId,
                'quote_id'      => $v2QuoteId,
                'product_id'    => $productId,
                'item_name'     => $v1Item->item_name ?? 'Item',
                'quantity'      => $v1Item->item_quantity ?? 1,
                'price'         => $v1Item->item_price ?? 0,
                'discount'      => $v1Item->item_discount_amount ?? 0,
                'tax_rate_id'   => $taxRateId,
                'subtotal'      => $v1Item->item_subtotal ?? 0,
                'tax_total'     => $v1Item->item_tax_total ?? 0,
                'total'         => $v1Item->item_total ?? 0,
                'description'   => $v1Item->item_description ?? null,
                'display_order' => $v1Item->item_order ?? 0,
            ]);

            $this->stats['quote_items']++;
        }
    }

    private function mapQuoteStatus(int $statusId): QuoteStatus
    {
        return match ($statusId) {
            1       => QuoteStatus::DRAFT,
            2       => QuoteStatus::SENT,
            3       => QuoteStatus::VIEWED,
            4       => QuoteStatus::APPROVED,
            5       => QuoteStatus::REJECTED,
            default => QuoteStatus::DRAFT,
        };
    }
}
