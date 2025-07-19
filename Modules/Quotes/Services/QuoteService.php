<?php

namespace Modules\Quotes\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Core\Services\BaseService;
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

            $quote = parent::create([
                'prospect_id'            => $data['prospect_id'],
                'document_group_id'      => $data['document_group_id'] ?? null,
                'user_id'                => auth()->id(),
                'quote_number'           => $data['quote_number'],
                'quote_status'           => $data['quote_status'],
                'quoted_at'              => Carbon::parse($data['quoted_at']),
                'quote_expires_at'       => Carbon::parse($data['quote_expires_at']),
                'quote_discount_amount'  => $data['quote_discount_amount'] ?? 0,
                'quote_discount_percent' => $data['quote_discount_percent'] ?? 0,
                'item_tax_total'         => $itemTaxTotal,
                'quote_item_subtotal'    => $data['quote_item_subtotal'] ?? 0,
                'quote_tax_total'        => $quoteTaxTotal,
                'quote_total'            => $quoteTotal,
                'quote_password'         => $data['quote_password'] ?? null,
                'url_key'                => $data['url_key'] ?? Str::random(32),
                'template'               => $data['template'] ?? null,
                'summary'                => $data['summary'] ?? null,
                'terms'                  => $data['terms'] ?? null,
                'footer'                 => $data['footer'] ?? null,
            ]);

            foreach ($data['quoteItems'] ?? [] as $item) {
                $quote->quoteItems()->create([
                    'item_id'       => $item['item_id'] ?? null,
                    'unit_id'       => $item['unit_id'] ?? null,
                    'item_name'     => $item['item_name'] ?? null,
                    'quantity'      => $item['quantity'],
                    'price'         => $item['price'],
                    'discount'      => $item['discount'] ?? 0,
                    'subtotal'      => $item['subtotal'],
                    'tax_1'         => $item['tax_1'] ?? 0,
                    'tax_2'         => $item['tax_2'] ?? 0,
                    'tax_total'     => $item['tax_total'] ?? 0,
                    'total'         => $item['total'] ?? 0,
                    'description'   => $item['description'] ?? null,
                    'tax_rate_id'   => $item['tax_rate_id'] ?? null,
                    'tax_rate_2_id' => $item['tax_rate_2_id'] ?? null,
                    'display_order' => $item['display_order'] ?? null,
                ]);
            }

            DB::commit();

            return $quote;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateQuote(Quote $model, array $data): Quote
    {
        $model->update([
            'customer_id' => $data['customer_id'],
            'quote_date'  => $data['quote_date'],
            'expires_at'  => $data['expires_at'],
            'status'      => $data['status'],
            'summary'     => $data['summary'] ?? null,
        ]);

        return $model;
    }
}
