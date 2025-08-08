<?php

namespace Modules\Invoices\Tests\Feature;

use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\DocumentGroup;
use Modules\Core\Models\TaxRate;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\CreateInvoice;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\EditInvoice;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\ListInvoices;
use Modules\Invoices\Models\Invoice;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Models\ProductUnit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListInvoices::class)]
class InvoicesTest extends AbstractCompanyPanelTestCase
{
    protected User $user;

    # region smoke
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['invoice_date' => '2024-11-01', 'invoice_number' => 'INV-0001']
     */
    public function it_lists_invoices(): void
    {
        /* arrange */
        $company         = $this->user->companies()->first();
        $user            = $this->user;
        $customer        = Relation::factory()->for($company)->customer()->create();
        $documentGroup   = DocumentGroup::factory()->for($company)->create();
        $taxRate         = TaxRate::factory()->for($company)->create();
        $productCategory = ProductCategory::factory()->for($company)->create();
        $productUnit     = ProductUnit::factory()->for($company)->create();
        $product         = Product::factory()->for($company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'customer_id'              => $customer->id,
            'document_group_id'        => $documentGroup->id,
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
            ->for($company)
            ->create($payload);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class, ['tenant' => Str::lower($this->user->companies()->first()->search_code)]);

        /* assert */
        $component->assertSuccessful();
    }
    # endregion

