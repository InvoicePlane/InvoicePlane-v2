<?php

namespace Modules\Invoices\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Models\RecurringInvoiceItem;

//use Modules\Invoices\Filament\Resources\RecurringInvoiceItemResource\Pages\CreateRecurringInvoiceItem;
//use Modules\Invoices\Filament\Resources\RecurringInvoiceItemResource\Pages\EditRecurringInvoiceItem;
//use Modules\Invoices\Filament\Resources\RecurringInvoiceItemResource\Pages\ListRecurringInvoiceItems;

class RecurringInvoiceItemsTest extends AbstractTestCase
{
    use RefreshDatabase;
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
    /**
     * @group smoke
     *
     */
    public function it_lists_recurring_invoice_items(): void
    {
        $this->markTestIncomplete();

        /* arrange */


        //$this->actingAs(User::factory()->create());

        /** act */
$component = Livewire::actingAs($this->user)->test(ListRecurringInvoiceItems::class);

/** assert */
$component->assertSuccessful();
    }

    // endregion

    // region crud
    #[Test]
    #[Group('Crud')]
    /**
     * @test
     *
     * @group crud
     *
     *
     * @payload
     * []
     */
    public function it_creates_a_recurring_invoice_item(): void
    {
        $this->markTestIncomplete();

        /* arrange */


        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        /** act */
$component = Livewire::actingAs($this->user)->test(CreateRecurringInvoiceItem::class)->fillForm($payload)->call('create');

/** assert */
$component->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('Crud')]
    /**
     * @test
     *
     * @group crud
     *
     *
     * @payload
     * []
     */
    public function it_fails_to_create_recurring_invoice_item_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */


        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        /** act */
$component = Livewire::actingAs($this->user)->test(CreateRecurringInvoiceItem::class)->fillForm($payload)->call('create');

/** assert */
$component->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }

    #[Test]
    #[Group('Crud')]
    /**
     *
     * @payload
     * []
     */
    public function it_updates_a_recurring_invoice_item(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = RecurringInvoiceItem::factory()->create();

        $payload = [
        ];

        /** act */
$component = Livewire::actingAs($this->user)->test(EditRecurringInvoiceItem::class, ['record' => $record->getKey()])->fillForm($payload)->call('save');

/** assert */
$component->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('Crud')]
    /**
     * @test
     *
     * @group crud
     *
     *
     * @payload
     * []
     */
    public function it_fails_to_update_recurring_invoice_item_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        /* arrange */


        //$this->actingAs(User::factory()->create());

        $record = RecurringInvoiceItem::factory()->create();

        $payload = [
        ];

        /** act */
$component = Livewire::actingAs($this->user)->test(EditRecurringInvoiceItem::class, ['record' => $record->getKey()])->fillForm($payload)->call('save');

/** assert */
$component->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }

    #[Test]
    #[Group('Crud')]
    /**
     *
     * @payload
     * []
     */
    public function it_deletes_a_recurring_invoice_item(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = RecurringInvoiceItem::factory()->create();

        /** act */
$component = Livewire::actingAs($this->user)->test(ListRecurringInvoiceItems::class)->callTableAction('delete', $record);

        $this->assertDatabaseMissing('recurring_invoice_items', ['id' => $record->id]);
    }

    // endregion

    // region usp

    // endregion
}
