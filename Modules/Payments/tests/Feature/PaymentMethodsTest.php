<?php

namespace Modules\Payments\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Models\User;
use Modules\Core\tests\AbstractTestCase;
use Modules\Payments\Filament\Resources\PaymentMethodResource\Pages\ManagePaymentMethods;
use Modules\Payments\Models\PaymentMethod;

class PaymentMethodsTest extends AbstractTestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    /** @test */
    public function it_shows_payment_methods_index(): void
    {
        $user = User::factory()->create();

        PaymentMethod::factory()->create([
            'payment_method_name' => '::payment_method_name::',
        ]);

        Livewire::test(ManagePaymentMethods::class)
            ->assertStatus(200)
            ->assertSee('::payment_method_name::');
    }

    /** @test */
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

        Livewire::test(CreatePaymentMethod::class)
            ->assertStatus(200)
            ->set('data.payment_method_name', $payload['payment_method_name'])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payment_methods', array_merge($data, [
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]));
    }

    /** @test */
    public function it_fails_to_create_a_payment_method_without_payment_method_name(): void
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
            'description' => '::description::',
        ];

        Livewire::test(CreatePaymentMethod::class)
            ->assertStatus(200)
            ->set('data.description', $payload['description'])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payment_methods', array_merge($data, [
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]));
    }

    /** @test */
    public function it_updates_a_payment_method(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $user = User::factory()->create();

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::original_payment_method_name::',
        ]);

        $updatedData = [
            'payment_method_name' => 'updated_payment_method_name',
        ];

        Livewire::test(EditPaymentMethod::class, ['record' => $paymentMethod->payment_method_id])
            ->set('data.payment_method_name', $updatedData['payment_method_name'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payment_methods', array_merge($updatedData, [
            'payment_method_id'   => $paymentMethod->payment_method_id,
            'payment_method_name' => $paymentMethod->payment_method_name,
        ]));
    }

    /** @test */
    public function it_deletes_a_payment_method(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $user = User::factory()->create();

        $paymentMethod = PaymentMethod::factory()->create();

        Livewire::test(ManagePaymentMethods::class)
            ->callTableAction('delete', $paymentMethod)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('payment_methods', [
            'payment_method_id' => $paymentMethod->payment_method_id,
        ]);
    }
}
