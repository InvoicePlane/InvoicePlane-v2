<?php

namespace Modules\Quotes\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\DocumentGroup;
use Modules\Core\Models\User;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Models\Quote;

/**
 * @extends Factory<\Modules\Quotes\Models\Quote>
 */
class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    public function definition(): array
    {
        $company  = Company::factory()->create();
        $prospect = Relation::factory()->create(['relation_type' => RelationType::PROSPECT->value]);
        $user     = User::factory()->create();
        $group    = DocumentGroup::factory()->create();

        $subtotal        = 300;
        $itemTaxTotal    = 0;
        $taxTotal        = 60;
        $discountAmount  = 0;
        $discountPercent = 0;
        $total           = $subtotal + $taxTotal - $discountAmount;

        return [
            'company_id'             => $company->id,
            'prospect_id'            => $prospect->id,
            'document_group_id'      => $group->id,
            'user_id'                => $user->id,
            'quote_number'           => 'Q-2025-001',
            'quote_status'           => QuoteStatus::DRAFT->value,
            'quoted_at'              => now()->format('Y-m-d'),
            'quote_expires_at'       => now()->addDays(30)->format('Y-m-d'),
            'quote_discount_amount'  => $discountAmount,
            'quote_discount_percent' => $discountPercent,
            'item_tax_total'         => $itemTaxTotal,
            'quote_item_subtotal'    => $subtotal,
            'quote_tax_total'        => $taxTotal,
            'quote_total'            => $total,
            'quote_password'         => null,
            'url_key'                => Str::random(32),
            'template'               => null,
            'summary'                => null,
            'terms'                  => null,
            'footer'                 => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['quote_status' => QuoteStatus::DRAFT->value]);
    }

    public function sent(): static
    {
        return $this->state(fn () => ['quote_status' => QuoteStatus::SENT->value]);
    }

    public function viewed(): static
    {
        return $this->state(fn () => ['quote_status' => QuoteStatus::VIEWED->value]);
    }

    public function approved(): static
    {
        return $this->state(fn () => ['quote_status' => QuoteStatus::APPROVED->value]);
    }

    public function canceled(): static
    {
        return $this->state(fn () => ['quote_status' => QuoteStatus::CANCELED->value]);
    }
}
