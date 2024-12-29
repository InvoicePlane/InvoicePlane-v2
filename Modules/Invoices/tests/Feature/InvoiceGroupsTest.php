<?php

namespace Modules\Invoices\Tests\Feature;

use Modules\Core\tests\AbstractTestCase;
use Modules\Invoices\Models\InvoiceGroup;

class InvoiceGroupsTest extends AbstractTestCase
{
    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_lists_invoice_groups(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->get(route('filament.resources.invoice-groups.index'));
        $response->assertStatus(200);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_creates_an_invoice_group(): void
    {
        // @var array $payload
        $payload = [
            'name'        => 'Test Invoice Group',
            'description' => 'This is a test invoice group.',
        ];

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->post(route('filament.resources.invoice-groups.create'), $payload);
        $response->assertStatus(201);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_updates_an_invoice_group(): void
    {
        $invoiceGroup = InvoiceGroup::factory()->create();

        // @var array $payload
        $payload = [
            'name'        => 'Updated Invoice Group',
            'description' => 'Updated description.',
        ];

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->put(route('filament.resources.invoice-groups.update', $invoiceGroup->invoice_group_id), $payload);
        $response->assertStatus(200);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_deletes_an_invoice_group(): void
    {
        $invoiceGroup = InvoiceGroup::factory()->create();

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->delete(route('filament.resources.invoice-groups.delete', $invoiceGroup->invoice_group_id));
        $response->assertStatus(200);
    }
}
