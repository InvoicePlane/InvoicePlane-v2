<?php

namespace Modules\Core\Tests\Unit;

use Modules\Core\Enums\TaxRateType;
use Modules\Core\Models\TaxRate;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Regression guard for a real bug in BelongsToCompany::bootBelongsToCompany():
 * the auto-assign-on-create logic was gated by `isset($model->company_id) &&
 * empty($model->company_id)`, but Eloquent's __isset() returns false for an
 * attribute that was never touched at all — not just one explicitly set to
 * null. A ->create([...]) call whose array has no 'company_id' key at all
 * (relying entirely on the trait to inject it) silently skipped
 * backfilling it, leaving the NOT NULL company_id column NULL and blowing
 * up as an unhandled SQL 500.
 *
 * Uses TaxRate specifically because it has no per-model Observer that
 * separately guards company_id (unlike Relation/Contact/Quote/Invoice,
 * whose own Observers happen to duplicate this assignment and would mask
 * a regression in the shared trait) — this isolates the trait's own
 * behavior.
 */
class BelongsToCompanyAutoAssignsCompanyIdTest extends AbstractAdminPanelTestCase
{
    #[Test]
    public function it_assigns_the_current_company_id_even_when_the_attribute_was_never_set(): void
    {
        /* Arrange */
        $this->actingAs($this->superAdmin());
        session(['current_company_id' => $this->company->id]);

        /* Act */
        $taxRate = TaxRate::query()->create([
            'tax_rate_type' => TaxRateType::EXCLUSIVE->value,
            'is_active'     => true,
            'code'          => 'NOCOID',
            'name'          => 'No Company Id Key Rate',
            'rate'          => 5.0,
        ]);

        /* Assert */
        $this->assertSame($this->company->id, $taxRate->company_id);
        $this->assertDatabaseHas('tax_rates', [
            'id'         => $taxRate->id,
            'company_id' => $this->company->id,
        ]);
    }
}