    # region modals
    #[Test]
    #[Group('crud')]
    public function it_creates_an_invoice_with_items_through_a_modal(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company         = $this->user->companies()->first();
        $user            = $this->user;
        $customer        = Relation::factory()->for($company)->customer()->create();
        $documentGroup   = DocumentGroup::factory()->for($company)->create();
        $taxRate         = TaxRate::factory()->for($company)->create();
        $productCategory = ProductCategory::factory()->for($company)->create();
        $productUnit     = ProductUnit::factory()->for($company)->create();
        $product         = Product::factory()->for($company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'invoice_number'           => 'INV-987654',
            'invoice_status'           => InvoiceStatus::DRAFT,
            'invoice_sign'             => '1',
            'invoiced_at'              => now()->format('Y-m-d'),
            'invoice_due_at'           => now()->addDays(30)->format('Y-m-d'),
            'invoice_discount_amount'  => 10,
            'invoice_discount_percent' => 5,
            'invoice_item_subtotal'    => 450,
            'item_tax_total'           => 90,
            'invoice_tax_total'        => 20,
            'invoice_total'            => 440,
            'customer_id'              => $customer->id,
            'user_id'                  => $user->id,
            'document_group_id'        => $documentGroup->id,
            'invoiceItems'             => [
                [
                    'item_name' => 'Design Consultation',
                    'quantity'  => 3,
                    'price'     => 150,
                    'discount'  => 0,
                    'subtotal'  => 450,
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
        $component->assertSuccessful()
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('invoices', [
            'invoice_number' => $payload['invoice_number'],
            'invoice_total'  => $payload['invoice_total'],
        ]);

        $this->assertDatabaseHas('invoice_items', [
            'item_name' => 'Design Consultation',
            'price'     => 150,
            'quantity'  => 3,
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_invoice_through_a_modal_without_required_invoice_number(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company         = $this->user->companies()->first();
        $user            = $this->user;
        $customer        = Relation::factory()->for($company)->customer()->create();
        $documentGroup   = DocumentGroup::factory()->for($company)->create();
        $taxRate         = TaxRate::factory()->for($company)->create();
        $productCategory = ProductCategory::factory()->for($company)->create();
        $productUnit     = ProductUnit::factory()->for($company)->create();
        $product         = Product::factory()->for($company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'customer_id'              => $customer->id,
            'document_group_id'        => $documentGroup->id,
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
        $this->markTestIncomplete();

        /* arrange */
        $company         = $this->user->companies()->first();
        $user            = $this->user;
        $customer        = Relation::factory()->for($company)->customer()->create();
        $documentGroup   = DocumentGroup::factory()->for($company)->create();
        $taxRate         = TaxRate::factory()->for($company)->create();
        $productCategory = ProductCategory::factory()->for($company)->create();
        $productUnit     = ProductUnit::factory()->for($company)->create();
        $product         = Product::factory()->for($company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'customer_id'              => $customer->id,
            'document_group_id'        => $documentGroup->id,
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
            ->test(ListInvoices::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        $component->assertHasFormErrors(['invoice_status' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_invoice_through_a_modal_without_required_invoice_sign(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company         = $this->user->companies()->first();
        $user            = $this->user;
        $customer        = Relation::factory()->for($company)->customer()->create();
        $documentGroup   = DocumentGroup::factory()->for($company)->create();
        $taxRate         = TaxRate::factory()->for($company)->create();
        $productCategory = ProductCategory::factory()->for($company)->create();
        $productUnit     = ProductUnit::factory()->for($company)->create();
        $product         = Product::factory()->for($company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'customer_id'              => $customer->id,
            'document_group_id'        => $documentGroup->id,
            'user_id'                  => $user->id,
            'invoice_number'           => 'INV-987654',
            'invoice_status'           => InvoiceStatus::DRAFT,
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
            ->test(ListInvoices::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        $component->assertHasFormErrors(['invoice_sign' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_invoice_through_a_modal_without_required_invoice_item_subtotal(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company         = $this->user->companies()->first();
        $user            = $this->user;
        $customer        = Relation::factory()->for($company)->customer()->create();
        $documentGroup   = DocumentGroup::factory()->for($company)->create();
        $taxRate         = TaxRate::factory()->for($company)->create();
        $productCategory = ProductCategory::factory()->for($company)->create();
        $productUnit     = ProductUnit::factory()->for($company)->create();
        $product         = Product::factory()->for($company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'customer_id'              => $customer->id,
            'document_group_id'        => $documentGroup->id,
            'user_id'                  => $user->id,
            'invoice_number'           => 'INV-987654',
            'invoice_status'           => InvoiceStatus::DRAFT,
            'invoice_sign'             => '1',
            'invoiced_at'              => now()->format('Y-m-d'),
            'invoice_due_at'           => now()->addDays(30)->format('Y-m-d'),
            'invoice_discount_amount'  => 10,
            'invoice_discount_percent' => 5,
            'item_tax_total'           => 0,
            'invoice_tax_total'        => 20,
            'invoice_total'            => 440,
        ];

        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        $component->assertHasFormErrors(['invoice_item_subtotal' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_invoice_through_a_modal_without_required_invoice_tax_total(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company         = $this->user->companies()->first();
        $user            = $this->user;
        $customer        = Relation::factory()->for($company)->customer()->create();
        $documentGroup   = DocumentGroup::factory()->for($company)->create();
        $taxRate         = TaxRate::factory()->for($company)->create();
        $productCategory = ProductCategory::factory()->for($company)->create();
        $productUnit     = ProductUnit::factory()->for($company)->create();
        $product         = Product::factory()->for($company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'customer_id'              => $customer->id,
            'document_group_id'        => $documentGroup->id,
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
            'invoice_total'            => 440,
        ];

        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        $component->assertHasFormErrors(['invoice_tax_total' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_invoice_through_a_modal_without_required_invoice_total(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company         = $this->user->companies()->first();
        $user            = $this->user;
        $customer        = Relation::factory()->for($company)->customer()->create();
        $documentGroup   = DocumentGroup::factory()->for($company)->create();
        $taxRate         = TaxRate::factory()->for($company)->create();
        $productCategory = ProductCategory::factory()->for($company)->create();
        $productUnit     = ProductUnit::factory()->for($company)->create();
        $product         = Product::factory()->for($company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'customer_id'              => $customer->id,
            'document_group_id'        => $documentGroup->id,
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
        ];

        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        $component->assertHasFormErrors(['invoice_total' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_invoice_through_a_modal_without_customer(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company         = $this->user->companies()->first();
        $user            = $this->user;
        $customer        = Relation::factory()->for($company)->customer()->create();
        $documentGroup   = DocumentGroup::factory()->for($company)->create();
        $taxRate         = TaxRate::factory()->for($company)->create();
        $productCategory = ProductCategory::factory()->for($company)->create();
        $productUnit     = ProductUnit::factory()->for($company)->create();
        $product         = Product::factory()->for($company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'document_group_id'        => $documentGroup->id,
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
            ->test(ListInvoices::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors(['customer_id']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_invoice_through_a_modal_without_required_document_group(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company         = $this->user->companies()->first();
        $user            = $this->user;
        $customer        = Relation::factory()->for($company)->customer()->create();
        $documentGroup   = DocumentGroup::factory()->for($company)->create();
        $taxRate         = TaxRate::factory()->for($company)->create();
        $productCategory = ProductCategory::factory()->for($company)->create();
        $productUnit     = ProductUnit::factory()->for($company)->create();
        $product         = Product::factory()->for($company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'customer_id'              => $customer->id,
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
            ->test(ListInvoices::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors(['document_group']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_invoice_through_a_modal_without_items(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company         = $this->user->companies()->first();
        $user            = $this->user;
        $customer        = Relation::factory()->for($company)->customer()->create();
        $documentGroup   = DocumentGroup::factory()->for($company)->create();
        $productCategory = ProductCategory::factory()->for($company)->create();
        $productUnit     = ProductUnit::factory()->for($company)->create();
        $product         = Product::factory()->for($company)->create([
            'category_id' => $productCategory->id,
            'unit_id'     => $productUnit->id,
        ]);

        $payload = [
            'invoice_number'           => 'INV-987654',
            'invoice_status'           => InvoiceStatus::DRAFT,
            'invoice_sign'             => '1',
            'invoiced_at'              => now()->format('Y-m-d'),
            'invoice_due_at'           => now()->addDays(30)->format('Y-m-d'),
            'invoice_discount_amount'  => 10,
            'invoice_discount_percent' => 5,
            'invoice_item_subtotal'    => 450,
            'invoice_tax_total'        => 20,
            'invoice_total'            => 440,
            'customer_id'              => $customer->id,
            'user_id'                  => $user->id,
            'document_group_id'        => $documentGroup->id,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        $component->assertHasErrors(['invoice_items']);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_an_invoice_through_a_modal(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $invoice = Invoice::factory()->for($this->user->companies()->first())->create([
            'status' => InvoiceStatus::DRAFT,
        ]);

        $payload = ['status' => InvoiceStatus::SENT];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction('edit', ['record' => $invoice->id])
            ->fillForm($payload)
            ->mountAction('save')
            ->callMountedAction();

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* assert */
        $this->assertDatabaseHas('invoices', [
            'id'     => $invoice->id,
            'status' => InvoiceStatus::SENT,
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_edits_invoice_and_updates_total_through_a_modal(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $invoice = Invoice::factory()->for($this->user->companies()->first())->create([
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
            ->test(ListInvoices::class)
            ->mountAction('edit', ['record' => $invoice->id])
            ->fillForm($payload)
            ->mountAction('save')
            ->callMountedAction()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'total' => 220]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_update_with_invalid_discount_through_a_modal(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $invoice = Invoice::factory()->for($this->user->companies()->first())->create([
            'subtotal' => 200,
            'tax'      => 40,
            'discount' => 10,
            'total'    => 230,
        ]);

        /** @payload */
        $payload = [
            'subtotal' => 200,
            'tax'      => 40,
            'discount' => 9999, // absurd value
            'total'    => 230,
        ];

        Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction('edit', ['record' => $invoice->id])
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasErrors(['discount']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_update_invoice_with_invalid_status_through_a_modal(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $invoice = Invoice::factory()->for($this->user->companies()->first())->create();
        $payload = ['status' => null];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction('edit', ['record' => $invoice->id])
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors(['status']);
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    public function it_creates_an_invoice_with_items(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company         = $this->user->companies()->first();
        $user            = $this->user;
        $customer        = Relation::factory()->for($company)->customer()->create();
        $documentGroup   = DocumentGroup::factory()->for($company)->create();
        $productCategory = ProductCategory::factory()->for($company)->create();
        $productUnit     = ProductUnit::factory()->for($company)->create();
        $product         = Product::factory()->for($company)->create([
            'category_id' => $productCategory->id,
            'unit_id'     => $productUnit->id,
        ]);

        $payload = [
            'invoice_number'           => 'INV-987654',
            'invoice_status'           => InvoiceStatus::DRAFT,
            'invoice_sign'             => '1',
            'invoiced_at'              => now()->format('Y-m-d'),
            'invoice_due_at'           => now()->addDays(30)->format('Y-m-d'),
            'invoice_discount_amount'  => 10,
            'invoice_discount_percent' => 5,
            'invoice_item_subtotal'    => 450,
            'item_tax_total'           => 90,
            'invoice_tax_total'        => 20,
            'invoice_total'            => 440,
            'customer_id'              => $customer->id,
            'user_id'                  => $user->id,
            'document_group_id'        => $documentGroup->id,
            'invoiceItems'             => [
                [
                    'item_name' => 'Design Consultation',
                    'quantity'  => 3,
                    'price'     => 150,
                    'discount'  => 0,
                    'subtotal'  => 450,
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

        $this->assertDatabaseHas('invoices', [
            'invoice_number' => $payload['invoice_number'],
            'invoice_total'  => $payload['invoice_total'],
        ]);

        $this->assertDatabaseHas('invoice_items', [
            'item_name' => 'Design Consultation',
            'price'     => 150,
            'quantity'  => 3,
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_invoice_without_required_invoice_number(): void
    {
        /* arrange */
        $company         = $this->user->companies()->first();
        $user            = $this->user;
        $customer        = Relation::factory()->for($company)->customer()->create();
        $documentGroup   = DocumentGroup::factory()->for($company)->create();
        $taxRate         = TaxRate::factory()->for($company)->create();
        $productCategory = ProductCategory::factory()->for($company)->create();
        $productUnit     = ProductUnit::factory()->for($company)->create();
        $product         = Product::factory()->for($company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'customer_id'              => $customer->id,
            'document_group_id'        => $documentGroup->id,
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
        $company         = $this->user->companies()->first();
        $user            = $this->user;
        $customer        = Relation::factory()->for($company)->customer()->create();
        $documentGroup   = DocumentGroup::factory()->for($company)->create();
        $taxRate         = TaxRate::factory()->for($company)->create();
        $productCategory = ProductCategory::factory()->for($company)->create();
        $productUnit     = ProductUnit::factory()->for($company)->create();
        $product         = Product::factory()->for($company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'customer_id'              => $customer->id,
            'document_group_id'        => $documentGroup->id,
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
    public function it_fails_to_create_invoice_without_required_invoice_sign(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company       = $this->user->companies()->first();
        $user          = $this->user;
        $customer      = Relation::factory()->for($company)->customer()->create();
        $documentGroup = DocumentGroup::factory()->for($company)->create();
        $product       = Product::factory()->for($company)->create();

        $payload = [
            'customer_id'              => $customer->id,
            'document_group_id'        => $documentGroup->id,
            'user_id'                  => $user->id,
            'invoice_number'           => 'INV-987654',
            'invoice_status'           => InvoiceStatus::DRAFT,
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

        $component->assertHasFormErrors(['invoice_sign' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_invoice_without_required_invoice_item_subtotal(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company       = $this->user->companies()->first();
        $user          = $this->user;
        $customer      = Relation::factory()->for($company)->customer()->create();
        $documentGroup = DocumentGroup::factory()->for($company)->create();
        $product       = Product::factory()->for($company)->create();

        $payload = [
            'customer_id'              => $customer->id,
            'document_group_id'        => $documentGroup->id,
            'user_id'                  => $user->id,
            'invoice_number'           => 'INV-987654',
            'invoice_status'           => InvoiceStatus::DRAFT,
            'invoice_sign'             => '1',
            'invoiced_at'              => now()->format('Y-m-d'),
            'invoice_due_at'           => now()->addDays(30)->format('Y-m-d'),
            'invoice_discount_amount'  => 10,
            'invoice_discount_percent' => 5,
            'item_tax_total'           => 0,
            'invoice_tax_total'        => 20,
            'invoice_total'            => 440,
        ];

        $component = Livewire::actingAs($this->user)
            ->test(CreateInvoice::class)
            ->fillForm($payload)
            ->call('create');

        $component->assertHasFormErrors(['invoice_item_subtotal' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_invoice_without_required_invoice_tax_total(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company       = $this->user->companies()->first();
        $user          = $this->user;
        $customer      = Relation::factory()->for($company)->customer()->create();
        $documentGroup = DocumentGroup::factory()->for($company)->create();
        $product       = Product::factory()->for($company)->create();

        $payload = [
            'customer_id'              => $customer->id,
            'document_group_id'        => $documentGroup->id,
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
            'invoice_total'            => 440,
        ];

        $component = Livewire::actingAs($this->user)
            ->test(CreateInvoice::class)
            ->fillForm($payload)
            ->call('create');

        $component->assertHasFormErrors(['invoice_tax_total' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_invoice_without_required_invoice_total(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company       = $this->user->companies()->first();
        $user          = $this->user;
        $customer      = Relation::factory()->for($company)->customer()->create();
        $documentGroup = DocumentGroup::factory()->for($company)->create();
        $product       = Product::factory()->for($company)->create();

        $payload = [
            'customer_id'              => $customer->id,
            'document_group_id'        => $documentGroup->id,
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
        ];

        $component = Livewire::actingAs($this->user)
            ->test(CreateInvoice::class)
            ->fillForm($payload)
            ->call('create');

        $component->assertHasFormErrors(['invoice_total' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_invoice_without_customer(): void
    {
        /* arrange */
        $company         = $this->user->companies()->first();
        $user            = $this->user;
        $customer        = Relation::factory()->for($company)->customer()->create();
        $documentGroup   = DocumentGroup::factory()->for($company)->create();
        $taxRate         = TaxRate::factory()->for($company)->create();
        $productCategory = ProductCategory::factory()->for($company)->create();
        $productUnit     = ProductUnit::factory()->for($company)->create();
        $product         = Product::factory()->for($company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'document_group_id'        => $documentGroup->id,
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
    public function it_fails_to_create_invoice_without_required_document_group(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company       = $this->user->companies()->first();
        $user          = $this->user;
        $customer      = Relation::factory()->for($company)->customer()->create();
        $documentGroup = DocumentGroup::factory()->for($company)->create();
        $product       = Product::factory()->for($company)->create();

        $payload = [
            'customer_id'              => $customer->id,
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
        $component->assertHasFormErrors(['document_group']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_invoice_without_items(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company       = $this->user->companies()->first();
        $user          = $this->user;
        $customer      = Relation::factory()->for($company)->customer()->create();
        $documentGroup = DocumentGroup::factory()->for($company)->create();
        $product       = Product::factory()->for($company)->create();

        $payload = [
            'invoice_number'           => 'INV-987654',
            'invoice_status'           => InvoiceStatus::DRAFT,
            'invoice_sign'             => '1',
            'invoiced_at'              => now()->format('Y-m-d'),
            'invoice_due_at'           => now()->addDays(30)->format('Y-m-d'),
            'invoice_discount_amount'  => 10,
            'invoice_discount_percent' => 5,
            'invoice_item_subtotal'    => 450,
            'invoice_tax_total'        => 20,
            'invoice_total'            => 440,
            'customer_id'              => $customer->id,
            'user_id'                  => $user->id,
            'document_group_id'        => $documentGroup->id,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateInvoice::class)
            ->fillForm($payload)
            ->call('create');

        $component->assertHasErrors(['invoice_items']);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_an_invoice(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $invoice = Invoice::factory()->for($this->user->companies()->first())->create([
            'status' => InvoiceStatus::DRAFT,
        ]);

        $payload = ['status' => InvoiceStatus::SENT];

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
            'id'     => $invoice->id,
            'status' => InvoiceStatus::SENT,
        ]);
    }

    #[Test]
    public function it_edits_invoice_and_updates_total(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $invoice = Invoice::factory()->for($this->user->companies()->first())->create([
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
    public function it_fails_to_update_with_invalid_discount(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $invoice = Invoice::factory()->for($this->user->companies()->first())->create([
            'subtotal' => 200,
            'tax'      => 40,
            'discount' => 10,
            'total'    => 230,
        ]);

        /** @payload */
        $payload = [
            'subtotal' => 200,
            'tax'      => 40,
            'discount' => 9999, // absurd value
            'total'    => 230,
        ];

        Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id])
            ->fillForm($payload)
            ->call('save')
            ->assertHasErrors(['discount']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_update_invoice_with_invalid_status(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $invoice = Invoice::factory()->for($this->user->companies()->first())->create();
        $payload = ['status' => null];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id])
            ->fillForm($payload)
            ->call('save');

        /* assert */
        $component->assertHasFormErrors(['status']);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_an_invoice(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $invoice = Invoice::factory()->for($this->user->companies()->first())->create();

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->callAction('delete', $invoice);

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
        $this->markTestIncomplete();

        /* arrange */

        $invoice = Invoice::factory()
            ->for($this->user->companies()->first())
            ->hasPayments(1)
            ->create([
                'status' => InvoiceStatus::PAID,
            ]);

        Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->call('delete', $invoice->id)
            ->assertHasErrors(['delete']);

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_if_has_payments(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $invoice = Invoice::factory()
            ->for($this->user->companies()->first())
            ->hasPayments(1)
            ->create();

        Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->call('delete', $invoice->id)
            ->assertHasErrors(['delete']);

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_invoice_that_was_already_deleted(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $invoice = Invoice::factory()->for($this->user->companies()->first())->create();
        $invoice->delete();

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->callAction('delete', $invoice);

        /* assert */
        $component->assertHasErrors();

        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }
    # endregion

    # region multi-tenancy
    #[Test]
    #[Group('multi-tenancy')]
    public function it_cannot_access_invoices_of_another_tenant(): void
    {
        $this->markTestIncomplete('Should assert forbidden/404 when accessing another tenant\'s invoice.');
    }

    #[Test]
    #[Group('multi-tenancy')]
    public function widget_shows_only_current_tenant_invoices(): void
    {
        $this->markTestIncomplete('Should assert widget only shows invoices for the current tenant.');
    }
    # endregion

    #region spicy
    # endregion
}
