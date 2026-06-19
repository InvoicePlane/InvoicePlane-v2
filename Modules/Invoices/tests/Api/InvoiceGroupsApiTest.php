<?php

namespace Modules\Invoices\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Sanctum\Sanctum;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;
use Modules\Core\tests\ApiTestTrait;
use Modules\Invoices\Models\InvoiceGroup;

class InvoiceGroupsApiTest extends AbstractTestCase
{
    use ApiTestTrait;
    use RefreshDatabase;
    use WithoutMiddleware;

    // endregion

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    // region CRUD Tests

    /** @test */
    public function it_returns_invoice_groups_index(): void
    {
        $this->markTestIncomplete('This test has not been implemented yet.');

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $invoiceGroups = InvoiceGroup::factory()->count(5)->create();

        $response = $this->getJson(route('api.invoice-groups.index'));
        $response->assertSuccessful();
    }

    /** @test */
    public function it_creates_an_invoice_group(): void
    {
        $this->markTestIncomplete('This test has not been implemented yet.');

        $invoiceGroup = InvoiceGroup::factory()->make()->toArray();

        $response = $this->postJson(route('api.invoice-groups.store'), $invoiceGroup);
        $response->assertStatus(200);
    }

    /** @test */
    public function it_updates_an_invoice_group(): void
    {
        $this->markTestIncomplete('This test has not been implemented yet.');

        $invoiceGroup = InvoiceGroup::factory()->create();
        $editedInvoiceGroup = InvoiceGroup::factory()->make()->toArray();

        $response = $this->putJson(route('api.invoice-groups.update', ['invoice_group' => $invoiceGroup->invoice_group_id]), $editedInvoiceGroup);
        $response->assertStatus(200);
    }

    /** @test */
    public function it_deletes_an_invoice_group(): void
    {
        $this->markTestIncomplete('This test has not been implemented yet.');

        $invoiceGroup = InvoiceGroup::factory()->create();

        $response = $this->deleteJson(route('api.invoice-groups.destroy', ['invoice_group' => $invoiceGroup->invoice_group_id]));
        $response->assertStatus(204);
    }
}
