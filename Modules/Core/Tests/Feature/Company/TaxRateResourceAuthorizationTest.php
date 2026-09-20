<?php

namespace Modules\Core\Tests\Feature\Company;

use Modules\Core\Enums\Permission;
use Modules\Core\Filament\Company\Resources\TaxRates\TaxRateResource;
use Modules\Core\Models\TaxRate;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Core\Tests\Concerns\InteractsWithPermissions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * Authorization tests for the company panel's Tax Rates resource (#238).
 *
 * These tests exercise the resource's can*() gates directly, matching
 * RelationResourceAuthorizationTest (#503).
 */
#[CoversClass(TaxRateResource::class)]
class TaxRateResourceAuthorizationTest extends AbstractCompanyPanelTestCase
{
    use InteractsWithPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->user);
    }

    #[Test]
    public function it_allows_viewing_the_list_with_view_tax_rates_permission(): void
    {
        $this->grantPermission(Permission::VIEW_TAX_RATES);

        $this->assertTrue(TaxRateResource::canViewAny());
    }

    #[Test]
    public function it_blocks_viewing_the_list_without_view_tax_rates_permission(): void
    {
        $this->withoutTaxRatesPermissions();

        $this->assertFalse(TaxRateResource::canViewAny());
    }

    #[Test]
    public function it_allows_creating_with_create_tax_rates_permission(): void
    {
        $this->grantPermission(Permission::CREATE_TAX_RATES);

        $this->assertTrue(TaxRateResource::canCreate());
    }

    #[Test]
    public function it_blocks_creating_without_create_tax_rates_permission(): void
    {
        $this->withoutTaxRatesPermissions();

        $this->assertFalse(TaxRateResource::canCreate());
    }

    #[Test]
    public function it_allows_editing_with_edit_tax_rates_permission(): void
    {
        $taxRate = TaxRate::factory()->for($this->company)->create();
        $this->grantPermission(Permission::EDIT_TAX_RATES);

        $this->assertTrue(TaxRateResource::canEdit($taxRate));
    }

    #[Test]
    public function it_blocks_editing_without_edit_tax_rates_permission(): void
    {
        $taxRate = TaxRate::factory()->for($this->company)->create();
        $this->withoutTaxRatesPermissions();

        $this->assertFalse(TaxRateResource::canEdit($taxRate));
    }

    #[Test]
    public function it_allows_deleting_with_delete_tax_rates_permission(): void
    {
        $taxRate = TaxRate::factory()->for($this->company)->create();
        $this->grantPermission(Permission::DELETE_TAX_RATES);

        $this->assertTrue(TaxRateResource::canDelete($taxRate));
    }

    #[Test]
    public function it_blocks_deleting_without_delete_tax_rates_permission(): void
    {
        $taxRate = TaxRate::factory()->for($this->company)->create();
        $this->withoutTaxRatesPermissions();

        $this->assertFalse(TaxRateResource::canDelete($taxRate));
    }

    /**
     * AbstractCompanyPanelTestCase assigns the client_admin role by default,
     * which now includes Tax Rates permissions -- strip it to test the
     * genuinely-unauthorized case, mirroring the plain `client` role.
     */
    private function withoutTaxRatesPermissions(): void
    {
        $this->user->syncRoles([]);
        $this->user->syncPermissions([]);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
