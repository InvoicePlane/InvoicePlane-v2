<?php

namespace Modules\Core\Tests\Unit\Observers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Company;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class CompanyObserverTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('unit')]
    public function it_bootstraps_default_data_when_company_is_created(): void
    {
        /* Arrange */
        /* Act */
        $company = Company::create([
            'search_code' => 'IVPLV2',
            'name'        => 'InvoicePlane Corporation',
            'slug'        => 'invoiceplane-corporation',
        ]);

        /* Assert */
        $this->assertDatabaseHas('email_templates', [
            'company_id' => $company->id,
        ]);

        $this->assertDatabaseHas('tax_rates', [
            'company_id' => $company->id,
        ]);
        $this->assertDatabaseHas('numbering', [
            'company_id' => $company->id,
        ]);

        $this->assertDatabaseHas('product_categories', [
            'company_id' => $company->id,
        ]);
        $this->assertDatabaseHas('product_units', [
            'company_id' => $company->id,
        ]);
        $this->assertDatabaseHas('expense_categories', [
            'company_id' => $company->id,
        ]);
    }

    #[Test]
    #[Group('unit')]
    public function it_creates_related_entities(): void
    {
        $this->markTestIncomplete('Test incomplete - requires investigation for PHPStan coverage and implementation details');
    }
}
