<?php

namespace Modules\Payments\Tests\Api;

use Modules\Core\Support\Results\Payments;

use Modules\Core\Tests\AbstractTestCase;

use Modules\Payments\Models\Payment;

use Modules\Payments\Tests\Api\PaymentsApiTest;

use Modules\Core\Tests\AbstractTestCase;

class PaymentsApiTest extends AbstractTestCase
{
    public function test_api_returns_all_payments(): void
    {
        $this->markTestSkipped('Test not implemented yet');
        $response = $this->getJson(route('api.payments.index'));

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['payment_id', 'payment_amount', 'payment_date', 'client_id'],
            ],
        ]);
    }

    public function test_api_can_create_payment(): void
    {
        $this->markTestSkipped('Test not implemented yet');
        $data = [
            'payment_amount'    => 150,
            'payment_date'      => now()->toDateString(),
            'payment_method_id' => 1,
            'client_id'         => 1,
        ];

        $response = $this->postJson(route('api.payments.store'), $data);

        $response->assertSuccessful();
        $this->assertDatabaseHas('payments', $data);
    }

    public function test_api_can_update_payment(): void
    {
        $this->markTestSkipped('Test not implemented yet');
        $payment = Payment::factory()->create();
        $data    = ['payment_amount' => 300];

        $response = $this->putJson(route('api.payments.update', $payment->payment_id), $data);

        $response->assertSuccessful();
        $this->assertDatabaseHas('payments', $data);
    }

    public function test_api_can_delete_payment(): void
    {
        $this->markTestSkipped('Test not implemented yet');
        $payment = Payment::factory()->create();

        $response = $this->deleteJson(route('api.payments.destroy', $payment->payment_id));

        $response->assertSuccessful();
        $this->assertDatabaseMissing('payments', ['payment_id' => $payment->payment_id]);
    }
}
