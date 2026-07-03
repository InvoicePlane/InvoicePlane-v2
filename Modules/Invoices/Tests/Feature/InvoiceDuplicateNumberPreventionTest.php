<?php

namespace Modules\Invoices\Tests\Feature;

use Modules\Core\Models\Company;
use Modules\Core\Models\Numbering;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class InvoiceDuplicateNumberPreventionTest extends AbstractAdminPanelTestCase
{
    #[Test]
    #[Group('failing')]
    public function it_prevents_duplicate_invoice_numbers_within_same_company(): void
    {
        /* Arrange */
        $company   = Company::factory()->create();
        $numbering = Numbering::factory()->for($company)->create();

        Invoice::factory()->for($company)->create([
            'numbering_id'   => $numbering->id,
            'invoice_number' => 'INV-2025-0001',
        ]);

        /* Act & Assert */
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Duplicate invoice number 'INV-2025-0001'");

        Invoice::factory()->for($company)->create([
            'numbering_id'   => $numbering->id,
            'invoice_number' => 'INV-2025-0001',
        ]);
    }

    #[Test]
    public function it_allows_same_invoice_number_in_different_companies(): void
    {
        /* Arrange */
        $company1   = Company::factory()->create();
        $company2   = Company::factory()->create();
        $numbering1 = Numbering::factory()->for($company1)->create();
        $numbering2 = Numbering::factory()->for($company2)->create();

        Invoice::factory()->for($company1)->create([
            'numbering_id'   => $numbering1->id,
            'invoice_number' => 'INV-2025-0001',
        ]);

        /* Act */
        $invoice2 = Invoice::factory()->for($company2)->create([
            'numbering_id'   => $numbering2->id,
            'invoice_number' => 'INV-2025-0001',
        ]);

        /* Assert */
        $this->assertNotNull($invoice2);
        $this->assertEquals('INV-2025-0001', $invoice2->invoice_number);
        $this->assertEquals($company2->id, $invoice2->company_id);
    }

    #[Test]
    #[Group('failing')]
    public function it_allows_multiple_null_invoice_numbers_for_drafts(): void
    {
        /* Arrange */
        $company   = Company::factory()->create();
        $numbering = Numbering::factory()->for($company)->create();

        /* Act */
        $draft1 = Invoice::factory()->for($company)->create([
            'numbering_id'   => $numbering->id,
            'invoice_number' => null,
        ]);

        $draft2 = Invoice::factory()->for($company)->create([
            'numbering_id'   => $numbering->id,
            'invoice_number' => null,
        ]);

        $draft3 = Invoice::factory()->for($company)->create([
            'numbering_id'   => $numbering->id,
            'invoice_number' => null,
        ]);

        /* Assert */
        $this->assertNull($draft1->invoice_number);
        $this->assertNull($draft2->invoice_number);
        $this->assertNull($draft3->invoice_number);

        // All three drafts should exist
        $drafts = Invoice::query()->where('company_id', $company->id)
            ->whereNull('invoice_number')
            ->count();
        $this->assertEquals(3, $drafts);
    }

    #[Test]
    public function it_allows_parent_invoice_to_be_edited_when_a_credit_note_shares_its_number(): void
    {
        /* Arrange */
        $company   = Company::factory()->create();
        $numbering = Numbering::factory()->for($company)->create();

        $parent = Invoice::factory()->for($company)->create([
            'numbering_id'   => $numbering->id,
            'invoice_number' => 'INV-2025-0001',
            'invoice_sign'   => '1',
        ]);

        // Credit note shares the parent's number (allowed by design)
        Invoice::factory()->for($company)->create([
            'numbering_id'            => $numbering->id,
            'invoice_number'          => 'INV-2025-0001',
            'invoice_sign'            => '-1',
            'creditinvoice_parent_id' => $parent->id,
        ]);

        /* Act — editing the parent must not throw */
        $parent->update(['invoice_status' => InvoiceStatus::PAID]);
        $parent->refresh();

        /* Assert */
        $this->assertEquals(InvoiceStatus::PAID, $parent->invoice_status);
    }

    #[Test]
    public function it_allows_creating_a_credit_note_with_the_same_number_as_its_parent(): void
    {
        /* Arrange */
        $company   = Company::factory()->create();
        $numbering = Numbering::factory()->for($company)->create();

        $parent = Invoice::factory()->for($company)->create([
            'numbering_id'   => $numbering->id,
            'invoice_number' => 'INV-2025-0001',
            'invoice_sign'   => '1',
        ]);

        /* Act */
        $creditNote = Invoice::factory()->for($company)->create([
            'numbering_id'            => $numbering->id,
            'invoice_number'          => 'INV-2025-0001',
            'invoice_sign'            => '-1',
            'creditinvoice_parent_id' => $parent->id,
        ]);

        /* Assert */
        $this->assertDatabaseHas('invoices', ['id' => $creditNote->id, 'creditinvoice_parent_id' => $parent->id]);
    }

    #[Test]
    #[Group('failing')]
    public function it_allows_updating_invoice_without_changing_number(): void
    {
        /* Arrange */
        $company   = Company::factory()->create();
        $numbering = Numbering::factory()->for($company)->create();

        $invoice = Invoice::factory()->for($company)->create([
            'numbering_id'   => $numbering->id,
            'invoice_number' => 'INV-2025-0001',
        ]);

        /* Act */
        $invoice->update([
            'invoice_status' => 'paid',
        ]);
        $invoice->refresh();

        /* Assert */
        $this->assertEquals('INV-2025-0001', $invoice->invoice_number);
        $this->assertEquals('paid', $invoice->invoice_status->value);
    }
}
