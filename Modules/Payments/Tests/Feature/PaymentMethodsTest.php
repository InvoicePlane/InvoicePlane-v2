<?php

namespace Modules\Payments\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
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
class PaymentMethodsTest extends AbstractCompanyPanelTestCase
{
    protected User $user;

    // region smoke
    #[Test]
    #[Group('module')]
    public function it_lists_payment_methods(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        PaymentMethod::factory()->create([
            'company_id'          => $company->id,
            'payment_method_name' => 'Credit Card',
        ]);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListPaymentMethods::class);

        /* assert */
        $component->assertSuccessful();
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

        /* arrange */
        /**
         * Payload from `all_posts.json`:
         * {
         *     "payment_method_name": "Credit Card",
         * }
         */
        $payload = [
            'company_id'          => $company->id,
            'payment_method_name' => 'Credit Card',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreatePaymentMethod::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoFormErrors()
            ->assertSee('Credit Card');

        $this->assertDatabaseHas('payment_methods', $payload);
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
    public function it_fails_to_create_a_payment_method_without_required_payment_method_name(): void
    {
        $this->markTestIncomplete();

        /* arrange */
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

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreatePaymentMethod::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['payment_method_name' => 'required']);

        if (app()->isLocal()) {
            dump($payload);
        }

        $this->assertDatabaseMissing('payment_methods', $payload);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_payment_method(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $user = User::factory()->create();

        $paymentMethod = PaymentMethod::factory()->create([
            'payment_method_name' => '::original_payment_method_name::',
        ]);

        $updatedData = [
            'payment_method_name' => 'updated_payment_method_name',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)->test(EditPaymentMethod::class, ['record' => $paymentMethod->payment_method_id])->set('data.payment_method_name', $updatedData['payment_method_name'])->call('save');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('payment_methods', array_merge($updatedData, [
            'payment_method_id'   => $paymentMethod->payment_method_id,
            'payment_method_name' => $paymentMethod->payment_method_name,
        ]));
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_a_payment_method(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestSkipped('Not implemented yet');
        // $this->authenticated();

        $user = User::factory()->create();

        $paymentMethod = PaymentMethod::factory()->create();

        /* act */
        $component = Livewire::actingAs($this->user)->test(ListPaymentMethods::class)->callTableAction('delete', $paymentMethod);

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('payment_methods', [
            'payment_method_id' => $paymentMethod->payment_method_id,
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_method_with_attached_payment(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $method = PaymentMethod::factory()->for($this->user->companies()->first())->create();
        Payment::factory()->for($this->user->companies()->first())->for($method)->create();

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
