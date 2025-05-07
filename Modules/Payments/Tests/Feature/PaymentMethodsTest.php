<?php

namespace Modules\Payments\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Payments\Filament\Company\Resources\PaymentMethodResource;
use Modules\Payments\Filament\Company\Resources\PaymentMethodResource\Pages\CreatePaymentMethod;
use Modules\Payments\Filament\Company\Resources\PaymentMethodResource\Pages\EditPaymentMethod;
use Modules\Payments\Filament\Company\Resources\PaymentMethodResource\Pages\ListPaymentMethods;
use Modules\Payments\Models\Payment;
use Modules\Payments\Models\PaymentMethod;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(PaymentMethodResource::class)]

class PaymentMethodsTest extends AbstractTestCase
{
    use RefreshDatabase;
    use WithFaker;
    use WithoutMiddleware;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->withCompany()->create();
        session(['current_company_id' => $this->user->company_id]);
        $this->withoutExceptionHandling();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    // region smoke
    #[Test]
    #[Group('module')]
    public function it_lists_payment_methods(): void
    {
        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        PaymentMethod::factory()->create([
            'company_id'          => $company->id,
            'payment_method_name' => 'Credit Card',
        ]);

        Livewire::test(ListPaymentMethods::class)
            ->assertSee('Credit Card');
    }

    // endregion

    // region crud
    #[Test]
    #[Group('module')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "payment_method_name": "Credit Card"
     * }
     */
    public function it_creates_a_payment_method(): void
    {
        $this->markTestIncomplete();

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        /**
         * Payload from `all_posts.json`:
         * {
         *     "payment_method_name": "Credit Card",
         * }
         */
        // Payload for creating a payment method
        // @var array $payload
        $payload = [
            'company_id'          => $company->id,
            'payment_method_name' => 'Credit Card',
        ];

        Livewire::test(CreatePaymentMethod::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertSee('Credit Card');

        $this->assertDatabaseHas('payment_methods', array_merge($data, [
            'payment_method_name' => 'Credit Card',
        ]));
    }

    #[Test]
    #[Group('module')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "payment_method_name": "Credit Card"
     * }
     */
    public function it_fails_to_create_a_payment_method_without_payment_method_name(): void
    {
        $this->markTestIncomplete();

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        /**
         * Payload from `all_posts.json`:
         * {
         *     "payment_method_name": "Credit Card",
         * }
         */
        $payload = [
            'company_id'  => $company->id,
            'description' => '::description::',
        ];

        Livewire::test(CreatePaymentMethod::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['payment_method_name' => 'required']);

        if (app()->isLocal()) {
            dump($payload);
        }

        $this->assertDatabaseHas('payment_methods', array_merge($data, [
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]));
    }

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

    public function it_deletes_a_payment_method(): void
    {
        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $user = User::factory()->create();

        $paymentMethod = PaymentMethod::factory()->create();

        Livewire::test(ListPaymentMethods::class)
            ->callTableAction('delete', $paymentMethod)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('payment_methods', [
            'payment_method_id' => $paymentMethod->payment_method_id,
        ]);
    }

    #[Test]
    public function it_fails_to_delete_method_with_attached_payment(): void
    {
        $method = PaymentMethod::factory()->for($this->user->company)->create();
        Payment::factory()->for($this->user->company)->for($method)->create();

        Livewire::actingAs($this->user)
            ->test(ListPaymentMethods::class)
            ->call('delete', $method->id)
            ->assertHasErrors(['delete']);

        $this->assertDatabaseHas('payment_methods', ['id' => $method->id]);
    }
    // endregion

    // region usp
    // endregion
}
