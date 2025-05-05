<?php

namespace Modules\Invoices\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource\Pages\CreateRecurringInvoice;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource\Pages\EditRecurringInvoice;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource\Pages\ListRecurringInvoices;
use Modules\Invoices\Models\RecurringInvoice;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(RecurringInvoiceResource::class)]

class RecurringInvoicesTest extends AbstractTestCase
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
    public function it_lists_recurring_invoices(): void
    {
        $this->markTestIncomplete();

        //$recurringInvoice = RecurringInvoice::factory()->create();

        //$this->actingAs(User::factory()->create());

        Livewire::test(ListRecurringInvoices::class)
            ->assertSuccessful();
    }

    // endregion

    // region crud
    #[Test]
    #[Group('crud')]
    /**
     * \Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource.
     *
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

        //$this->actingAs(User::factory()->create());

        $payload = [
            'company_id'        => 'Value',
            'invoice_id'        => 'Value',
            'document_group_id' => 'Value',
            'frequency'         => 'Value',
            'start_at'          => '2025-04-30',
            'end_at'            => '2025-04-30',
        ];

        Livewire::test(CreateRecurringInvoice::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
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

        Livewire::test(EditRecurringInvoice::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }
}
