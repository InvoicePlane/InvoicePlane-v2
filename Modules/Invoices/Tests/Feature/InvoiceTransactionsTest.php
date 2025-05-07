<?php

namespace Tests\Feature;

use Modules\Core\Tests\AbstractTestCase;

use Modules\Invoices\Models\InvoiceTransaction;

use Modules\Core\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Filament\Resources\InvoiceTransactionResource\Pages\CreateInvoiceTransaction;
use Modules\Core\Filament\Resources\InvoiceTransactionResource\Pages\EditInvoiceTransaction;
use Modules\Core\Filament\Resources\InvoiceTransactionResource\Pages\ListInvoiceTransactions;
use Modules\Core\Models\InvoiceTransaction;
use Modules\Core\Tests\AbstractTestCase;

class InvoiceTransactionsTest extends TestCase
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
    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\Group('smoke')]
    /**
     * @group smoke
     *
     * @covers \Modules\.\Filament\./app/Filament\Resources\InvoiceTransactionResource
     */
    public function it_lists_invoicetransactions(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        Livewire::test(ListInvoiceTransactions::class)
            ->assertSuccessful();
    }

    // endregion

    // region crud
    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\Group('crud')]
    /**
     * @test
     *
     * @group crud
     *
     * @covers \Modules\.\Filament\./app/Filament\Resources\InvoiceTransactionResource
     *
     * @payload
     * []
     */
    public function it_creates_a_invoicetransaction(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        Livewire::test(CreateInvoiceTransaction::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\Group('crud')]
    /**
     * @test
     *
     * @group crud
     *
     * @covers \Modules\.\Filament\./app/Filament\Resources\InvoiceTransactionResource
     *
     * @payload
     * []
     */
    public function it_fails_to_create_invoicetransaction_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $payload = [
        ];

        Livewire::test(CreateInvoiceTransaction::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\Group('crud')]
    /**
     * @covers \Modules\.\Filament\./app/Filament\Resources\InvoiceTransactionResource
     *
     * @payload
     * []
     */
    public function it_updates_a_invoicetransaction(): void
    {
        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = InvoiceTransaction::factory()->create();

        $payload = [
        ];

        Livewire::test(EditInvoiceTransaction::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\Group('crud')]
    /**
     * @test
     *
     * @group crud
     *
     * @covers \Modules\.\Filament\./app/Filament\Resources\InvoiceTransactionResource
     *
     * @payload
     * []
     */
    public function it_fails_to_update_invoicetransaction_when_required_fields_are_missing(): void
    {
        $this->markTestIncomplete();

        //$this->actingAs(User::factory()->create());

        $record = InvoiceTransaction::factory()->create();

        $payload = [
        ];

        Livewire::test(EditInvoiceTransaction::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasFormErrors();

        if (app()->isLocal()) {
            dump($payload);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\Group('crud')]
    /**
     * @covers \Modules\.\Filament\./app/Filament\Resources\InvoiceTransactionResource
     *
     * @payload
     * []
     */
    public function it_deletes_a_invoicetransaction(): void
    {
        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = InvoiceTransaction::factory()->create();

        Livewire::test(ListInvoiceTransactions::class)
            ->callTableAction('delete', $record);

        $this->assertDatabaseMissing('invoicetransactions', ['id' => $record->id]);
    }

    // endregion

    // region usp

    // endregion
}
