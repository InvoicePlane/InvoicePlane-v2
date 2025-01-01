<?php

namespace Modules\Payments\Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Sanctum\Sanctum;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;
use Modules\Core\tests\ApiTestTrait;
use Modules\Payments\Models\PaymentMethod;

class PaymentMethodApiTest extends AbstractTestCase
{
    use ApiTestTrait;
    use RefreshDatabase;
    use WithoutMiddleware;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_returns_payment_methods_index(): void
    {
        Sanctum::actingAs(User::factory()->create());

        PaymentMethod::factory()->count(5)->create([
            'payment_method_name' => '::payment_method_name::',
        ]);
        $response = $this->get(route('api.payment-methods.index'));
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'name',
                ],
            ],
        ]);

        $response->assertJsonFragment(['name' => '::payment_method_name::']);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_creates_a_payment_method(): void
    {
        $initialPaymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $response = $this->post(route('api.payment-methods.store'), [
            'payment_method_name' => $initialPaymentMethod->payment_method_name,
        ]);

        $response->assertStatus(200);

        $initialPaymentMethod->refresh();
        $response->assertJsonFragment(['name' => '::payment_method_name::']);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_returns_error_when_posting_payment_method_with_wrong_data(): void
    {
        $initialPaymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $response = $this->post(route('api.payment-methods.store'), [
            'family_name' => $initialPaymentMethod->payment_method_name,
        ]);

        $response->assertStatus(422);

        $response->assertJsonFragment(['message' => 'The given data was invalid']);
        $response->assertJsonFragment(['errors' => ['payment_method_name' => ['The payment method name field is required.']], ]);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_updates_a_payment_method(): void
    {
        $initialPaymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $response = $this->post(route('api.payment-methods.store'), [
            'payment_method_name' => $initialPaymentMethod->payment_method_name,
        ]);

        $response->assertStatus(200);

        $initialPaymentMethod->refresh();
        $response->assertJsonFragment(['name' => '::payment_method_name::']);

        $updatedData = [
            'payment_method_name' => '::updated_payment_method_name::',
        ];

        Sanctum::actingAs(User::factory()->create());

        $response = $this->put(route('api.payment-methods.update', ['paymentMethod' => $initialPaymentMethod->payment_method_id]), $updatedData);

        $response->assertStatus(200);

        $initialPaymentMethod->refresh();

        $response->assertJsonFragment(['name' => '::updated_payment_method_name::']);

        $this->assertEquals($updatedData['payment_method_name'], $initialPaymentMethod->payment_method_name);
    }

    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_deletes_a_payment_method(): void
    {
        $initialPaymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->deleteJson(
            route('api.payment-methods.destroy', ['paymentMethod' => $initialPaymentMethod->payment_method_id])
        );

        $response->assertSuccessful();

        $getPaymentMethodResponse = $this->getJson(
            route('api.payment-methods.show', [
                'user' => $initialPaymentMethod->payment_method_id,
            ])
        );

        $getPaymentMethodResponse->assertNotFound();
    }
}
