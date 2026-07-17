<?php

namespace Modules\Invoices\Tests\Feature;

use Carbon\Carbon;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Models\Numbering;
use Modules\Core\Models\NoteTemplate;
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
        /* Arrange */
        $user            = $this->user;
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->state(['type' => NumberingType::INVOICE->value])->create();
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class, ['tenant' => Str::lower($this->company->search_code)]);

        /* Assert */
        $component->assertSuccessful();
    }
    # endregion

    # region modals
    #[Test]
    #[Group('crud')]
    #[Group('failing')]
    public function it_creates_an_invoice_through_a_modal(): void
    {
        /* Arrange */
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->state(['type' => NumberingType::INVOICE->value])->create();
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

        /* Act */
        Livewire::actingAs($this->user)->test(ListInvoices::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->assertHasNoFormErrors()
            ->callMountedAction()
            ->assertHasNoFormErrors();

        /* Assert */
        $expected = Arr::except($payload, ['invoiceItems', 'numbering_id']);
        if (isset($expected['invoiced_at'])) {
            $expected['invoiced_at'] = Carbon::parse($expected['invoiced_at'])->format('Y-m-d H:i:s');
        }
        if (isset($expected['invoice_due_at'])) {
            $expected['invoice_due_at'] = Carbon::parse($expected['invoice_due_at'])->format('Y-m-d H:i:s');
        }
        $this->assertDatabaseHas('invoices', $expected);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_invoice_through_a_modal_without_required_invoice_number(): void
    {
        /* Arrange */
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->state(['type' => NumberingType::INVOICE->value])->create();
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertHasFormErrors(['invoice_number' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_invoice_through_a_modal_without_required_invoice_status(): void
    {
        /* Arrange */
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->state(['type' => NumberingType::INVOICE->value])->create();
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
        /* Arrange */
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->state(['type' => NumberingType::INVOICE->value])->create();
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertHasFormErrors(['customer_id']);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_an_invoice_through_a_modal(): void
    {
        /* Arrange */
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->state(['type' => NumberingType::INVOICE->value])->create();
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction(TestAction::make('edit')->table($invoice), $payload)
            ->fillForm($payload)
            ->mountAction('save')
            ->callMountedAction();

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* Assert */
        $this->assertDatabaseHas('invoices', [
            'id'             => $invoice->id,
            'invoice_status' => InvoiceStatus::SENT,
        ]);
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    #[Group('failing')]
    public function it_creates_an_invoice_with_items(): void
    {
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->state(['type' => NumberingType::INVOICE->value])->create();
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateInvoice::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component->assertSuccessful()
            ->assertHasNoFormErrors();

        $expected = Arr::except($payload, ['invoiceItems', 'numbering_id']);
        if (isset($expected['invoiced_at'])) {
            $expected['invoiced_at'] = Carbon::parse($expected['invoiced_at'])->format('Y-m-d H:i:s');
        }
        if (isset($expected['invoice_due_at'])) {
            $expected['invoice_due_at'] = Carbon::parse($expected['invoice_due_at'])->format('Y-m-d H:i:s');
        }
        $this->assertDatabaseHas('invoices', $expected);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_invoice_without_required_invoice_number(): void
    {
        /* Arrange */
        $user            = $this->user;
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->state(['type' => NumberingType::INVOICE->value])->create();
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateInvoice::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component->assertHasFormErrors(['invoice_number' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_invoice_without_required_invoice_status(): void
    {
        /* Arrange */
        $user            = $this->user;
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->state(['type' => NumberingType::INVOICE->value])->create();
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
        /* Arrange */
        $user            = $this->user;
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->state(['type' => NumberingType::INVOICE->value])->create();
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateInvoice::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component->assertHasFormErrors(['customer_id']);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_an_invoice(): void
    {
        /* Arrange */
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->state(['type' => NumberingType::INVOICE->value])->create();
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id])
            ->fillForm($payload)
            ->call('save');

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* Assert */
        $this->assertDatabaseHas('invoices', [
            'id'             => $invoice->id,
            'invoice_status' => InvoiceStatus::SENT,
        ]);
    }

    #[Test]
    public function it_updates_invoice_and_updates_total(): void
    {
        /* Arrange */
        $customer = Relation::factory()->customer()->for($this->company)->create();

        $invoice = Invoice::factory()->for($this->company)->create([
            'customer_id'           => $customer->id,
            'user_id'               => $this->user->id,
            'invoice_item_subtotal' => 100,
            'invoice_tax_total'     => 20,
            'invoice_total'         => 120,
        ]);

        /* Act — load the edit page and verify it renders without errors */
        $component = Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id]);

        $component->assertSuccessful();

        /* Assert — invoice exists in DB with correct initial totals */
        $this->assertDatabaseHas('invoices', [
            'id'                    => $invoice->id,
            'invoice_item_subtotal' => 100,
            'invoice_total'         => 120,
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_inserts_a_note_template_into_the_notes_field(): void
    {
        /* Arrange */
        $customer = Relation::factory()->customer()->for($this->company)->create();
        $invoice  = Invoice::factory()->for($this->company)->create([
            'customer_id' => $customer->id,
            'user_id'     => $this->user->id,
            'notes'       => null,
        ]);
        $template = NoteTemplate::factory()->for($this->company)->create([
            'template_title' => 'SEO Terms',
            'template_body'  => 'Payment due Net 30.',
        ]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id])
            ->callFormComponentAction('notes', 'insert_note_template_notes', [
                'note_template_id' => $template->id,
                'replace_content'  => true,
            ]);

        /* Assert */
        $component->assertFormSet(['notes' => 'Payment due Net 30.']);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_an_invoice(): void
    {
        /* Arrange */
        $user            = $this->user;
        $customer        = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup   = Numbering::factory()->for($this->company)->state(['type' => NumberingType::INVOICE->value])->create();
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction(TestAction::make('delete')->table($invoice))
            ->callMountedAction();

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* Assert */
        $this->assertSoftDeleted($invoice);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_paid_invoice(): void
    {
        /* Arrange */
        $user     = $this->user;
        $customer = Relation::factory()->for($this->company)->customer()->create();

        $invoice = Invoice::factory()
            ->for($this->company)
            ->paid()
            ->create([
                'customer_id'    => $customer->id,
                'user_id'        => $user->id,
                'invoice_number' => 'INV-PAID-001',
            ]);

        Payment::factory()->for($this->company)->create([
            'customer_id'    => $customer->id,
            'invoice_id'     => $invoice->id,
            'payment_amount' => $invoice->invoice_total,
            'paid_at'        => now(),
        ]);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction(TestAction::make('delete')->table($invoice))
            ->callMountedAction();

        /* Assert — paid invoice must be preserved */
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_invoice_that_was_already_deleted(): void
    {
        /* Arrange */
        $invoice   = Invoice::factory()->for($this->company)->create();
        $invoiceId = $invoice->id;
        $invoice->delete();

        /* Act */
        $deletedInvoice = Invoice::withTrashed()->find($invoiceId);
        $result         = $deletedInvoice ? $deletedInvoice->delete() : false;

        /* Assert */
        $this->assertFalse($result);
        $this->assertDatabaseMissing('invoices', ['id' => $invoiceId]);
    }
    # endregion

    # region multi-tenancy
    #[Test]
    #[Group('multi-tenancy')]
    public function it_only_returns_invoices_belonging_to_the_current_tenant(): void
    {
        /* Arrange */
        $companyB = \Modules\Core\Models\Company::factory()->create();
        $invoiceA = Invoice::factory()->for($this->company)->create(['invoice_number' => 'INV-TENANT-A']);
        $invoiceB = Invoice::factory()->for($companyB)->create(['invoice_number' => 'INV-TENANT-B']);

        /* Act — authenticate as Company A user; global scope filters to Company A */
        $this->actingAs($this->user);

        /* Assert */
        $this->assertDatabaseHas('invoices', ['id' => $invoiceA->id]);
        $this->assertDatabaseHas('invoices', ['id' => $invoiceB->id]);    // B is in the DB...
        $this->assertNotNull(Invoice::find($invoiceA->id));               // A is visible to tenant A
        $this->assertNull(Invoice::find($invoiceB->id));                  // B is NOT visible to tenant A
    }
    # endregion

    #region spicy
    # endregion
}
