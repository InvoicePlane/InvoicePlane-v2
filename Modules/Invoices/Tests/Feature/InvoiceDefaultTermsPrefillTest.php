<?php

namespace Modules\Invoices\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Models\Company;
use Modules\Core\Models\Setting;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\CreateInvoice;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * Per #258: the company's `invoice_default_terms` setting should pre-fill
 * the `invoice_terms` field when creating a new invoice.
 */
#[CoversClass(CreateInvoice::class)]
class InvoiceDefaultTermsPrefillTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    public function it_prefills_invoice_terms_from_the_company_setting(): void
    {
        /* Arrange */
        Setting::saveForCompany(
            $this->company->id,
            Setting::KEY_INVOICE_DEFAULT_TERMS,
            'Payment due within 30 days.'
        );

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateInvoice::class);

        /* Assert */
        $component->assertFormSet(['invoice_terms' => 'Payment due within 30 days.']);
    }

    #[Test]
    public function it_leaves_invoice_terms_empty_when_no_company_setting_exists(): void
    {
        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateInvoice::class);

        /* Assert */
        $component->assertFormSet(['invoice_terms' => null]);
    }

    #[Test]
    public function it_does_not_leak_another_companys_default_terms(): void
    {
        /* Arrange */
        $other = Company::factory()->create();
        Setting::saveForCompany($other->id, Setting::KEY_INVOICE_DEFAULT_TERMS, 'Other company terms.');

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateInvoice::class);

        /* Assert */
        $component->assertFormSet(['invoice_terms' => null]);
    }

    #[Test]
    public function the_prefilled_terms_can_be_overridden_before_saving(): void
    {
        /* Arrange */
        Setting::saveForCompany($this->company->id, Setting::KEY_INVOICE_DEFAULT_TERMS, 'Default terms.');

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateInvoice::class)
            ->fillForm(['invoice_terms' => 'Custom terms for this invoice.']);

        /* Assert */
        $component->assertFormSet(['invoice_terms' => 'Custom terms for this invoice.']);
    }
}
