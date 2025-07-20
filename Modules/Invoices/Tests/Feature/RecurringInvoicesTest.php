<?php

namespace Modules\Invoices\Tests\Feature;

use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\DocumentGroup;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Enums\RecurringFrequency;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Pages\CreateRecurringInvoice;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Pages\EditRecurringInvoice;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Pages\ListRecurringInvoices;
use Modules\Invoices\Models\RecurringInvoice;
use Modules\Products\Models\Product;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListRecurringInvoices::class)]
class RecurringInvoicesTest extends AbstractCompanyPanelTestCase
{
    // region smoke
    #[Test]
    #[Group('smoke')]
    public function it_lists_recurring_invoices(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $user          = $this->user;
        $customer      = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup = DocumentGroup::factory()->for($this->company)->create();
        $product       = Product::factory()->for($this->company)->create();

        /** @payload */
        $payload = [
            'invoice_id'              => null,
            'start_at'                => now()->format('Y-m-d'),
            'end_at'                  => now()->addMonths(6)->format('Y-m-d'),
            'frequency'               => RecurringFrequency::MONTHLY->value,
            'recurring_invoice_items' => [
                ['name' => 'Subscription A', 'quantity' => 1, 'price' => 99],
            ],
        ];

        $recurring = RecurringInvoice::factory()->for($this->user->companies()->first())->create($payload);

        $component = Livewire::actingAs($this->user)
            ->test(ListRecurringInvoices::class, ['tenant' => Str::lower($this->user->companies()->first()->search_code)]);

        $component
            ->assertSuccessful();
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    public function it_creates_recurring_invoice_with_items_through_a_modal(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company       = $this->user->companies()->first();
        $user          = $this->user;
        $customer      = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup = DocumentGroup::factory()->for($this->company)->create();
        $product       = Product::factory()->for($this->company)->create();

        /** @payload */
        $payload = [
            'invoice_id'              => $invoice->id,
            'start_at'                => now()->format('Y-m-d'),
            'end_at'                  => now()->addMonths(6)->format('Y-m-d'),
            'frequency'               => RecurringFrequency::MONTHLY->value,
            'recurring_invoice_items' => [
                ['name' => 'Subscription A', 'quantity' => 1, 'price' => 99],
            ],
        ];

        $component = Livewire::actingAs($this->user)
            ->test(CreateRecurringInvoice::class)
            ->fillForm($payload)
            ->mountAction('create')
            ->callMountedAction();

        $component
            ->assertHasNoErrors();

        $this->assertDatabaseHas('recurring_invoices', [
            'invoice_id' => $invoice->id,
            'frequency'  => RecurringFrequency::MONTHLY->value,
        ]);

        $this->assertDatabaseCount('recurring_invoice_items', 1);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_through_a_modal_without_items(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company       = $this->user->companies()->first();
        $user          = $this->user;
        $customer      = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup = DocumentGroup::factory()->for($this->company)->create();
        $product       = Product::factory()->for($this->company)->create();

        /** @payload */
        $payload = [
            'invoice_id' => $invoice->id,
            'start_at'   => now()->format('Y-m-d'),
            'end_at'     => now()->addMonths(6)->format('Y-m-d'),
            'frequency'  => RecurringFrequency::MONTHLY->value,
        ];

        Livewire::actingAs($this->user)
            ->test(CreateRecurringInvoice::class)
            ->fillForm($payload)
            ->mountAction('create')
            ->callMountedAction()
            ->assertHasErrors(['recurring_invoice_items']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_through_a_modal_without_frequency(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company       = $this->user->companies()->first();
        $user          = $this->user;
        $customer      = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup = DocumentGroup::factory()->for($this->company)->create();
        $product       = Product::factory()->for($this->company)->create();

        /** @payload */
        $payload = [
            'invoice_id'              => $invoice->id,
            'start_at'                => now()->format('Y-m-d'),
            'end_at'                  => now()->addMonths(6)->format('Y-m-d'),
            'recurring_invoice_items' => [
                ['name' => 'Subscription A', 'quantity' => 1, 'price' => 99],
            ],
        ];

        Livewire::actingAs($this->user)
            ->test(CreateRecurringInvoice::class)
            ->fillForm($payload)
            ->mountAction('create')
            ->callMountedAction()
            ->assertHasErrors(['frequency']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     * "company_id": "Value",
     * "invoice_id": "Value",
     * "document_group_id": "Value",
     * "frequency": "Value",
     * "end_at": "2025-04-30"
     * }
     */
    public function it_fails_to_create_recurringinvoice_without_required_start_at(): void
    {
        $this->markTestIncomplete();
        /* arrange */
        $company       = $this->user->companies()->first();
        $user          = $this->user;
        $customer      = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup = DocumentGroup::factory()->for($this->company)->create();
        $product       = Product::factory()->for($this->company)->create();

        /** @payload */
        $payload = [
            'invoice_id'              => $invoice->id,
            'end_at'                  => now()->addMonths(6)->format('Y-m-d'),
            'frequency'               => RecurringFrequency::MONTHLY->value,
            'recurring_invoice_items' => [
                ['name' => 'Subscription A', 'quantity' => 1, 'price' => 99],
            ],
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateRecurringInvoice::class)
            ->fillForm($payload)
            ->mountAction('create')
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_if_end_at_is_before_today(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company       = $this->user->companies()->first();
        $user          = $this->user;
        $customer      = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup = DocumentGroup::factory()->for($this->company)->create();
        $product       = Product::factory()->for($this->company)->create();

        /** @payload */
        $payload = [
            'invoice_id'              => $invoice->id,
            'start_at'                => now()->subMonths(2)->format('Y-m-d'),
            'end_at'                  => now()->subDays(1)->format('Y-m-d'),
            'frequency'               => RecurringFrequency::WEEKLY->value,
            'recurring_invoice_items' => [
                ['name' => 'Expired', 'quantity' => 1, 'price' => 20],
            ],
        ];

        Livewire::actingAs($this->user)
            ->test(CreateRecurringInvoice::class)
            ->fillForm($payload)
            ->mountAction('create')
            ->callMountedAction()
            ->assertHasErrors(['end_at']);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_recurring_invoice(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $recurring = RecurringInvoice::factory()
            ->for($this->user->companies()->first())
            ->create([
                'frequency' => RecurringFrequency::WEEKLY->value,
                'end_at'    => now()->addWeeks(2)->format('Y-m-d'),
            ]);

        /** @payload */
        $payload = [
            'frequency' => RecurringFrequency::MONTHLY->value,
            'end_at'    => now()->addMonths(3)->format('Y-m-d'),
        ];

        Livewire::actingAs($this->user)
            ->test(EditRecurringInvoice::class, ['record' => $recurring->id])
            ->fillForm($payload)
            ->mountAction('edit')
            ->callMountedAction()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('recurring_invoices', [
            'id'        => $recurring->id,
            'frequency' => RecurringFrequency::MONTHLY->value,
        ]);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     * "company_id": "Value",
     * "invoice_id": "Value",
     * "document_group_id": "Value",
     * "frequency": "Value",
     * "start_at": "2025-04-30",
     * "end_at": "2025-04-30"
     * }
     */
    public function it_fails_to_update_recurringinvoice_without_required_fields(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        //$this->actingAs(User::factory()->create());

        $record = RecurringInvoice::factory()->create();

        $payload = [
            'company_id'        => 'Value',
            'invoice_id'        => 'Value',
            'document_group_id' => 'Value',
            'frequency'         => 'Value',
            'start_at'          => '2025-04-30',
            'end_at'            => '2025-04-30',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(EditRecurringInvoice::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->mountAction('edit')
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    public function it_creates_recurring_invoice_with_items(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company       = $this->user->companies()->first();
        $user          = $this->user;
        $customer      = Relation::factory()->for($company)->customer()->create();
        $documentGroup = DocumentGroup::factory()->for($company)->create();
        $product       = Product::factory()->for($company)->create();

        /** @payload */
        $payload = [
            'invoice_id'              => $invoice->id,
            'start_at'                => now()->format('Y-m-d'),
            'end_at'                  => now()->addMonths(6)->format('Y-m-d'),
            'frequency'               => RecurringFrequency::MONTHLY->value,
            'recurring_invoice_items' => [
                ['name' => 'Subscription A', 'quantity' => 1, 'price' => 99],
            ],
        ];

        $component = Livewire::actingAs($this->user)
            ->test(CreateRecurringInvoice::class)
            ->fillForm($payload)
            ->call('create');

        $component
            ->assertHasNoErrors();

        $this->assertDatabaseHas('recurring_invoices', [
            'invoice_id' => $invoice->id,
            'frequency'  => RecurringFrequency::MONTHLY->value,
        ]);

        $this->assertDatabaseCount('recurring_invoice_items', 1);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_without_items(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company       = $this->user->companies()->first();
        $user          = $this->user;
        $customer      = Relation::factory()->for($company)->customer()->create();
        $documentGroup = DocumentGroup::factory()->for($company)->create();
        $product       = Product::factory()->for($company)->create();

        /** @payload */
        $payload = [
            'invoice_id' => $invoice->id,
            'start_at'   => now()->format('Y-m-d'),
            'end_at'     => now()->addMonths(6)->format('Y-m-d'),
            'frequency'  => RecurringFrequency::MONTHLY->value,
        ];

        Livewire::actingAs($this->user)
            ->test(CreateRecurringInvoice::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasErrors(['recurring_invoice_items']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_without_frequency(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company       = $this->user->companies()->first();
        $user          = $this->user;
        $customer      = Relation::factory()->for($company)->customer()->create();
        $documentGroup = DocumentGroup::factory()->for($company)->create();
        $product       = Product::factory()->for($company)->create();

        /** @payload */
        $payload = [
            'invoice_id'              => $invoice->id,
            'start_at'                => now()->format('Y-m-d'),
            'end_at'                  => now()->addMonths(6)->format('Y-m-d'),
            'recurring_invoice_items' => [
                ['name' => 'Subscription A', 'quantity' => 1, 'price' => 99],
            ],
        ];

        Livewire::actingAs($this->user)
            ->test(CreateRecurringInvoice::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasErrors(['frequency']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     * "company_id": "Value",
     * "invoice_id": "Value",
     * "document_group_id": "Value",
     * "frequency": "Value",
     * "end_at": "2025-04-30"
     * }
     */
    public function it_fails_to_create_recurringinvoice_without_required_start_at(): void
    {
        $this->markTestIncomplete();
        /* arrange */
        $company       = $this->user->companies()->first();
        $user          = $this->user;
        $customer      = Relation::factory()->for($company)->customer()->create();
        $documentGroup = DocumentGroup::factory()->for($company)->create();
        $product       = Product::factory()->for($company)->create();

        /** @payload */
        $payload = [
            'invoice_id'              => $invoice->id,
            'end_at'                  => now()->addMonths(6)->format('Y-m-d'),
            'frequency'               => RecurringFrequency::MONTHLY->value,
            'recurring_invoice_items' => [
                ['name' => 'Subscription A', 'quantity' => 1, 'price' => 99],
            ],
        ];

        /* act */
        $component = Livewire::actingAs($this->user)->test(CreateRecurringInvoice::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_if_end_at_is_before_today(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $company       = $this->user->companies()->first();
        $user          = $this->user;
        $customer      = Relation::factory()->for($company)->customer()->create();
        $documentGroup = DocumentGroup::factory()->for($company)->create();
        $product       = Product::factory()->for($company)->create();

        /** @payload */
        $payload = [
            'invoice_id'              => $invoice->id,
            'start_at'                => now()->subMonths(2)->format('Y-m-d'),
            'end_at'                  => now()->subDays(1)->format('Y-m-d'),
            'frequency'               => RecurringFrequency::WEEKLY->value,
            'recurring_invoice_items' => [
                ['name' => 'Expired', 'quantity' => 1, 'price' => 20],
            ],
        ];

        Livewire::actingAs($this->user)
            ->test(CreateRecurringInvoice::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasErrors(['end_at']);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_recurring_invoice(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $recurring = RecurringInvoice::factory()
            ->for($this->user->companies()->first())
            ->create([
                'frequency' => RecurringFrequency::WEEKLY->value,
                'end_at'    => now()->addWeeks(2)->format('Y-m-d'),
            ]);

        /** @payload */
        $payload = [
            'frequency' => RecurringFrequency::MONTHLY->value,
            'end_at'    => now()->addMonths(3)->format('Y-m-d'),
        ];

        Livewire::actingAs($this->user)
            ->test(EditRecurringInvoice::class, ['record' => $recurring->id])
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('recurring_invoices', [
            'id'        => $recurring->id,
            'frequency' => RecurringFrequency::MONTHLY->value,
        ]);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     * "company_id": "Value",
     * "invoice_id": "Value",
     * "document_group_id": "Value",
     * "frequency": "Value",
     * "start_at": "2025-04-30",
     * "end_at": "2025-04-30"
     * }
     */
    public function it_fails_to_update_recurringinvoice_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        //$this->actingAs(User::factory()->create());

        $record = RecurringInvoice::factory()->create();

        $payload = [
            'company_id'        => 'Value',
            'invoice_id'        => 'Value',
            'document_group_id' => 'Value',
            'frequency'         => 'Value',
            'start_at'          => '2025-04-30',
            'end_at'            => '2025-04-30',
        ];

        /* act */
        $component = Livewire::actingAs($this->user)->test(EditRecurringInvoice::class, ['record' => $record->getKey()])->fillForm($payload)->call('save');

        /* assert */
        $component->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * []
     */
    public function it_deletes_a_recurringinvoice(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $recurring = RecurringInvoice::factory()->for($this->user->companies()->first())->create();
        //$this->actingAs(User::factory()->create());

        $record = RecurringInvoice::factory()->create();

        $component = Livewire::actingAs($this->user)
            ->test(ListRecurringInvoices::class)
            ->callAction('delete', $record);

        $this->assertDatabaseMissing('recurring_invoices', ['id' => $record->id]);
    }
    # endregion

    # region multi-tenancy
    #[Test]
    #[Group('multi-tenancy')]
    public function it_cannot_access_recurring_invoices_of_another_tenant(): void
    {
        $this->markTestIncomplete('Should assert forbidden/404 when accessing another tenant\'s recurring invoice.');
    }
    # endregion

    # region spicy
    # endregion
}
