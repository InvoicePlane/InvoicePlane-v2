<?php

namespace Modules\Invoices\Tests\Feature;

use Modules\Core\Models\Company;
use Modules\Core\Models\Numbering;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Models\Invoice;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class InvoiceDuplicateNumberPreventionTest extends AbstractTestCase
{
    #[Test]
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
        $drafts = Invoice::where('company_id', $company->id)
            ->whereNull('invoice_number')
            ->count();
        $this->assertEquals(3, $drafts);
    }

    #[Test]
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
        $this->assertEquals('paid', $invoice->invoice_status);
    }
}
