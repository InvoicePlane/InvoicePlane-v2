<?php

namespace Modules\Payments\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;
use Modules\Payments\Models\PaymentMethod;

class PaymentMethodsTest extends AbstractTestCase
{
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
     */
    public function it_shows_payment_methods_index(): void
    {
        $user = User::factory()->create();

        PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        $response = $this->actingAs(user: $user, guard: 'web')->get(route('filament.ivpl.resources.filament.resources.payment-methods.index'));
        $response->assertStatus(200);
        $response->assertSee('::payment_method_name::');
    }

    /**
     * @test
     */
    public function it_creates_a_payment_method(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $user = User::factory()->create();

        /**
         * Payload from `all_posts.json`:
         * {
         *     "payment_method_name": "Credit Card",
         * }
         */
        // Payload for creating a payment method
        // @var array $payload
        $payload = [
            'payment_method_name' => '::payment_method_name::',
        ];

        $response = $this->actingAs(user: $user, guard: 'web')->post(route('filament.ivpl.resources.filament.resources.payment-methods.store'), $payload);
        $response->assertRedirect(route('filament.ivpl.resources.filament.resources.payment-methods.index'));

        $response->assertStatus(201);
        $this->assertDatabaseHas('payment_methods', ['payment_method_name' => '::payment_method_name::']);
    }

    /** @test */
    public function it_fails_to_create_a_payment_method_without_payment_method_name(): void
    {
        $payload = ['description' => '::description::'];

        $response = $this->post(route('filament.ivpl.resources.filament.resources.payment-methods.store'), $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    /**
     * @test
     */
    public function it_updates_a_payment_method(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $user = User::factory()->create();

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::original_payment_method_name::',
        ]);

        // Payload for updating a payment method
        // @var array $payload
        $payload = ['payment_method_name' => '::updated_payment_method_name::'];

        $response = $this->actingAs(user: $user, guard: 'web')->put(route('filament.ivpl.resources.filament.resources.payment-methods.update', ['record' => $paymentMethod->payment_method_id]), $payload);
        $response->assertRedirect(route('filament.ivpl.resources.filament.resources.payment-methods.index'));

        $paymentMethod->refresh();
        $this->assertEquals('::updated_payment_method_name::', $paymentMethod->payment_method_name);
    }

    /**
     * @test
     */
    public function it_deletes_a_payment_method(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $user = User::factory()->create();

        $paymentMethod = PaymentMethod::factory()->create();

        $response = $this->actingAs(user: $user, guard: 'web')->delete(route('filament.ivpl.resources.filament.resources.payment-methods.destroy', ['record' => $paymentMethod->payment_method_id]));
        $response->assertRedirect(route('filament.ivpl.resources.filament.resources.payment-methods.index'));

        $this->assertDatabaseMissing('payment_methods', ['payment_method_id' => $paymentMethod->payment_method_id]);
    }
}
