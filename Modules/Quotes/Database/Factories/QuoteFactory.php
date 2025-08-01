<?php

namespace Modules\Quotes\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Clients\Enums\RelationType;
use Modules\Core\Database\Factories\AbstractFactory;
use Modules\Core\Models\Company;
use Modules\Core\Models\DocumentGroup;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Models\Quote;

/**
 * @extends Factory<\Modules\Quotes\Models\Quote>
 */
class QuoteFactory extends AbstractFactory
{
    protected $model = Quote::class;

    public function definition(): array
    {
        $companyId = $attributes['company_id'] ?? (Company::query()->inRandomOrder()->first()?->id ?? null);
        $company   = Company::query()->find($companyId);

        $prospect = $this->findOrCreateRelationOfType($companyId, RelationType::CUSTOMER);

        $user = $this->findOrCreateRandomUser($companyId);

        // Create or get a document group that belongs to this company
        $group = DocumentGroup::query()
            ->where('company_id', $companyId)
            ->inRandomOrder()
            ->first() ?? DocumentGroup::factory()
            ->for($company)
            ->create();

        $subtotal        = 300;
        $itemTaxTotal    = 0;
        $taxTotal        = 60;
        $discountAmount  = 0;
        $discountPercent = 0;
        $total           = $subtotal + $taxTotal - $discountAmount;

        $quotedAt  = fake()->dateTimeBetween('-1 year', 'now');
        $expiresAt = (clone $quotedAt)->modify('+' . fake()->numberBetween(7, 180) . ' days');

        return [
            'prospect_id'            => $prospect->id,
            'document_group_id'      => $group->id,
            'user_id'                => $user->id,
            'quote_number'           => 'Q-' . now()->year . '-' . fake()->unique()->numberBetween(1, 9999),
            'quote_status'           => fake()->randomElement(QuoteStatus::cases())->value,
            'quoted_at'              => $quotedAt,
            'quote_expires_at'       => $expiresAt,
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
        return $this->state(fn () => ['quote_status' => QuoteStatus::REJECTED->value]);
    }
}
