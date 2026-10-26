<?php

namespace Modules\Core\Tests\Feature\Seeders;

use Modules\Core\Database\Seeders\TaxRatesSeeder;
use Modules\Core\Enums\TaxRateType;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\Test;

class TaxRatesSeederTest extends AbstractAdminPanelTestCase
{
    #[Test]
    public function it_seeds_country_specific_vat_rates_for_a_company(): void
    {
        /* Act */
        (new TaxRatesSeeder())->buildOne($this->company->id);

        /* Assert */
        $this->assertDatabaseHas('tax_rates', [
            'company_id'    => $this->company->id,
            'code'          => 'DE-VAT-STD-19-EXCL',
            'rate'          => 19.00,
            'tax_rate_type' => TaxRateType::EXCLUSIVE->value,
        ]);
        $this->assertDatabaseHas('tax_rates', [
            'company_id'    => $this->company->id,
            'code'          => 'DE-VAT-STD-19-INCL',
            'rate'          => 19.00,
            'tax_rate_type' => TaxRateType::INCLUSIVE->value,
        ]);
        $this->assertDatabaseHas('tax_rates', ['company_id' => $this->company->id, 'code' => 'NL-VAT-STD-21-EXCL']);
        $this->assertDatabaseHas('tax_rates', ['company_id' => $this->company->id, 'code' => 'BE-VAT-STD-21-EXCL']);
        $this->assertDatabaseHas('tax_rates', ['company_id' => $this->company->id, 'code' => 'FR-VAT-STD-20-EXCL']);
    }

    #[Test]
    public function it_only_seeds_tax_rates_for_the_given_company(): void
    {
        /* Arrange */
        $otherCompany = \Modules\Core\Models\Company::factory()->create();

        /* Act */
        (new TaxRatesSeeder())->buildOne($this->company->id);

        /* Assert */
        $this->assertDatabaseMissing('tax_rates', [
            'company_id' => $otherCompany->id,
            'code'       => 'DE-VAT-STD-19-EXCL',
        ]);
    }

    #[Test]
    public function it_is_idempotent_when_run_twice(): void
    {
        /* Act */
        (new TaxRatesSeeder())->buildOne($this->company->id);
        $firstCount = \Modules\Core\Models\TaxRate::query()->where('company_id', $this->company->id)->count();

        (new TaxRatesSeeder())->buildOne($this->company->id);
        $secondCount = \Modules\Core\Models\TaxRate::query()->where('company_id', $this->company->id)->count();

        /* Assert */
        $this->assertSame($firstCount, $secondCount);
    }
}
