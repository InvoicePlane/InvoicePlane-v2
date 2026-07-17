<?php

namespace Modules\Quotes\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Modules\Core\Services\BaseService;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Models\Quote;
use Throwable;

class QuoteService extends BaseService
{
    public function model(): string
    {
        return Quote::class;
    }

    public function createQuote(array $data): Model
    {
        DB::beginTransaction();

        try {
            $itemTaxTotal  = $this->calculateItemTaxTotal($data);
            $quoteTaxTotal = $this->calculateQuoteTaxTotal($data);
            $quoteTotal    = $this->calculateQuoteTotal($data, $itemTaxTotal, $quoteTaxTotal);

            $quote = Quote::query()->create([
                'company_id'             => $this->getCompanyId(),
                'prospect_id'            => $data['prospect_id'],
                'numbering_id'           => $data['numbering_id'] ?? null,
                'user_id'                => $data['user_id'] ?? auth()->id(),
                'quote_number'           => $data['quote_number'],
                'client_reference'       => $data['client_reference'] ?? null,
                'work_order'             => $data['work_order'] ?? null,
                'quote_status'           => $data['quote_status'],
                'quoted_at'              => Carbon::parse($data['quoted_at']),
                'quote_expires_at'       => Carbon::parse($data['quote_expires_at']),
                'quote_discount_amount'  => $data['quote_discount_amount'] ?? 0,
                'quote_discount_percent' => $data['quote_discount_percent'] ?? 0,
                'item_tax_total'         => $itemTaxTotal,
                'quote_item_subtotal'    => $data['quote_item_subtotal'] ?? 0,
                'quote_tax_total'        => $quoteTaxTotal,
                'quote_total'            => $data['quote_total'] ?? 0,
                'quote_password'         => $data['quote_password'] ?? null,
                'url_key'                => $data['url_key'] ?? Str::random(32),
                'template'               => $data['template'] ?? null,
                'summary'                => $data['summary'] ?? null,
                'terms'                  => $data['terms'] ?? null,
                'footer'                 => $data['footer'] ?? null,
            ]);

            foreach ($data['quoteItems'] as $item) {
                $calculateMySubtotal = $item['quantity'] * $item['price'];

                $quote->quoteItems()->create([
                    'company_id'      => $this->getCompanyId(),
                    'product_id'      => $item['product_id'] ?? 1,
                    'product_unit_id' => $item['product_unit_id'] ?? 1,
                    'added_at'        => Carbon::now()->toDateString(),
                    'item_name'       => $item['item_name'] ?? null,
                    'quantity'        => $item['quantity'],
                    'price'           => $item['price'],
                    'discount'        => $item['discount'] ?? 0,
                    'tax_1'           => $item['tax_1'] ?? 0,
                    'tax_2'           => $item['tax_2'] ?? 0,
                    'tax_total'       => $item['tax_total'] ?? 0,
                    'total'           => $item['total'] ?? 0,
                    'tax_rate_id'     => $item['tax_rate_id'] ?? null,
                    'tax_rate_2_id'   => $item['tax_rate_2_id'] ?? null,
                    'display_order'   => 1,
                    'description'     => $item['description'] ?? null,
                ]);
            }

            DB::commit();

            return $quote;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateQuote(Quote $quote, array $data): Quote
    {
        $itemTaxTotal  = $this->calculateItemTaxTotal($data);
        $quoteTaxTotal = $this->calculateQuoteTaxTotal($data);
        $quoteTotal    = $this->calculateQuoteTotal($data, $itemTaxTotal, $quoteTaxTotal);

        DB::beginTransaction();

        try {
            $quote->update([
                'prospect_id'            => $data['prospect_id'],
                'client_reference'       => $data['client_reference'] ?? null,
                'work_order'             => $data['work_order'] ?? null,
                'quoted_at'              => $data['quoted_at'],
                'quote_expires_at'       => $data['quote_expires_at'],
                'quote_status'           => $data['quote_status'],
                'quote_discount_amount'  => $data['quote_discount_amount'] ?? 0,
                'quote_discount_percent' => $data['quote_discount_percent'] ?? 0,
                'item_tax_total'         => $itemTaxTotal,
                'quote_item_subtotal'    => $data['quote_item_subtotal'] ?? 0,
                'quote_tax_total'        => $quoteTaxTotal,
                'quote_total'            => $quoteTotal,
                'summary'                => $data['summary'] ?? null,
            ]);

            DB::commit();

            return $quote->refresh();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function duplicateQuote(Quote $quote): Quote
    {
        DB::beginTransaction();

        try {
            $copy = Quote::query()->create([
                'company_id'             => $quote->company_id,
                'prospect_id'            => $quote->prospect_id,
                'numbering_id'           => $quote->numbering_id,
                'user_id'                => auth()->id(),
                'quote_number'           => null,
                'quote_status'           => QuoteStatus::DRAFT,
                'quoted_at'              => Carbon::today(),
                'quote_expires_at'       => Carbon::today()->addDays(30),
                'quote_discount_amount'  => $quote->quote_discount_amount,
                'quote_discount_percent' => $quote->quote_discount_percent,
                'item_tax_total'         => $quote->item_tax_total,
                'quote_item_subtotal'    => $quote->quote_item_subtotal,
                'quote_tax_total'        => $quote->quote_tax_total,
                'quote_total'            => $quote->quote_total,
                'quote_password'         => null,
                'url_key'                => Str::random(32),
                'template'               => $quote->template,
                'summary'                => $quote->summary,
                'terms'                  => $quote->terms,
                'footer'                 => $quote->footer,
            ]);

            foreach ($quote->quoteItems as $item) {
                $copy->quoteItems()->create([
                    'company_id'      => $item->company_id,
                    'product_id'      => $item->product_id,
                    'product_unit_id' => $item->product_unit_id,
                    'added_at'        => Carbon::today()->toDateString(),
                    'item_name'       => $item->item_name,
                    'quantity'        => $item->quantity,
                    'price'           => $item->price,
                    'discount'        => $item->discount,
                    'subtotal'        => $item->subtotal,
                    'tax_1'           => $item->tax_1,
                    'tax_2'           => $item->tax_2,
                    'tax_total'       => $item->tax_total,
                    'total'           => $item->total,
                    'tax_rate_id'     => $item->tax_rate_id,
                    'tax_rate_2_id'   => $item->tax_rate_2_id,
                    'display_order'   => $item->display_order,
                    'description'     => $item->description,
                ]);
            }

            DB::commit();

            return $copy;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteQuote(Quote $quote): Quote
    {
        DB::beginTransaction();
        try {
            $quote->quoteItems()->delete();
            $quote->delete();
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $quote;
    }

    /**
     * Convert an accepted quote into a draft invoice.
     *
     * Copies the client, all quote items, and the summary fields onto a new
     * unnumbered draft invoice, then marks the quote as Converted. Numbering
     * is assigned when the invoice leaves draft.
     *
     * @throws InvalidArgumentException when the quote was already converted
     */
    public function convertQuoteToInvoice(Quote $quote): Invoice
    {
        if ($quote->quote_status === QuoteStatus::CONVERTED) {
            throw new InvalidArgumentException(trans('ip.quote_already_converted'));
        }

        return DB::transaction(function () use ($quote) {
            $invoice = Invoice::query()->create([
                'company_id'               => $quote->company_id,
                'customer_id'              => $quote->prospect_id,
                'numbering_id'             => null,
                'user_id'                  => auth()->id() ?? $quote->user_id,
                'invoice_number'           => null,
                'invoice_status'           => InvoiceStatus::DRAFT->value,
                'invoice_sign'             => '1',
                'invoiced_at'              => Carbon::today(),
                'invoice_due_at'           => Carbon::today()->addDays(30),
                'invoice_discount_amount'  => $quote->quote_discount_amount ?? 0,
                'invoice_discount_percent' => $quote->quote_discount_percent ?? 0,
                'item_tax_total'           => $quote->item_tax_total ?? 0,
                'invoice_item_subtotal'    => $quote->quote_item_subtotal ?? 0,
                'invoice_tax_total'        => $quote->quote_tax_total ?? 0,
                'invoice_total'            => $quote->quote_total ?? 0,
                'url_key'                  => Str::random(32),
                'summary'                  => $quote->summary,
                'terms'                    => $quote->terms,
                'footer'                   => $quote->footer,
            ]);

            foreach ($quote->quoteItems as $item) {
                $invoice->invoiceItems()->create([
                    'company_id'      => $item->company_id,
                    'product_id'      => $item->product_id,
                    'product_unit_id' => $item->product_unit_id,
                    'task_id'         => $item->task_id,
                    'added_at'        => Carbon::today()->toDateString(),
                    'item_name'       => $item->item_name,
                    'quantity'        => $item->quantity,
                    'price'           => $item->price,
                    'discount'        => $item->discount,
                    'subtotal'        => $item->subtotal,
                    'tax_1'           => $item->tax_1,
                    'tax_2'           => $item->tax_2,
                    'tax_total'       => $item->tax_total,
                    'total'           => $item->total,
                    'tax_rate_id'     => $item->tax_rate_id,
                    'tax_rate_2_id'   => $item->tax_rate_2_id,
                    'description'     => $item->description,
                ]);
            }

            $quote->update(['quote_status' => QuoteStatus::CONVERTED->value]);

            return $invoice;
        });
    }

    private function calculateItemTaxTotal(array $data): float
    {
        return collect($data['quoteItems'] ?? [])->sum(fn ($item) => $item['tax_total'] ?? 0);
    }

    private function calculateQuoteTaxTotal(array $data): float
    {
        return collect($data['quoteItems'] ?? [])->sum(fn ($item) => ($item['tax_1'] ?? 0) + ($item['tax_2'] ?? 0));
    }

    private function calculateQuoteTotal(array $data, float $itemTaxTotal, float $quoteTaxTotal): float
    {
        $subtotal       = $data['quote_item_subtotal'] ?? 0;
        $discountAmount = $data['quote_discount_amount'] ?? 0;

        return $subtotal + $itemTaxTotal + $quoteTaxTotal - $discountAmount;
    }
}
