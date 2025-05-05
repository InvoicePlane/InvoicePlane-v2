<?php

namespace Modules\Payments\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Payments\Filament\Company\Resources\PaymentResource;
use Modules\Payments\Filament\Company\Resources\PaymentResource\Pages\CreatePayment;
use Modules\Payments\Filament\Company\Resources\PaymentResource\Pages\EditPayment;
use Modules\Payments\Filament\Company\Resources\PaymentResource\Pages\ListPayments;
use Modules\Payments\Models\Payment;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(PaymentResource::class)]

class PaymentsTest extends AbstractTestCase
{
    use WithFaker;
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    // region smoke
    #[Test]
    #[Group('module')]
    public function it_lists_payments(): void
    {
        $this->markTestIncomplete('payable_type');
        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        Payment::factory()->create([
            'company_id'     => $company->id,
            'payment_amount' => 99.99,
        ]);

        Livewire::test(ListPayments::class)
            ->assertSee('99.99');
    }
    // endregion

    // region crud
    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "invoice_id": 2,
     *   "payment_method_id": 3,
     *   "payment_status": "completed",
     *   "paid_at": "2025-05-01",
     *   "payment_amount": "99.99"
     * }
     */
    public function it_creates_a_payment(): void
    {
        $this->markTestIncomplete();
        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        $payload = [
            'company_id'        => $company->id,
            'invoice_id'        => 2,
            'payment_method_id' => 3,
            'payment_status'    => 'completed',
            'paid_at'           => '2025-05-01',
            'payment_amount'    => 99.99,
        ];

        Livewire::test(CreatePayment::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "payment_status": "completed"
     * }
     */
    public function it_fails_to_create_payment_without_required_fields(): void
    {
        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        $payload = [
            'payment_status' => 'completed',
        ];

        Livewire::test(CreatePayment::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['payment_amount' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Payments\Filament\Company\Resources\PaymentResource.
     *
     * @payload
     * {
     * "company_id": "Value",
     * "invoice_id": "Value",
     * "payment_method_id": "Value",
     * "payment_status": "Value",
     * "paid_at": "2025-04-30",
     * "payment_amount": "9.99"
     * }
     */
    public function it_updates_a_payment(): void
    {
        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = Payment::factory()->create();

        $payload = [
            'company_id'        => 'Value',
            'invoice_id'        => 'Value',
            'payment_method_id' => 'Value',
            'payment_status'    => 'Value',
            'paid_at'           => '2025-04-30',
            'payment_amount'    => 9.99,
        ];

        Livewire::test(EditPayment::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Payments\Filament\Company\Resources\PaymentResource.
     *
     * @payload
     * {
     * "company_id": "Value",
     * "invoice_id": "Value",
     * "payment_method_id": "Value",
     * "payment_status": "Value",
     * "paid_at": "2025-04-30",
     * "payment_amount": "9.99"
     * }
     */
    public function it_deletes_a_payment(): void
    {
        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = Payment::factory()->create();

        Livewire::test(ListPayments::class)
            ->callTableAction('delete', $record);

        $this->assertDatabaseMissing('payments', ['id' => $record->id]);
    }

    // endregion

    // region usp

    // endregion
}
