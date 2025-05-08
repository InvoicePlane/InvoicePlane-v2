<?php

namespace Modules\Invoices\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Enums\RecurringFrequency;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource\Pages\CreateRecurringInvoice;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource\Pages\EditRecurringInvoice;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource\Pages\ListRecurringInvoices;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\RecurringInvoice;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(RecurringInvoiceResource::class)]

class RecurringInvoicesTest extends AbstractTestCase
{
    use RefreshDatabase;
    use WithFaker;
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->withCompany()->create();
        session(['current_company_id' => $this->user->company_id]);
        $this->withoutExceptionHandling();
    }

    // region smoke
    #[Test]
    #[Group('smoke')]
    public function it_lists_recurring_invoices(): void
    {
        $this->markTestIncomplete();

        /* arrange */


        //$recurringInvoice = RecurringInvoice::factory()->create();
        $recurring = RecurringInvoice::factory()->for($this->user->company)->create();
        //$this->actingAs(User::factory()->create());

        Livewire::actingAs($this->user)
            ->test(ListRecurringInvoices::class)
            ->assertSuccessful();
    }

    // endregion

    // region crud
    #[Test]
    public function it_creates_recurring_invoice_with_items(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $invoice = Invoice::factory()->for($this->user->company)->create();

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

        Livewire::actingAs($this->user)
            ->test(CreateRecurringInvoice::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('recurring_invoices', [
            'invoice_id' => $invoice->id,
            'frequency'  => RecurringFrequency::MONTHLY->value,
        ]);

        $this->assertDatabaseCount('recurring_invoice_items', 1);
    }

    #[Test]
    public function it_fails_without_items(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $invoice = Invoice::factory()->for($this->user->company)->create();

        /** @payload */
        $payload = [
            'invoice_id'              => $invoice->id,
            'start_at'                => now()->format('Y-m-d'),
            'frequency'               => RecurringFrequency::MONTHLY->value,
            'recurring_invoice_items' => [],
        ];

        Livewire::actingAs($this->user)
            ->test(CreateRecurringInvoice::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasErrors(['recurring_invoice_items']);
    }

    #[Test]
    public function it_fails_without_frequency(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $invoice = Invoice::factory()->for($this->user->company)->create();

        /** @payload */
        $payload = [
            'invoice_id'              => $invoice->id,
            'start_at'                => now()->format('Y-m-d'),
            'recurring_invoice_items' => [
                ['name' => 'Monthly', 'quantity' => 1, 'price' => 10],
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
     * "start_at": "2025-04-30",
     * "end_at": "2025-04-30"
     * }
     */
    public function it_fails_to_create_recurringinvoice_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */


        //$this->actingAs(User::factory()->create());

        $payload = [
            'company_id'        => 'Value',
            'invoice_id'        => 'Value',
            'document_group_id' => 'Value',
            'frequency'         => 'Value',
            'start_at'          => '2025-04-30',
            'end_at'            => '2025-04-30',
        ];

        /** act */
$component = Livewire::actingAs($this->user)->test(CreateRecurringInvoice::class)->fillForm($payload)->call('create');

/** assert */
$component->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }

    #[Test]
    public function it_fails_if_end_at_is_before_today(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $invoice = Invoice::factory()->for($this->user->company)->create();

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
    public function it_updates_recurring_invoice(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $recurring = RecurringInvoice::factory()
            ->for($this->user->company)
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

        /** act */
$component = Livewire::actingAs($this->user)->test(EditRecurringInvoice::class, ['record' => $record->getKey()])->fillForm($payload)->call('save');

/** assert */
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

        $this->markTestIncomplete('Delete test needs confirmation logic.');

        $recurring = RecurringInvoice::factory()->for($this->user->company)->create();
        //$this->actingAs(User::factory()->create());

        $record = RecurringInvoice::factory()->create();

        Livewire::actingAs($this->user)
            ->test(ListRecurringInvoices::class)
            ->call('delete', $recurring->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('recurring_invoices', ['id' => $record->id]);
    }

    // endregion

    // region usp
    // endregion
}
