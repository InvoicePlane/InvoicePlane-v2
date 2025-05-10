<?php

namespace Modules\Quotes\Services;

use Modules\Core\Services\BaseService;
use Modules\Quotes\Models\Quote;

class QuoteService extends BaseService
{
    public function model(): string
    {
        return Quote::class;
    }

    public function create(array $data): Quote
    {
        DB::beginTransaction();

        try {
            $quote = Quote::create([
                'company_id'             => $data['company_id'],
                'prospect_id'            => $data['prospect_id'],
                'document_group_id'      => $data['document_group_id'] ?? null,
                'user_id'                => auth()->id(),
                'quote_number'           => $data['quote_number'],
                'quote_status'           => $data['quote_status'],
                'quoted_at'              => Carbon::parse($data['quoted_at']),
                'quote_expires_at'       => Carbon::parse($data['quote_expires_at']),
                'quote_discount_amount'  => $data['quote_discount_amount'] ?? 0,
                'quote_discount_percent' => $data['quote_discount_percent'] ?? 0,
                'item_tax_total'         => $data['item_tax_total'] ?? 0,
                'quote_item_subtotal'    => $data['quote_item_subtotal'],
                'quote_tax_total'        => $data['quote_tax_total'],
                'quote_total'            => $data['quote_total'],
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
}
