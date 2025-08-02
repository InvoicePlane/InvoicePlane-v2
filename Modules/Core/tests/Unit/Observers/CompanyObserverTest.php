<?php

namespace Modules\Core\tests\Unit\Observers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Company;
use Modules\Core\Tests\AbstractTestCase;

class CompanyObserverTest extends AbstractTestCase
{
    use RefreshDatabase;

    public function it_bootstraps_default_data_when_company_is_created(): void
    {
        $company = Company::create([
            'search_code' => 'IVPLV2',
            'name'        => 'InvoicePlane Corporation',
            'slug'        => 'invoiceplane-corporation',
        ]);

        $this->assertDatabaseHas('email_templates', [
            'company_id' => $company->id,
        ]);

        $this->assertDatabaseHas('tax_rates', [
            'company_id' => $company->id,
        ]);
        $this->assertDatabaseHas('document_groups', [
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
}
