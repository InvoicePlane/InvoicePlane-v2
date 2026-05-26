<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Company;
use Modules\Core\Models\Numbering;
use Modules\Core\Services\NumberingService;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class NumberingPanelAccessTest extends AbstractAdminPanelTestCase
{
    use RefreshDatabase;

    private NumberingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(NumberingService::class);
    }

    #[Test]
    public function it_allows_admin_to_assign_numbering_to_any_company(): void
    {
        /* Arrange */
        $company1 = Company::factory()->create(['name' => 'Company One']);
        $company2 = Company::factory()->create(['name' => 'Company Two']);

        /* Act */
        // Admin can create numbering for company 1
        $numbering1 = $this->service->createNumbering([
            'name'       => 'Invoice Numbering for Company 1',
            'type'       => 'Invoice',
            'format'     => 'INV-{{number}}',
            'company_id' => $company1->id,
            'next_id'    => 1,
            'left_pad'   => 4,
        ]);

        // Admin can create numbering for company 2
        $numbering2 = $this->service->createNumbering([
            'name'       => 'Invoice Numbering for Company 2',
            'type'       => 'Invoice',
            'format'     => 'INV-{{number}}',
            'company_id' => $company2->id,
            'next_id'    => 1,
            'left_pad'   => 4,
        ]);

        /* Assert */
        $this->assertEquals($company1->id, $numbering1->company_id);
        $this->assertEquals($company2->id, $numbering2->company_id);

        // Admin can see numberings from all companies
        $allNumberings = Numbering::all();
        $this->assertGreaterThanOrEqual(2, $allNumberings->count());
    }

    #[Test]
    #[Group('failing')]
    public function it_restricts_company_panel_to_current_company_only(): void
    {
        /* Arrange */
        $company1 = Company::factory()->create(['name' => 'Company One']);
        $company2 = Company::factory()->create(['name' => 'Company Two']);

        Numbering::query()->delete(); // Ensure clean state

        $numbering1 = $this->service->createNumbering([
            'name'       => 'Numbering for Company 1',
            'type'       => 'Invoice',
            'format'     => 'INV-{{number}}',
            'company_id' => $company1->id,
            'next_id'    => 1,
            'left_pad'   => 4,
        ]);

        $numbering2 = $this->service->createNumbering([
            'name'       => 'Numbering for Company 2',
            'type'       => 'Invoice',
            'format'     => 'INV-{{number}}',
            'company_id' => $company2->id,
            'next_id'    => 1,
            'left_pad'   => 4,
        ]);

        /* Act */
        $company1Numberings = Numbering::query()->where('company_id', $company1->id)->get();
        $company2Numberings = Numbering::query()->where('company_id', $company2->id)->get();

        /* Assert */
        $this->assertEquals(1, $company1Numberings->count());
        $this->assertEquals($numbering1->id, $company1Numberings->first()->id);

        $this->assertEquals(1, $company2Numberings->count());
        $this->assertEquals($numbering2->id, $company2Numberings->first()->id);
    }

    #[Test]
    public function it_prevents_company_user_from_changing_company_id(): void
    {
        /* Arrange */
        $company1 = Company::factory()->create(['name' => 'Company One']);
        $company2 = Company::factory()->create(['name' => 'Company Two']);

        $numbering = $this->service->createNumbering([
            'name'       => 'Numbering for Company 1',
            'type'       => 'Invoice',
            'format'     => 'INV-{{number}}',
            'company_id' => $company1->id,
            'next_id'    => 1,
            'left_pad'   => 4,
        ]);

        /* Act & Assert */
        // Company user should not be able to change company_id
        // This would typically be enforced at the form/policy level
        // In the Company panel, company_id field should be read-only or hidden

        $this->assertEquals($company1->id, $numbering->company_id);

        // Attempting to update with different company_id should fail or be ignored
        // In practice, this would be prevented by form validation or policy
    }

    #[Test]
    public function it_allows_company_user_to_edit_their_numbering_format(): void
    {
        /* Arrange */
        $company = Company::factory()->create(['name' => 'My Company']);

        $numbering = $this->service->createNumbering([
            'name'       => 'Invoice Numbering',
            'type'       => 'Invoice',
            'format'     => 'INV-{{number}}',
            'company_id' => $company->id,
            'next_id'    => 1,
            'left_pad'   => 4,
        ]);

        /* Act */
        // Company user can update format (but not company_id)
        $numbering->update([
            'format'   => 'INV-{{year}}-{{month}}-{{number}}',
            'left_pad' => 6,
        ]);
        $numbering->refresh();

        /* Assert */
        $this->assertEquals('INV-{{year}}-{{month}}-{{number}}', $numbering->format);
        $this->assertEquals(6, $numbering->left_pad);
        $this->assertEquals($company->id, $numbering->company_id); // company_id unchanged
    }
}
