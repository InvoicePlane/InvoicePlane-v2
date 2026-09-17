<?php

namespace Modules\Core\Tests\Feature\Admin;

use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Modules\Core\Filament\Admin\Resources\Companies\Pages\ListCompanies;
use Modules\Core\Filament\Admin\Resources\Companies\Tables\Actions\AssignPaymentMethodBulkAction;
use Modules\Core\Models\Company;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use Modules\Payments\Enums\PaymentMethod;
use PHPUnit\Framework\Attributes\Test;

class CompanyPaymentMethodBulkActionTest extends AbstractAdminPanelTestCase
{
    #[Test]
    public function it_assigns_payment_method_to_multiple_companies(): void
    {
        /* Arrange */
        $companies = Company::factory()->count(2)->create();
        $ids       = $companies->pluck('id')->all();

        /* Act */
        Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->selectTableRecords($ids)
            ->callAction(TestAction::make('assignPaymentMethod')->table()->bulk(), ['payment_method' => PaymentMethod::PAYPAL->value])
            ->assertHasNoFormErrors();

        /* Assert */
        foreach ($companies as $company) {
            $this->assertDatabaseHas('company_payment_method', [
                'company_id'     => $company->id,
                'payment_method' => PaymentMethod::PAYPAL->value,
            ]);
        }
    }

    #[Test]
    public function it_does_not_duplicate_the_pivot_row_when_the_payment_method_is_already_assigned(): void
    {
        /* Arrange */
        $company = Company::factory()->create();
        $company->paymentMethods()->create(['payment_method' => PaymentMethod::STRIPE->value]);

        /* Act */
        Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->selectTableRecords([$company->id])
            ->callAction(TestAction::make('assignPaymentMethod')->table()->bulk(), ['payment_method' => PaymentMethod::STRIPE->value])
            ->assertHasNoFormErrors();

        /* Assert */
        $this->assertDatabaseCount('company_payment_method', 1);
    }

    #[Test]
    public function it_allows_the_same_company_to_have_multiple_different_payment_methods(): void
    {
        /* Arrange */
        $company = Company::factory()->create();
        $company->paymentMethods()->create(['payment_method' => PaymentMethod::CASH->value]);

        /* Act */
        Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->selectTableRecords([$company->id])
            ->callAction(TestAction::make('assignPaymentMethod')->table()->bulk(), ['payment_method' => PaymentMethod::BANK_TRANSFER->value])
            ->assertHasNoFormErrors();

        /* Assert */
        $this->assertDatabaseCount('company_payment_method', 2);
        $this->assertDatabaseHas('company_payment_method', [
            'company_id'     => $company->id,
            'payment_method' => PaymentMethod::CASH->value,
        ]);
        $this->assertDatabaseHas('company_payment_method', [
            'company_id'     => $company->id,
            'payment_method' => PaymentMethod::BANK_TRANSFER->value,
        ]);
    }

    #[Test]
    public function it_lists_every_enum_case_as_a_selectable_option(): void
    {
        /* Act */
        $options = AssignPaymentMethodBulkAction::getPaymentMethodOptions();

        /* Assert */
        $this->assertSame(
            collect(PaymentMethod::cases())->pluck('value')->all(),
            array_keys($options)
        );
        $this->assertSame('PayPal', $options[PaymentMethod::PAYPAL->value]);
    }

    #[Test]
    public function it_requires_a_payment_method_to_be_selected(): void
    {
        /* Arrange */
        $company = Company::factory()->create();

        /* Act & Assert */
        Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->selectTableRecords([$company->id])
            ->callAction(TestAction::make('assignPaymentMethod')->table()->bulk(), ['payment_method' => null])
            ->assertHasFormErrors(['payment_method' => 'required']);

        $this->assertDatabaseCount('company_payment_method', 0);
    }

    #[Test]
    public function it_assigns_the_payment_method_to_every_selected_company_in_one_bulk_call(): void
    {
        /* Arrange */
        $companies = Company::factory()->count(3)->create();

        /* Act */
        Livewire::actingAs($this->superAdmin())
            ->test(ListCompanies::class)
            ->selectTableRecords($companies->pluck('id')->all())
            ->callAction(TestAction::make('assignPaymentMethod')->table()->bulk(), ['payment_method' => PaymentMethod::CREDIT_CARD->value])
            ->assertHasNoFormErrors();

        /* Assert */
        $this->assertDatabaseCount('company_payment_method', 3);
    }
}
