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
     * @covers \Modules\.\Filament\./app/Filament\Resources\RecurringInvoiceItemResource
     */
    public function it_lists_recurringinvoiceitems(): void
    {
        /* arrange */
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        Livewire::test(ListRecurringInvoiceItems::class)
            ->assertSuccessful();
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
     * @covers \Modules\.\Filament\./app/Filament\Resources\RecurringInvoiceItemResource
     *
     * @payload
     * []
     */
    public function it_creates_a_recurringinvoiceitem(): void
    {
        /* arrange */
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        Livewire::test(CreateRecurringInvoiceItem::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('Crud')]
    /**
     * @test
     *
     * @group crud
     *
     * @covers \Modules\.\Filament\./app/Filament\Resources\RecurringInvoiceItemResource
     *
     * @payload
     * []
     */
    public function it_fails_to_create_recurringinvoiceitem_when_required_fields_are_missing(): void
    {
        /* arrange */
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        Livewire::test(CreateRecurringInvoiceItem::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }

    #[Test]
    #[Group('Crud')]
    /**
     * @covers \Modules\.\Filament\./app/Filament\Resources\RecurringInvoiceItemResource
     *
     * @payload
     * []
     */
    public function it_updates_a_recurringinvoiceitem(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = RecurringInvoiceItem::factory()->create();

        $payload = [
        ];

        Livewire::test(EditRecurringInvoiceItem::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('Crud')]
    /**
     * @test
     *
     * @group crud
     *
     * @covers \Modules\.\Filament\./app/Filament\Resources\RecurringInvoiceItemResource
     *
     * @payload
     * []
     */
    public function it_fails_to_update_recurringinvoiceitem_when_required_fields_are_missing(): void
    {
        /* arrange */
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $record = RecurringInvoiceItem::factory()->create();

        $payload = [
        ];

        Livewire::test(EditRecurringInvoiceItem::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }

    #[Test]
    #[Group('Crud')]
    /**
     * @covers \Modules\.\Filament\./app/Filament\Resources\RecurringInvoiceItemResource
     *
     * @payload
     * []
     */
    public function it_deletes_a_recurringinvoiceitem(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = RecurringInvoiceItem::factory()->create();

        Livewire::test(ListRecurringInvoiceItems::class)
            ->callTableAction('delete', $record);

        $this->assertDatabaseMissing('recurringinvoiceitems', ['id' => $record->id]);
    }

    // endregion

    // region usp

    // endregion
}
