<?php

namespace Modules\Invoices\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Numbering;
use Modules\Core\Models\TaxRate;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\CreateInvoice;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\EditInvoice;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\ListInvoices;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Models\Payment;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Models\ProductUnit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListInvoices::class)]
class InvoicesTest extends AbstractCompanyPanelTestCase
{
    # region smoke
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['invoice_date' => '2024-11-01', 'invoice_number' => 'INV-0001']
     */
    public function it_lists_invoices(): void
    {
        /* arrange */
        $user            = $this->user;
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'customer_id'              => $customer->id,
            'numbering_id'             => $documentGroup->id,
            'user_id'                  => $user->id,
            'invoice_number'           => 'INV-987654',
            'invoice_status'           => InvoiceStatus::DRAFT,
            'invoice_sign'             => '1',
            'invoiced_at'              => now()->format('Y-m-d'),
            'invoice_due_at'           => now()->addDays(30)->format('Y-m-d'),
            'invoice_discount_amount'  => 10,
            'invoice_discount_percent' => 5,
            'item_tax_total'           => 0,
            'invoice_item_subtotal'    => 450,
            'invoice_tax_total'        => 20,
            'invoice_total'            => 440,
        ];

        Invoice::factory()
            ->for($this->company)
            ->create($payload);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class, ['tenant' => Str::lower($this->company->search_code)]);

        /* assert */
        $component->assertSuccessful();
    }
    # endregion

    # region modals
    #[Test]
    #[Group('crud')]
    public function it_creates_an_invoice_through_a_modal(): void
    {
        /* arrange */
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'invoice_number' => 'INV-987654',
            'customer_id'    => $customer->getKey(),
            'numbering_id'   => $documentGroup->getKey(),
            'user_id'        => $this->user->id,
            'invoice_status' => 'draft',
            'invoiced_at'    => '2025-05-10',
            'invoice_due_at' => '2025-06-09',
            'invoiceItems'   => [
                [
                    'product_id' => $product->getKey(),
                    'quantity'   => 3,
                    'price'      => 150,
                    'discount'   => 0,
                ],
            ],
        ];

        /* act */
        Livewire::actingAs($this->user)->test(ListInvoices::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->assertHasNoFormErrors()
            ->callMountedAction()
            ->assertHasNoFormErrors();

        /* assert */
        $this->assertDatabaseHas('invoices', Arr::except($payload, ['invoiceItems', 'numbering_id']));
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_invoice_through_a_modal_without_required_invoice_number(): void
    {
        /* arrange */
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'customer_id'    => $customer->getKey(),
            'numbering_id'   => $documentGroup->getKey(),
            'user_id'        => $this->user->id,
            'invoice_status' => 'draft',
            'invoiced_at'    => '2025-05-10',
            'invoice_due_at' => '2025-06-09',
            'invoiceItems'   => [
                [
                    'product_id' => $product->getKey(),
                    'quantity'   => 3,
                    'price'      => 150,
                    'discount'   => 0,
                ],
            ],
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors(['invoice_number' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_invoice_through_a_modal_without_required_invoice_status(): void
    {
        /* arrange */
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'invoice_number' => 'INV-987654',
            'customer_id'    => $customer->getKey(),
            'numbering_id'   => $documentGroup->getKey(),
            'user_id'        => $this->user->id,
            'invoiced_at'    => '2025-05-10',
            'invoice_due_at' => '2025-06-09',
            'invoiceItems'   => [
                [
                    'product_id' => $product->getKey(),
                    'quantity'   => 3,
                    'price'      => 150,
                    'discount'   => 0,
                ],
            ],
        ];

        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        $component->assertHasFormErrors(['invoice_status' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_invoice_through_a_modal_without_required_customer(): void
    {
        /* arrange */
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'invoice_number' => 'INV-987654',
            'numbering_id'   => $documentGroup->getKey(),
            'user_id'        => $this->user->id,
            'invoice_status' => 'draft',
            'invoiced_at'    => '2025-05-10',
            'invoice_due_at' => '2025-06-09',
            'invoiceItems'   => [
                [
                    'product_id' => $product->getKey(),
                    'quantity'   => 3,
                    'price'      => 150,
                    'discount'   => 0,
                ],
            ],
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors(['customer_id']);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_an_invoice_through_a_modal(): void
    {
        /* arrange */
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $invoice = Invoice::factory()->for($this->company)->create([
            'invoice_number' => 'INV-987654',
            'customer_id'    => $customer->getKey(),
            'numbering_id'   => $documentGroup->getKey(),
            'user_id'        => $this->user->id,
            'invoice_status' => InvoiceStatus::DRAFT->value,
            'invoiced_at'    => '2025-05-10',
            'invoice_due_at' => '2025-06-09',
        ]);

        $payload = ['invoice_status' => InvoiceStatus::SENT];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction(TestAction::make('edit')->table($invoice), $payload)
            ->fillForm($payload)
            ->mountAction('save')
            ->callMountedAction();

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* assert */
        $this->assertDatabaseHas('invoices', [
            'id'             => $invoice->id,
            'invoice_status' => InvoiceStatus::SENT,
        ]);
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    public function it_creates_an_invoice_with_items(): void
    {
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'invoice_number' => 'INV-987654',
            'customer_id'    => $customer->getKey(),
            'numbering_id'   => $documentGroup->getKey(),
            'user_id'        => $this->user->id,
            'invoice_status' => 'draft',
            'invoiced_at'    => '2025-05-10',
            'invoice_due_at' => '2025-06-09',
            'invoiceItems'   => [
                [
                    'product_id' => $product->getKey(),
                    'quantity'   => 3,
                    'price'      => 150,
                    'discount'   => 0,
                ],
            ],
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateInvoice::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertSuccessful()
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('invoices', Arr::except($payload, ['invoiceItems', 'numbering_id']));
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_invoice_without_required_invoice_number(): void
    {
        /* arrange */
        $user            = $this->user;
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'customer_id'              => $customer->id,
            'numbering_id'             => $documentGroup->id,
            'user_id'                  => $user->id,
            'invoice_status'           => InvoiceStatus::DRAFT,
            'invoice_sign'             => '1',
            'invoiced_at'              => now()->format('Y-m-d'),
            'invoice_due_at'           => now()->addDays(30)->format('Y-m-d'),
            'invoice_discount_amount'  => 10,
            'invoice_discount_percent' => 5,
            'item_tax_total'           => 0,
            'invoice_item_subtotal'    => 450,
            'invoice_tax_total'        => 20,
            'invoice_total'            => 440,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateInvoice::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['invoice_number' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_invoice_without_required_invoice_status(): void
    {
        /* arrange */
        $user            = $this->user;
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'customer_id'              => $customer->id,
            'numbering_id'             => $documentGroup->id,
            'user_id'                  => $user->id,
            'invoice_number'           => 'INV-987654',
            'invoice_sign'             => '1',
            'invoiced_at'              => now()->format('Y-m-d'),
            'invoice_due_at'           => now()->addDays(30)->format('Y-m-d'),
            'invoice_discount_amount'  => 10,
            'invoice_discount_percent' => 5,
            'item_tax_total'           => 0,
            'invoice_item_subtotal'    => 450,
            'invoice_tax_total'        => 20,
            'invoice_total'            => 440,
        ];

        $component = Livewire::actingAs($this->user)
            ->test(CreateInvoice::class)
            ->fillForm($payload)
            ->call('create');

        $component->assertHasFormErrors(['invoice_status' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_invoice_without_required_customer(): void
    {
        /* arrange */
        $user            = $this->user;
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'numbering_id'             => $documentGroup->id,
            'user_id'                  => $user->id,
            'invoice_number'           => 'INV-987654',
            'invoice_status'           => InvoiceStatus::DRAFT,
            'invoice_sign'             => '1',
            'invoiced_at'              => now()->format('Y-m-d'),
            'invoice_due_at'           => now()->addDays(30)->format('Y-m-d'),
            'invoice_discount_amount'  => 10,
            'invoice_discount_percent' => 5,
            'item_tax_total'           => 0,
            'invoice_item_subtotal'    => 450,
            'invoice_tax_total'        => 20,
            'invoice_total'            => 440,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateInvoice::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['customer_id']);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_an_invoice(): void
    {
        /* arrange */
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $invoice = Invoice::factory()->for($this->company)->create([
            'invoice_number' => 'INV-987654',
            'customer_id'    => $customer->getKey(),
            'numbering_id'   => $documentGroup->getKey(),
            'user_id'        => $this->user->id,
            'invoice_status' => InvoiceStatus::DRAFT->value,
            'invoiced_at'    => '2025-05-10',
            'invoice_due_at' => '2025-06-09',
        ]);

        $payload = ['invoice_status' => InvoiceStatus::SENT];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id])
            ->fillForm($payload)
            ->call('save');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* assert */
        $this->assertDatabaseHas('invoices', [
            'id'             => $invoice->id,
            'invoice_status' => InvoiceStatus::SENT,
        ]);
    }

    #[Test]
    public function it_updates_invoice_and_updates_total(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $invoice = Invoice::factory()->for($this->company)->create([
            'subtotal' => 100,
            'tax'      => 20,
            'discount' => 0,
            'total'    => 120,
        ]);

        /** @payload */
        $payload = [
            'subtotal' => 200,
            'tax'      => 40,
            'discount' => 20,
            'total'    => 220,
        ];

        Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id])
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'total' => 220]);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_an_invoice(): void
    {
        /* arrange */
        $user            = $this->user;
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'customer_id'              => $customer->id,
            'numbering_id'             => $documentGroup->id,
            'user_id'                  => $user->id,
            'invoice_number'           => 'INV-987654',
            'invoice_status'           => InvoiceStatus::DRAFT,
            'invoice_sign'             => '1',
            'invoiced_at'              => now()->format('Y-m-d'),
            'invoice_due_at'           => now()->addDays(30)->format('Y-m-d'),
            'invoice_discount_amount'  => 10,
            'invoice_discount_percent' => 5,
            'item_tax_total'           => 0,
            'invoice_item_subtotal'    => 450,
            'invoice_tax_total'        => 20,
            'invoice_total'            => 440,
        ];

        $invoice = Invoice::factory()
            ->for($this->company)
            ->create($payload);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction(TestAction::make('delete')->table($invoice))
            ->callMountedAction();

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* assert */
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_paid_invoice(): void
    {
        $this->markTestIncomplete('Still can delete paid invoice');

        /* arrange */
        $user            = $this->user;
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->create();
        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'customer_id'              => $customer->id,
            'numbering_id'             => $documentGroup->id,
            'user_id'                  => $user->id,
            'invoice_number'           => 'INV-987654',
            'invoice_status'           => InvoiceStatus::PAID,
            'invoice_sign'             => '1',
            'invoiced_at'              => now()->format('Y-m-d'),
            'invoice_due_at'           => now()->addDays(30)->format('Y-m-d'),
            'invoice_discount_amount'  => 10,
            'invoice_discount_percent' => 5,
            'item_tax_total'           => 0,
            'invoice_item_subtotal'    => 450,
            'invoice_tax_total'        => 20,
            'invoice_total'            => 440,
        ];

        $invoice = Invoice::factory()
            ->for($this->company)
            ->create($payload);

        $payment = Payment::factory()->for($this->company)->create([
            'customer_id'    => $customer->id,
            'invoice_id'     => $invoice->id,
            'payment_amount' => 440,
            'paid_at'        => now(),
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction(TestAction::make('delete')->table($invoice))
            ->callMountedAction();

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_invoice_that_was_already_deleted(): void
    {
        $this->markTestIncomplete('record to deleteAction cannot be null');

        /* arrange */
        $invoice = Invoice::factory()->for($this->company)->create();
        $invoice->delete();

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction(TestAction::make('delete')->table($invoice))
            ->callMountedAction();

        /* assert */
        $component->assertHasErrors();

        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }
    # endregion

    # region multi-tenancy
    # endregion

    #region spicy
    # endregion
}
