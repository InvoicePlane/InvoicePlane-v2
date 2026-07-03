<?php

namespace Modules\Invoices\Tests\Feature;

use Carbon\Carbon;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Clients\Enums\CommunicationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Models\Numbering;
use Modules\Core\Models\Setting;
use Modules\Core\Models\TaxRate;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\CreateInvoice;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\EditInvoice;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\ListInvoices;
use Modules\Invoices\Mail\InvoiceMailable;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Models\Payment;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Models\ProductUnit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\PermissionRegistrar;

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
        // Draft-number auto-generation is disabled so the still-required
        // invoice_number field isn't silently auto-filled, keeping this test
        // a genuine check of the required rule (see InvoiceForm's generator wiring).
        Setting::saveByKey('generate_invoice_number_for_draft', '0');

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
    #[Group('slow')]
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
        // Draft-number auto-generation is disabled so the still-required
        // invoice_number field isn't silently auto-filled, keeping this test
        // a genuine check of the required rule (see InvoiceForm's generator wiring).
        Setting::saveByKey('generate_invoice_number_for_draft', '0');

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
    #[Group('slow')]
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

    # region numbering-group
    #[Test]
    #[Group('crud')]
    public function it_moves_an_invoice_to_a_different_numbering_group(): void
    {
        /* Arrange */
        $this->grantPermission(Permission::VIEW_INVOICES, Permission::EDIT_INVOICES);

        $customer = Relation::factory()->for($this->company)->customer()->create();
        $groupA   = Numbering::factory()->for($this->company)->create(['type' => NumberingType::INVOICE->value]);
        $groupB   = Numbering::factory()->for($this->company)->create(['type' => NumberingType::INVOICE->value]);

        $invoice = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $customer->getKey(),
            'numbering_id'   => $groupA->id,
            'invoice_number' => 'INV-2026-001',
            'user_id'        => $this->user->id,
        ]);

        $payload = ['numbering_id' => $groupB->id];

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

        $this->assertDatabaseHas('invoices', [
            'id'             => $invoice->id,
            'numbering_id'   => $groupB->id,
            'invoice_number' => 'INV-2026-001', // number unchanged
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_rejects_a_numbering_group_that_does_not_belong_to_the_current_company(): void
    {
        /* Arrange */
        $this->grantPermission(Permission::VIEW_INVOICES, Permission::EDIT_INVOICES);

        $customer         = Relation::factory()->for($this->company)->customer()->create();
        $ownGroup         = Numbering::factory()->for($this->company)->create(['type' => NumberingType::INVOICE->value]);
        $otherCompany     = Company::factory()->create();
        $foreignNumbering = Numbering::factory()->for($otherCompany)->create(['type' => NumberingType::INVOICE->value]);

        $invoice = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $customer->getKey(),
            'numbering_id'   => $ownGroup->id,
            'invoice_number' => 'INV-2026-002',
            'user_id'        => $this->user->id,
        ]);

        $payload = ['numbering_id' => $foreignNumbering->id];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction(TestAction::make('edit')->table($invoice), $payload)
            ->fillForm($payload)
            ->mountAction('save')
            ->callMountedAction();

        /* Assert — a numbering group belonging to another company is not a valid option */
        $component->assertHasFormErrors(['numbering_id']);

        $this->assertDatabaseHas('invoices', [
            'id'           => $invoice->id,
            'numbering_id' => $ownGroup->id,
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_rejects_a_numbering_group_of_a_different_type(): void
    {
        /* Arrange */
        $this->grantPermission(Permission::VIEW_INVOICES, Permission::EDIT_INVOICES);

        $customer     = Relation::factory()->for($this->company)->customer()->create();
        $invoiceGroup = Numbering::factory()->for($this->company)->create(['type' => NumberingType::INVOICE->value]);
        $quoteGroup   = Numbering::factory()->for($this->company)->create(['type' => NumberingType::QUOTE->value]);

        $invoice = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $customer->getKey(),
            'numbering_id'   => $invoiceGroup->id,
            'invoice_number' => 'INV-2026-003',
            'user_id'        => $this->user->id,
        ]);

        $payload = ['numbering_id' => $quoteGroup->id];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction(TestAction::make('edit')->table($invoice), $payload)
            ->fillForm($payload)
            ->mountAction('save')
            ->callMountedAction();

        /* Assert — a Quote numbering group must never be selectable for an Invoice */
        $component->assertHasFormErrors(['numbering_id']);
    }
    # endregion

    # region email
    #[Test]
    #[Group('crud')]
    public function it_dispatches_a_queued_mail_when_send_email_action_is_called(): void
    {
        /* Arrange */
        Mail::fake();
        $this->grantPermission(Permission::VIEW_INVOICES, Permission::EMAIL_INVOICES);

        $relation = Relation::factory()->for($this->company)->customer()->create();
        $contact  = $relation->contacts()->create([
            'company_id' => $this->company->id,
            'first_name' => 'Jane',
            'last_name'  => 'Doe',
        ]);
        $contact->communications()->create([
            'company_id'          => $this->company->id,
            'is_primary'          => true,
            'communication_type'  => CommunicationType::EMAIL->value,
            'communication_value' => 'customer@example.com',
        ]);

        $invoice = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $relation->getKey(),
            'invoice_number' => 'INV-EMAIL-001',
            'invoice_status' => InvoiceStatus::DRAFT->value,
            'user_id'        => $this->user->id,
        ]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction(TestAction::make('email_invoice')->table($invoice))
            ->callMountedAction();

        /* Assert */
        $component->assertSuccessful()->assertHasNoErrors();

        Mail::assertQueued(
            InvoiceMailable::class,
            fn ($mail) => $mail->hasTo('customer@example.com') && $mail->invoice->is($invoice)
        );
    }

    #[Test]
    #[Group('crud')]
    public function it_uses_the_invoice_sent_email_template_when_one_exists_for_the_company(): void
    {
        /* Arrange */
        Mail::fake();
        $this->grantPermission(Permission::VIEW_INVOICES, Permission::EMAIL_INVOICES);

        $relation = Relation::factory()->for($this->company)->customer()->create(['company_name' => 'Acme Corp']);
        $contact  = $relation->contacts()->create([
            'company_id' => $this->company->id,
            'first_name' => 'Jane',
            'last_name'  => 'Doe',
        ]);
        $contact->communications()->create([
            'company_id'          => $this->company->id,
            'is_primary'          => true,
            'communication_type'  => CommunicationType::EMAIL->value,
            'communication_value' => 'billing@example.com',
        ]);

        /*
         * Every company is auto-bootstrapped with an "invoice_sent" EmailTemplate
         * (see CompanyObserver::created()), so update it rather than creating a
         * second row with the same title.
         */
        EmailTemplate::forCompany($this->company->id)
            ->where('title', 'invoice_sent')
            ->update([
                'subject' => 'Invoice {{ invoice.number }} from {{ company.name }}',
                'body'    => 'Hello {{ customer.name }}, your invoice {{ invoice.number }} is ready.',
            ]);

        $invoice = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $relation->getKey(),
            'invoice_number' => 'INV-EMAIL-002',
            'invoice_status' => InvoiceStatus::DRAFT->value,
            'user_id'        => $this->user->id,
        ]);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction(TestAction::make('email_invoice')->table($invoice))
            ->callMountedAction();

        /* Assert */
        Mail::assertQueued(
            InvoiceMailable::class,
            fn ($mail) => $mail->hasTo('billing@example.com')
                && str_contains($mail->emailSubject, 'INV-EMAIL-002')
                && str_contains($mail->bodyText, 'Acme Corp')
        );
    }

    #[Test]
    #[Group('crud')]
    public function it_shows_an_error_notification_when_the_customer_has_no_email_on_file(): void
    {
        /* Arrange */
        Mail::fake();
        $this->grantPermission(Permission::VIEW_INVOICES, Permission::EMAIL_INVOICES);

        $relation = Relation::factory()->for($this->company)->customer()->create();

        $invoice = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $relation->getKey(),
            'invoice_number' => 'INV-EMAIL-003',
            'invoice_status' => InvoiceStatus::DRAFT->value,
            'user_id'        => $this->user->id,
        ]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction(TestAction::make('email_invoice')->table($invoice))
            ->callMountedAction();

        /* Assert */
        $component->assertSuccessful();
        Mail::assertNothingQueued();
        Mail::assertNothingSent();
    }

    #[Test]
    #[Group('crud')]
    public function it_ccs_the_customers_stored_cc_emails_on_the_invoice_mail(): void
    {
        /* Arrange */
        Mail::fake();
        $this->grantPermission(Permission::VIEW_INVOICES, Permission::EMAIL_INVOICES);

        $relation = Relation::factory()->for($this->company)->customer()->create();
        $contact  = $relation->contacts()->create([
            'company_id' => $this->company->id,
            'first_name' => 'Jane',
            'last_name'  => 'Doe',
        ]);
        $contact->communications()->create([
            'company_id'          => $this->company->id,
            'is_primary'          => true,
            'communication_type'  => CommunicationType::EMAIL->value,
            'communication_value' => 'customer@example.com',
        ]);
        $relation->communications()->createMany([
            [
                'company_id'          => $this->company->id,
                'communication_type'  => CommunicationType::INVOICE_CC->value,
                'communication_value' => 'cc1@example.com',
                'is_primary'          => false,
            ],
            [
                'company_id'          => $this->company->id,
                'communication_type'  => CommunicationType::INVOICE_CC->value,
                'communication_value' => 'cc2@example.com',
                'is_primary'          => false,
            ],
        ]);

        $invoice = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $relation->getKey(),
            'invoice_number' => 'INV-EMAIL-004',
            'invoice_status' => InvoiceStatus::DRAFT->value,
            'user_id'        => $this->user->id,
        ]);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction(TestAction::make('email_invoice')->table($invoice))
            ->callMountedAction();

        /* Assert */
        Mail::assertQueued(
            InvoiceMailable::class,
            fn ($mail) => $mail->hasCc('cc1@example.com') && $mail->hasCc('cc2@example.com')
        );
    }

    #[Test]
    #[Group('crud')]
    public function it_merges_and_deduplicates_client_and_template_cc_emails(): void
    {
        /* Arrange */
        Mail::fake();
        $this->grantPermission(Permission::VIEW_INVOICES, Permission::EMAIL_INVOICES);

        $relation = Relation::factory()->for($this->company)->customer()->create();
        $contact  = $relation->contacts()->create([
            'company_id' => $this->company->id,
            'first_name' => 'Jane',
            'last_name'  => 'Doe',
        ]);
        $contact->communications()->create([
            'company_id'          => $this->company->id,
            'is_primary'          => true,
            'communication_type'  => CommunicationType::EMAIL->value,
            'communication_value' => 'customer@example.com',
        ]);
        $relation->communications()->create([
            'company_id'          => $this->company->id,
            'communication_type'  => CommunicationType::INVOICE_CC->value,
            'communication_value' => 'shared@example.com',
            'is_primary'          => false,
        ]);

        /*
         * Every company is auto-bootstrapped with an "invoice_sent" EmailTemplate
         * (see CompanyObserver::created()), so update it rather than creating a
         * second row with the same title.
         */
        EmailTemplate::forCompany($this->company->id)
            ->where('title', 'invoice_sent')
            ->update(['cc' => 'shared@example.com, template@example.com']);

        $invoice = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $relation->getKey(),
            'invoice_number' => 'INV-EMAIL-005',
            'invoice_status' => InvoiceStatus::DRAFT->value,
            'user_id'        => $this->user->id,
        ]);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction(TestAction::make('email_invoice')->table($invoice))
            ->callMountedAction();

        /* Assert */
        Mail::assertQueued(InvoiceMailable::class, function ($mail) {
            $ccAddresses = collect($mail->cc)->pluck('address');

            return $mail->hasCc('shared@example.com')
                && $mail->hasCc('template@example.com')
                && $ccAddresses->filter(fn ($address) => $address === 'shared@example.com')->count() === 1;
        });
    }

    #[Test]
    #[Group('crud')]
    public function it_sends_without_cc_when_the_customer_has_no_cc_emails(): void
    {
        /* Arrange */
        Mail::fake();
        $this->grantPermission(Permission::VIEW_INVOICES, Permission::EMAIL_INVOICES);

        $relation = Relation::factory()->for($this->company)->customer()->create();
        $contact  = $relation->contacts()->create([
            'company_id' => $this->company->id,
            'first_name' => 'Jane',
            'last_name'  => 'Doe',
        ]);
        $contact->communications()->create([
            'company_id'          => $this->company->id,
            'is_primary'          => true,
            'communication_type'  => CommunicationType::EMAIL->value,
            'communication_value' => 'customer@example.com',
        ]);

        $invoice = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $relation->getKey(),
            'invoice_number' => 'INV-EMAIL-006',
            'invoice_status' => InvoiceStatus::DRAFT->value,
            'user_id'        => $this->user->id,
        ]);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListInvoices::class)
            ->mountAction(TestAction::make('email_invoice')->table($invoice))
            ->callMountedAction();

        /* Assert */
        Mail::assertQueued(InvoiceMailable::class, fn ($mail) => empty($mail->cc));
    }

    /**
     * Grant the current test user one or more permissions, creating the
     * underlying Spatie permission records first if they don't already exist.
     */
    private function grantPermission(Permission ...$permissions): void
    {
        foreach ($permissions as $permission) {
            SpatiePermission::query()->firstOrCreate(['name' => $permission->value, 'guard_name' => 'web']);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach ($permissions as $permission) {
            $this->user->givePermissionTo($permission->value);
        }
    }
    # endregion

    #region spicy
    # endregion
}
