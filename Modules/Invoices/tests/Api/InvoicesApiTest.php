<?php

namespace Modules\Invoices\Tests\Api;

use Modules\Clients\Models\Client;
use Modules\Core\tests\AbstractTestCase;
use Modules\Invoices\Models\Invoice;
use Modules\Products\Models\Product;

class InvoicesApiTest extends AbstractTestCase
{
    // region CRUD Tests

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_lists_invoices(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->getJson(route('api.invoices.index'));
        $response->assertStatus(200);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_creates_an_invoice(): void
    {
        /** @var array $payload */
        $payload = [
            'client_id'    => Client::factory()->create()->id,
            'invoice_date' => now()->toDateString(),
            'due_date'     => now()->addDays(30)->toDateString(),
            'items'        => [
                [
                    'product_id' => Product::factory()->create()->id,
                    'quantity'   => 2,
                    'price'      => 50.00,
                ],
            ],
        ];

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->postJson(route('api.invoices.store'), $payload);
        $response->assertStatus(201);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_updates_an_invoice(): void
    {
        $invoice = Invoice::factory()->create();

        /** @var array $payload */
        $payload = [
            'due_date' => now()->addDays(60)->toDateString(),
        ];

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->putJson(route('api.invoices.update', ['record' => $invoice->id]), $payload);
        $response->assertStatus(200);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_deletes_an_invoice(): void
    {
        $invoice = Invoice::factory()->create();

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $response = $this->deleteJson(route('api.invoices.delete', ['record' => $invoice->id]));
        $response->assertStatus(200);
    }
    // endregion
}
