<?php

namespace Modules\Quotes\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\DocumentGroup;
use Modules\Core\Models\TaxRate;
use Modules\Core\Models\User;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Models\Quote;

class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    public function definition(): array
    {
        $company = Company::query()
            ->inRandomOrder()
            ->first()
    ?: Company::factory()->create();
        $prospect = Relation::query()->where('relation_type', RelationType::PROSPECT->value)
            ->inRandomOrder()
            ->first() ?? Relation::factory()->create(['relation_type' => RelationType::PROSPECT->value]);
        $user = User::query()->inRandomOrder()->first() ?? User::factory()->create();

        $documentGroup = DocumentGroup::query()->inRandomOrder()->first() ?? DocumentGroup::factory()->create();

        $taxRate        = TaxRate::query()->inRandomOrder()->first() ?? TaxRate::factory()->create();
        $taxRatePercent = $taxRate->rate / 100;

        $subtotal        = $this->faker->randomFloat(4, 100, 2000);
        $itemTaxTotal    = $subtotal * $taxRatePercent;
        $taxTotal        = $subtotal * $taxRatePercent;
        $discountAmount  = $this->faker->randomFloat(4, 0, 100);
        $discountPercent = $this->faker->randomFloat(4, 0, 20);
        $total           = ($subtotal + $itemTaxTotal + $taxTotal) - $discountAmount;

        return [
            'company_id'             => $company->id,
            'prospect_id'            => $prospect->id,
            'document_group_id'      => $documentGroup->id,
            'user_id'                => $user->id,
            'quote_number'           => $this->faker->unique()->numerify('QUO-#####'),
            'quote_status'           => $this->faker->randomElement(QuoteStatus::cases())->value,
            'quoted_at'              => $this->faker->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            'quote_expires_at'       => $this->faker->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            'quote_discount_amount'  => $discountAmount,
            'quote_discount_percent' => $discountPercent,
            'quote_item_subtotal'    => $subtotal,
            'quote_tax_total'        => $taxTotal,
            'quote_total'            => $total,
            'quote_password'         => bcrypt('password'),
            'url_key'                => $this->faker->regexify('[A-Za-z0-9]{30}'),
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
