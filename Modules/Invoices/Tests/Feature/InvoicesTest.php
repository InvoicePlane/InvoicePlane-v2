<?php

namespace Modules\Invoices\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Filament\Company\Resources\InvoiceResource;
use Modules\Invoices\Filament\Company\Resources\InvoiceResource\Pages\CreateInvoice;
use Modules\Invoices\Filament\Company\Resources\InvoiceResource\Pages\EditInvoice;
use Modules\Invoices\Filament\Company\Resources\InvoiceResource\Pages\ListInvoices;
use Modules\Invoices\Models\Invoice;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(InvoiceResource::class)]
class InvoicesTest extends AbstractTestCase
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
    #[Group('smoke')]
    public function it_lists_invoices(): void
    {
        $this->markTestIncomplete();

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        Invoice::factory()->create([
            'company_id'     => $company->id,
            'invoice_number' => 'INV-2025-A',
        ]);

        Livewire::test(ListInvoices::class)
            ->assertSee('INV-2025-A');
    }
    // endregion

    // region crud
    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "company_id": 1,
     *   "customer_id": 2,
     *   "document_group_id": 3,
     *   "creditinvoice_parent_id": null,
     *   "user_id": 4,
     *   "invoice_number": "INV-1001",
     *   "invoice_status": "draft",
     *   "invoiced_at": "2025-05-01",
     *   "invoice_due_at": "2025-05-10",
     *   "invoiceItems": [
     *      { "item_name": "Design", "quantity": 1, "price": 150.00 }
     *   ]
     *   "invoice_discount_amount": "0.00",
     *   "invoice_discount_percent": "0.00",
     *   "invoice_item_tax_total": "0.00",
     *   "invoice_item_subtotal": "100.00",
     *   "invoice_tax_total": "0.00",
     *   "invoice_total": "100.00",
     *   "invoice_password": null,
     *   "invoice_url_key": "abc123",
     *   "is_read_only": false,
     *   "invoice_terms": "Net 30"
     * }
     */
    public function it_creates_an_invoice(): void
    {
        $this->markTestIncomplete();

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        Livewire::test(CreateInvoice::class)
            ->fillForm([
                'customer_id'    => 1,
                'invoice_number' => 'INV-2025-A',
                'invoice_status' => 'draft',
                'invoiced_at'    => '2025-05-05',
                'invoice_due_at' => '2025-05-10',
                'invoiceItems'   => [
                    ['item_name' => 'Design', 'quantity' => 1, 'price' => 150.00],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * @payload {
     *   "company_id": 1,
     *   "document_group_id": 3,
     *   "creditinvoice_parent_id": null,
     *   "user_id": 4,
     *   "invoice_number": "INV-1001",
     *   "invoice_status": "draft",
     *   "invoiced_at": "2025-05-01",
     *   "invoice_due_at": "2025-05-10",
     *   "invoiceItems": [
     *      { "item_name": "Design", "quantity": 1, "price": 150.00 }
     *   ]
     *   "invoice_discount_amount": "0.00",
     *   "invoice_discount_percent": "0.00",
     *   "invoice_item_tax_total": "0.00",
     *   "invoice_item_subtotal": "100.00",
     *   "invoice_tax_total": "0.00",
     *   "invoice_total": "100.00",
     *   "invoice_password": null,
     *   "invoice_url_key": "abc123",
     *   "is_read_only": false,
     *   "invoice_terms": "Net 30"
     * }
     */
    public function it_fails_to_create_invoice_without_customer(): void
    {
        $this->markTestIncomplete();

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        $payload = [
            'company_id'               => $company->id,
            'customer_id'              => 2,
            'document_group_id'        => 3,
            'creditinvoice_parent_id'  => null,
            'user_id'                  => $user->id,
            'invoice_number'           => 'INV-1001',
            'invoice_status'           => 'draft',
            'invoiced_at'              => '2025-05-01',
            'invoice_due_at'           => '2025-05-10',
            'invoice_discount_amount'  => 0.00,
            'invoice_discount_percent' => 0.00,
            'invoice_item_tax_total'   => 0.00,
            'invoice_item_subtotal'    => 100.00,
            'invoice_tax_total'        => 0.00,
            'invoice_total'            => 100.00,
            'invoice_password'         => null,
            'invoice_url_key'          => 'abc123',
            'is_read_only'             => false,
            'invoice_is_altered'       => false,
            'invoice_terms'            => 'Net 30',
        ];

        Livewire::test(CreateInvoice::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "company_id": 1,
     *   "customer_id": 2,
     *   "document_group_id": 3,
     *   "creditinvoice_parent_id": null,
     *   "user_id": 4,
     *   "invoice_number": "INV-1001",
     *   "invoice_status": "draft",
     *   "invoiced_at": "2025-05-01",
     *   "invoice_due_at": "2025-05-10",
     *   "invoiceItems": [
     *   ]
     *   "invoice_discount_amount": "0.00",
     *   "invoice_discount_percent": "0.00",
     *   "invoice_item_tax_total": "0.00",
     *   "invoice_item_subtotal": "100.00",
     *   "invoice_tax_total": "0.00",
     *   "invoice_total": "100.00",
     *   "invoice_password": null,
     *   "invoice_url_key": "abc123",
     *   "is_read_only": false,
     *   "invoice_terms": "Net 30"
     * }
     */
    public function it_fails_to_create_invoice_without_invoice_items(): void
    {
        $this->markTestIncomplete();
        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        $payload = [
            'company_id'               => $company->id,
            'customer_id'              => 2,
            'document_group_id'        => 3,
            'creditinvoice_parent_id'  => null,
            'user_id'                  => $user->id,
            'invoice_number'           => 'INV-1001',
            'invoice_status'           => 'draft',
            'invoiced_at'              => '2025-05-01',
            'invoice_due_at'           => '2025-05-10',
            'invoice_discount_amount'  => 0.00,
            'invoice_discount_percent' => 0.00,
            'invoice_item_tax_total'   => 0.00,
            'invoice_item_subtotal'    => 100.00,
            'invoice_tax_total'        => 0.00,
            'invoice_total'            => 100.00,
            'invoice_password'         => null,
            'invoice_url_key'          => 'abc123',
            'is_read_only'             => false,
            'invoice_is_altered'       => false,
            'invoice_terms'            => 'Net 30',
        ];

        Livewire::test(CreateInvoice::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasErrors(['customer_id', 'invoiceItems.0.item_name', 'invoiceItems.0.quantity', 'invoiceItems.0.price']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "company_id": 1,
     *   "customer_id": 2,
     *   "document_group_id": 3,
     *   "creditinvoice_parent_id": null,
     *   "user_id": 4,
     *   "invoice_number": "INV-1001",
     *   "invoice_status": "draft",
     *   "invoiced_at": "2025-05-01",
     *   "invoice_due_at": "2025-05-10",
     *   "invoiceItems": [
     *      { "item_name": "Design", "quantity": 1, "price": 150.00 }
     *   ]
     *   "invoice_discount_amount": "0.00",
     *   "invoice_discount_percent": "0.00",
     *   "invoice_item_tax_total": "0.00",
     *   "invoice_item_subtotal": "100.00",
     *   "invoice_tax_total": "0.00",
     *   "invoice_total": "100.00",
     *   "invoice_password": null,
     *   "invoice_url_key": "abc123",
     *   "is_read_only": false,
     *   "invoice_terms": "Net 30"
     * }
     */
    public function it_updates_a_invoice(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $record = Invoice::factory()->create();

        $payload = [
            'company_id'               => 'Value',
            'customer_id'              => 'Value',
            'document_group_id'        => 'Value',
            'creditinvoice_parent_id'  => 'Value',
            'user_id'                  => 'Value',
            'invoice_number'           => 'Example',
            'invoice_status'           => 'Value',
            'invoiced_at'              => '2025-04-30',
            'invoice_due_at'           => '2025-04-30',
            'invoice_discount_amount'  => 9.99,
            'invoice_discount_percent' => 9.99,
            'invoice_item_tax_total'   => 9.99,
            'invoice_item_subtotal'    => 9.99,
            'invoice_tax_total'        => 9.99,
            'invoice_total'            => 9.99,
            'invoice_password'         => 'Example',
            'invoice_url_key'          => 'Example',
            'is_read_only'             => true,
            'invoice_is_altered'       => 'Example',
            'invoice_terms'            => 'Example',
        ];

        Livewire::test(EditInvoice::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload {
     *   "company_id": 1,
     *   "customer_id": 2,
     *   "document_group_id": 3,
     *   "creditinvoice_parent_id": null,
     *   "user_id": 4,
     *   "invoice_number": "INV-1001",
     *   "invoice_status": "draft",
     *   "invoiced_at": "2025-05-01",
     *   "invoice_due_at": "2025-05-10",
     *   "invoiceItems": [
     *      { "item_name": "Design", "quantity": 1, "price": 150.00 }
     *   ]
     *   "invoice_discount_amount": "0.00",
     *   "invoice_discount_percent": "0.00",
     *   "invoice_item_tax_total": "0.00",
     *   "invoice_item_subtotal": "100.00",
     *   "invoice_tax_total": "0.00",
     *   "invoice_total": "100.00",
     *   "invoice_password": null,
     *   "invoice_url_key": "abc123",
     *   "is_read_only": false,
     *   "invoice_terms": "Net 30"
     * }
     */
    public function it_deletes_a_invoice(): void
    {
        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = Invoice::factory()->create();

        Livewire::test(ListInvoices::class)
            ->callTableAction('delete', $record);

        $this->assertDatabaseMissing('invoices', ['id' => $record->id]);
    }
    // endregion

    // region usp
    /**
     * @payload ["invoiceId" => $invoice->id]
     */
    #[Test]
    #[Group('spicy')]
    public function it_copies_an_invoice(): void
    {
        $this->markTestIncomplete();

        $invoice = Invoice::factory()->create([
            'amount' => 200,
            'status' => 'draft',
        ]);

        $component = Livewire::test(CopyInvoice::class, ['invoiceId' => $invoice->id])
            ->fillForm(['count' => 2])
            ->call('save');

        $component
            ->assertHasNoFormErrors()
            ->assertEmitted('invoiceCopied');

        if (app()->isLocal()) {
            dump(Invoice::where('original_id', $invoice->id)->get());
        }

        $this->assertEquals(2, Invoice::where('original_id', $invoice->id)->count());
        $this->assertDatabaseHas('invoices', [
            'original_id' => $invoice->id,
            'status'      => $invoice->status,
        ]);
    }

    /**
     * @payload ["invoiceId" => $invoice->id]
     */
    #[Test]
    #[Group('spicy')]
    public function it_clones_an_invoice(): void
    {
        $this->markTestIncomplete();

        $invoice = Invoice::factory()->create([
            'amount' => 150,
            'status' => 'pending',
        ]);

        $component = Livewire::test(CloneInvoice::class, ['invoiceId' => $invoice->id])
            ->fillForm(['template' => 'standard'])
            ->call('save');

        $component
            ->assertHasNoFormErrors()
            ->assertEmitted('invoiceCloned')
            ->assertRedirect(route('invoices.edit', ['invoice' => Invoice::latest()->first()->id]));

        $newInvoice = Invoice::latest()->first();

        if (app()->isLocal()) {
            dump($newInvoice);
        }

        $this->assertDatabaseHas('invoices', [
            'id'     => $newInvoice->id,
            'amount' => $invoice->amount,
            'status' => $invoice->status,
        ]);

        $this->assertNotEquals($invoice->id, $newInvoice->id);
        $this->assertTrue($newInvoice->created_at->gt($invoice->created_at));
    }

    /**
     * @payload ["invoiceId" => $invoice->id]
     */
    #[Test]
    #[Group('spicy')]
    public function it_exports_an_invoice_to_pdf(): void
    {
        $this->markTestIncomplete();

        Storage::fake('local');

        $invoice = Invoice::factory()->create();

        $component = Livewire::test(ExportInvoice::class, ['invoiceId' => $invoice->id])
            ->call('export');

        $component
            ->assertHasNoFormErrors()
            ->assertEmitted('exportCompleted');

        $responseData = $component->lastResponse->getData();
        $this->assertArrayHasKey('url', $responseData);
        $this->assertArrayHasKey('filename', $responseData);

        $path = 'exports/invoices/' . $responseData['filename'];
        Storage::disk('local')->assertExists($path);

        if (app()->isLocal()) {
            dump($path);
        }
    }
    // endregion
}
