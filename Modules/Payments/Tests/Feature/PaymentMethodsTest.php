<?php

namespace Modules\Payments\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Payments\Filament\Company\Resources\PaymentMethodResource;
use Modules\Payments\Filament\Company\Resources\PaymentMethodResource\Pages\CreatePaymentMethod;
use Modules\Payments\Filament\Company\Resources\PaymentMethodResource\Pages\ListPaymentMethods;
use Modules\Payments\Models\PaymentMethod;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(PaymentMethodResource::class)]

class PaymentMethodsTest extends AbstractTestCase
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

        $payload = [
            'company_id'          => $company->id,
            'payment_method_name' => 'Credit Card',
        ];

        Livewire::test(CreatePaymentMethod::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertSee('Credit Card');
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
    public function it_fails_to_create_payment_method_without_name(): void
    {
        $this->markTestIncomplete();

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        $payload = [
            'company_id' => $company->id,
        ];

        Livewire::test(CreatePaymentMethod::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['payment_method_name' => 'required']);

        if (app()->isLocal()) {
            dump($payload);
        }
    }
    // endregion
}
