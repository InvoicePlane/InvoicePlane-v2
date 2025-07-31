<?php

namespace Modules\Quotes\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
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
        $company = Company::query()->inRandomOrder()->first() ?? Company::factory()->create();

        // Create or get a prospect that belongs to this company
        $prospect = Relation::query()
            ->where('company_id', $company->id)
            ->where('relation_type', RelationType::PROSPECT->value)
            ->inRandomOrder()
            ->first() ?? Relation::factory()
            ->for($company)
            ->prospect()
            ->create();

        // Create or get a user that belongs to this company
        /*
        $user = User::query()
            ->whereHas('companies', fn ($q) => $q->where('companies.id', $company->id))
            ->inRandomOrder()
            ->first() ?? User::factory()
            ->hasAttached($company)
            ->create();
            */

        // Create or get a document group that belongs to this company
        $group = DocumentGroup::query()
            ->where('company_id', $company->id)
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
            'company_id'             => $company->id,
            'prospect_id'            => $prospect->id,
            'document_group_id'      => $group->id,
            'user_id'                => null,
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
